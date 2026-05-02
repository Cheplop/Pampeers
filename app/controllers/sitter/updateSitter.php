<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Check if the request is a POST request, if not, redirect to edit profile
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/user/editProfile.php');
    exit();
}

// Get the user ID from session
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
// Get and clean the input data from the form
|--------------------------------------------------------------------------
*/
$firstName        = trim($_POST['firstName'] ?? ''); // User's first name
$middleName       = trim($_POST['middleName'] ?? ''); // Middle name
$lastName         = trim($_POST['lastName'] ?? ''); // Last name
$suffix           = trim($_POST['suffix'] ?? ''); // Name suffix
$birthDate        = trim($_POST['birthDate'] ?? ''); // Birth date
$sex              = trim($_POST['sex'] ?? ''); // Gender

$contactNumber    = trim($_POST['contactNumber'] ?? ''); // Phone number
$emailAddress     = trim($_POST['emailAddress'] ?? ''); // Email
$usernameInput    = trim($_POST['username'] ?? ''); // Username

$streetAddress    = trim($_POST['streetAddress'] ?? ''); // Street address
$barangay         = trim($_POST['barangay'] ?? ''); // Barangay
$cityMunicipality = trim($_POST['cityMunicipality'] ?? ''); // City
$province         = trim($_POST['province'] ?? ''); // Province
$country          = trim($_POST['country'] ?? ''); // Country
$zipCode          = trim($_POST['zipCode'] ?? ''); // Zip code

$bio              = trim($_POST['bio'] ?? ''); // Sitter bio
$hourlyRate       = trim($_POST['hourlyRate'] ?? '0'); // Hourly rate
$experience       = trim($_POST['experience'] ?? '0'); // Years of experience
$isAvailable      = isset($_POST['isAvailable']) ? 1 : 0; // Availability

/*
|--------------------------------------------------------------------------
// Check that all required fields are filled
|--------------------------------------------------------------------------
*/
$requiredFields = [
    'firstName'        => $firstName,
    'lastName'         => $lastName,
    'birthDate'        => $birthDate,
    'sex'              => $sex,
    'contactNumber'    => $contactNumber,
    'emailAddress'     => $emailAddress,
    'username'         => $usernameInput,
    'streetAddress'    => $streetAddress,
    'barangay'         => $barangay,
    'cityMunicipality' => $cityMunicipality,
    'province'         => $province,
    'country'          => $country,
    'zipCode'          => $zipCode,
];

// Loop through required fields and check if empty
foreach ($requiredFields as $field => $value) {
    if ($value === '') {
        header('Location: /Pampeers/public/user/editProfile.php?error=missing_' . urlencode($field));
        exit();
    }
}

// Validate email format
if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
    header('Location: /Pampeers/public/user/editProfile.php?error=invalid_email');
    exit();
}

// Validate sex
$allowedSex = ['male', 'female', 'other'];
if (!in_array(strtolower($sex), $allowedSex, true)) {
    header('Location: /Pampeers/public/user/editProfile.php?error=invalid_sex');
    exit();
}

// Validate hourly rate
if (!is_numeric($hourlyRate) || (float)$hourlyRate < 0) {
    header('Location: /Pampeers/public/user/editProfile.php?error=invalid_rate');
    exit();
}

// Validate experience
if (!ctype_digit((string)$experience) || (int)$experience < 0) {
    header('Location: /Pampeers/public/user/editProfile.php?error=invalid_experience');
    exit();
}

/*
|--------------------------------------------------------------------------
// Get current user and sitter data
|--------------------------------------------------------------------------
*/
$currentStmt = $conn->prepare("
    SELECT u.profilePic, s.sitterID
    FROM users u
    INNER JOIN sitters s ON s.userID = u.id
    WHERE u.id = ?
    LIMIT 1
");
// Bind user ID
$currentStmt->bind_param("i", $userId);
$currentStmt->execute();
$currentResult = $currentStmt->get_result();

// If not a sitter, redirect
if ($currentResult->num_rows === 0) {
    $currentStmt->close();
    header('Location: /Pampeers/public/user/dashboard.php?error=not_a_sitter');
    exit();
}

// Get current data
$currentData = $currentResult->fetch_assoc();
$currentStmt->close();

$profilePic = $currentData['profilePic'] ?: 'default.jpg'; // Current profile pic
$sitterId   = (int)$currentData['sitterID']; // Sitter ID

/*
|--------------------------------------------------------------------------
// Check if email or username is already taken by another user
|--------------------------------------------------------------------------
*/
$duplicateStmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE (emailAddress = ? OR username = ?)
      AND id != ?
    LIMIT 1
");
// Bind email, username, and exclude current user
$duplicateStmt->bind_param("ssi", $emailAddress, $usernameInput, $userId);
$duplicateStmt->execute();
$duplicateResult = $duplicateStmt->get_result();

// If duplicate found, redirect
if ($duplicateResult->num_rows > 0) {
    $duplicateStmt->close();
    header('Location: /Pampeers/public/user/editProfile.php?error=account_exists');
    exit();
}
$duplicateStmt->close();

/*
|--------------------------------------------------------------------------
// Handle profile picture upload if provided
|--------------------------------------------------------------------------
*/
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../uploads/profiles/'; // Upload directory

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName  = $_FILES['profilePic']['name']; // Original file name
    $tmpName       = $_FILES['profilePic']['tmp_name']; // Temp file
    $fileSize      = $_FILES['profilePic']['size']; // File size
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)); // Extension
    $allowedTypes  = ['jpg', 'jpeg', 'png', 'webp']; // Allowed types

    // Check file type
    if (!in_array($fileExtension, $allowedTypes, true)) {
        header('Location: /Pampeers/public/user/editProfile.php?error=invalid_image_type');
        exit();
    }

    // Check file size (5MB max)
    if ($fileSize > 5 * 1024 * 1024) {
        header('Location: /Pampeers/public/user/editProfile.php?error=image_too_large');
        exit();
    }

    // Generate new file name
    $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
    $targetPath  = $uploadDir . $newFileName;

    // Move uploaded file
    if (!move_uploaded_file($tmpName, $targetPath)) {
        header('Location: /Pampeers/public/user/editProfile.php?error=upload_failed');
        exit();
    }

    // Delete old profile pic if exists
    if (
        !empty($profilePic) &&
        $profilePic !== 'default.jpg' &&
        file_exists($uploadDir . $profilePic)
    ) {
        unlink($uploadDir . $profilePic);
    }

    $profilePic = $newFileName; // Set new profile pic
}

/*
|--------------------------------------------------------------------------
// Update both users and sitters tables in a transaction
|--------------------------------------------------------------------------
*/
$conn->begin_transaction(); // Start transaction

try {
    // Update users table
    $userStmt = $conn->prepare("
        UPDATE users
        SET
            firstName = ?,
            middleName = ?,
            lastName = ?,
            suffix = ?,
            birthDate = ?,
            sex = ?,
            contactNumber = ?,
            emailAddress = ?,
            username = ?,
            streetAddress = ?,
            barangay = ?,
            cityMunicipality = ?,
            province = ?,
            country = ?,
            zipCode = ?,
            profilePic = ?
        WHERE id = ?
    ");

    // Bind all user fields
    $userStmt->bind_param(
        "ssssssssssssssssi",
        $firstName,
        $middleName,
        $lastName,
        $suffix,
        $birthDate,
        $sex,
        $contactNumber,
        $emailAddress,
        $usernameInput,
        $streetAddress,
        $barangay,
        $cityMunicipality,
        $province,
        $country,
        $zipCode,
        $profilePic,
        $userId
    );
    $userStmt->execute();
    $userStmt->close();

    // Update sitters table
    $sitterStmt = $conn->prepare("
        UPDATE sitters
        SET
            bio = ?,
            hourlyRate = ?,
            experience = ?,
            isAvailable = ?
        WHERE sitterID = ?
    ");

    $hourlyRateFloat = (float)$hourlyRate; // Convert to float
    $experienceInt   = (int)$experience; // Convert to int

    // Bind sitter fields
    $sitterStmt->bind_param(
        "sdiii",
        $bio,
        $hourlyRateFloat,
        $experienceInt,
        $isAvailable,
        $sitterId
    );
    $sitterStmt->execute();
    $sitterStmt->close();

    $conn->commit(); // Commit transaction

    // Update session with new names
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;

    header('Location: /Pampeers/public/user/sitterDashboard.php?update=success');
    exit();
} catch (Exception $e) {
    $conn->rollback(); // Rollback on error
    header('Location: /Pampeers/public/user/editProfile.php?error=update_failed');
    exit();
}
?>