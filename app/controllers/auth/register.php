<?php
// Include the config file for database connection
require_once __DIR__ . '/../../config/config.php';

// Check if the request is a POST request, if not, redirect to register page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/register.php');
    exit();
}

/*
|--------------------------------------------------------------------------
// Helper function to create a unique ID for the user
|--------------------------------------------------------------------------
*/
function generateUUIDv4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/*
|--------------------------------------------------------------------------
// Get and clean the input data from the form
|--------------------------------------------------------------------------
*/
$firstName        = trim($_POST['firstName'] ?? ''); 
$middleName       = trim($_POST['middleName'] ?? ''); 
$lastName         = trim($_POST['lastName'] ?? ''); 
$suffix           = trim($_POST['suffix'] ?? ''); 
$birthDate        = trim($_POST['birthDate'] ?? '');
$sex              = trim($_POST['sex'] ?? '');
$contactNumber    = trim($_POST['contactNumber'] ?? '');
$emailAddress     = trim($_POST['emailAddress'] ?? ''); 
$usernameInput    = trim($_POST['username'] ?? '');
$passwordRaw      = $_POST['password'] ?? '';
$streetAddress    = trim($_POST['streetAddress'] ?? '');
$barangay         = trim($_POST['barangay'] ?? '');
$cityMunicipality = trim($_POST['cityMunicipality'] ?? '');
$province         = trim($_POST['province'] ?? '');
$country          = trim($_POST['country'] ?? '');
$zipCode          = trim($_POST['zipCode'] ?? '');

/* |--------------------------------------------------------------------------
// Validation
|--------------------------------------------------------------------------
*/
if (empty($firstName) || empty($lastName) || empty($emailAddress) || empty($usernameInput) || empty($passwordRaw)) {
    header('Location: /Pampeers/public/register.php?error=missing_fields');
    exit();
}

// Check if username or email already exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR emailAddress = ? LIMIT 1");
$checkStmt->bind_param("ss", $usernameInput, $emailAddress);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    header('Location: /Pampeers/public/register.php?error=already_exists');
    exit();
}
$checkStmt->close();

/* |--------------------------------------------------------------------------
// Defaults (Bio and Profile Photo handled here)
|--------------------------------------------------------------------------
*/
$uuid = generateUUIDv4();
$passwordHashed = password_hash($passwordRaw, PASSWORD_DEFAULT);
$role = "guardian"; 

// Both Bio and Profile Pic are set to defaults to be updated later in the Profile Area
$bio = ""; 
$profilePic = "default.jpg"; 

/*
|--------------------------------------------------------------------------
// Insert the new user
|--------------------------------------------------------------------------
*/
$insertStmt = $conn->prepare("
    INSERT INTO users (
        uuid, firstName, middleName, lastName, suffix, 
        birthDate, sex, role, contactNumber, emailAddress, 
        username, password, streetAddress, barangay, 
        cityMunicipality, province, country, zipCode, 
        profilePic, bio
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insertStmt->bind_param(
    "ssssssssssssssssssss",
    $uuid, $firstName, $middleName, $lastName, $suffix,
    $birthDate, $sex, $role, $contactNumber, $emailAddress,
    $usernameInput, $passwordHashed, $streetAddress, $barangay,
    $cityMunicipality, $province, $country, $zipCode,
    $profilePic, $bio
);

if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: /Pampeers/public/guestDashboard.php?success=registered');
} else {
    header('Location: /Pampeers/public/register.php?error=db_error&details=' . urlencode($conn->error));
}
exit();