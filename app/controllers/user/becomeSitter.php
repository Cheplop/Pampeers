<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Check if the request is a POST request, if not, redirect to dashboard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pampeers/public/user/dashboard.php');
    exit();
}

/*
|--------------------------------------------------------------------------
// Helper function to create a unique ID for the sitter
|--------------------------------------------------------------------------
*/
function generateUUIDv4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Get the user ID from session
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
// Check if the user exists
|--------------------------------------------------------------------------
*/
$userStmt = $conn->prepare("
    SELECT id, role
    FROM users
    WHERE id = ?
    LIMIT 1
");
// Bind user ID
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

// If user not found, logout
if ($userResult->num_rows === 0) {
    $userStmt->close();
    session_unset();
    session_destroy();
    header('Location: /pampeers/public/login.php?error=user_not_found');
    exit();
}
$userStmt->close();

/*
|--------------------------------------------------------------------------
// Check if user is already a sitter
|--------------------------------------------------------------------------
*/
$checkStmt = $conn->prepare("
    SELECT sitterID
    FROM sitters
    WHERE userID = ?
    LIMIT 1
");
// Bind user ID
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

// If already a sitter, set session and redirect
if ($checkResult->num_rows > 0) {
    $existingSitter = $checkResult->fetch_assoc();
    $_SESSION['sitter_id'] = $existingSitter['sitterID'];
    $checkStmt->close();

    header('Location: /pampeers/public/user/sitterDashboard.php?info=already_sitter');
    exit();
}
$checkStmt->close();

/*
|--------------------------------------------------------------------------
// Get optional sitter fields from form
|--------------------------------------------------------------------------
*/
$bio        = trim($_POST['bio'] ?? ''); // Bio description
$hourlyRate = trim($_POST['hourlyRate'] ?? '0'); // Hourly rate
$experience = trim($_POST['experience'] ?? '0'); // Years of experience

// Set defaults if empty
if ($hourlyRate === '') {
    $hourlyRate = '0';
}

if ($experience === '') {
    $experience = '0';
}

// Validate hourly rate
if (!is_numeric($hourlyRate) || (float)$hourlyRate < 0) {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_rate');
    exit();
}

// Validate experience
if (!ctype_digit((string)$experience) || (int)$experience < 0) {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_experience');
    exit();
}

// Generate UUID for sitter
$sitterUUID       = generateUUIDv4();
// Convert to float and int
$hourlyRateFloat  = (float) $hourlyRate;
$experienceInt    = (int) $experience;
$isAvailable      = 1; // Default to available
$verificationStatus = 'pending'; // Default verification status

/*
|--------------------------------------------------------------------------
// Insert the new sitter profile into the database
|--------------------------------------------------------------------------
*/
$insertStmt = $conn->prepare("
    INSERT INTO sitters (
        uuid,
        userID,
        bio,
        hourlyRate,
        experience,
        isAvailable,
        verificationStatus
    ) VALUES (?, ?, ?, ?, ?, ?, ?)
");

// Bind the values
$insertStmt->bind_param(
    "sisdiss",
    $sitterUUID,
    $userId,
    $bio,
    $hourlyRateFloat,
    $experienceInt,
    $isAvailable,
    $verificationStatus
);

// If insert succeeds, set session sitter ID and redirect
if ($insertStmt->execute()) {
    $_SESSION['sitter_id'] = $insertStmt->insert_id;
    $insertStmt->close();

    header('Location: /pampeers/public/user/sitterDashboard.php?success=became_sitter');
    exit();
}

// If failed, redirect with error
$insertStmt->close();
header('Location: /pampeers/public/user/dashboard.php?error=become_sitter_failed');
exit();
?>