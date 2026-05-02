<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Check if the request is a POST request, if not, redirect to edit profile
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pampeers/public/user/editProfile.php');
    exit();
}

// Get the user ID from session
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
// Collect and sanitize input from form
|--------------------------------------------------------------------------
*/
$firstName        = trim($_POST['firstName'] ?? ''); // User's first name
$middleName       = trim($_POST['middleName'] ?? ''); // Middle name (optional)
$lastName         = trim($_POST['lastName'] ?? ''); // Last name
$suffix           = trim($_POST['suffix'] ?? ''); // Name suffix (optional)
$birthDate        = trim($_POST['birthDate'] ?? ''); // Birth date
$sex              = trim($_POST['sex'] ?? ''); // Gender

$contactNumber    = trim($_POST['contactNumber'] ?? ''); // Phone number
$emailAddress     = trim($_POST['emailAddress'] ?? ''); // Email address
$usernameInput    = trim($_POST['username'] ?? ''); // Username

$streetAddress    = trim($_POST['streetAddress'] ?? ''); // Street address
$barangay         = trim($_POST['barangay'] ?? ''); // Barangay
$cityMunicipality = trim($_POST['cityMunicipality'] ?? ''); // City/Municipality
$province         = trim($_POST['province'] ?? ''); // Province
$country          = trim($_POST['country'] ?? ''); // Country
$zipCode          = trim($_POST['zipCode'] ?? ''); // Zip code

/*
|--------------------------------------------------------------------------
// Validate required fields - check if any are empty
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

// Loop through required fields and redirect if any are empty
foreach ($requiredFields as $field => $value) {
    if ($value === '') {
        header('Location: /pampeers/public/user/editProfile.php?error=missing_' . urlencode($field));
        exit();
    }
}

/*
|--------------------------------------------------------------------------
// Additional validation rules
|--------------------------------------------------------------------------
*/
// Check if email is valid format
if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
    header('Location: /pampeers/public/user/editProfile.php?error=invalid_email');
    exit();
}

// Check if sex is one of allowed values
$allowedSex = ['male', 'female', 'other'];
if (!in_array(strtolower($sex), $allowedSex, true)) {
    header('Location: /pampeers/public/user/editProfile.php?error=invalid_sex');
    exit();
}

/*
|--------------------------------------------------------------------------
// Fetch current user data to get existing profile pic
|--------------------------------------------------------------------------
*/
$currentStmt = $conn->prepare("
    SELECT profilePic
    FROM users
    WHERE id = ?
    LIMIT 1
");
// Bind user ID
$currentStmt->bind_param("i", $userId);
$currentStmt->execute();
$currentResult = $currentStmt->get_result();

// If user not found, redirect
if ($currentResult->num_rows === 0) {
    $currentStmt->close();
    header('Location: /pampeers/public/login.php?error=user_not_found');
    exit();
}

// Get current user data
$currentUser = $currentResult->fetch_assoc();
$currentStmt->close();

// Set profile pic to current or default
$profilePic = $currentUser['profilePic'] ?: 'default.jpg';

/*
|--------------------------------------------------------------------------
// Check for duplicate email or username (excluding current user)
|--------------------------------------------------------------------------
*/
$duplicateStmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE (emailAddress = ? OR username = ?)
      AND id != ?
    LIMIT 1
");
// Bind email, username, and exclude current user ID
$duplicateStmt->bind_param("ssi", $emailAddress, $usernameInput, $userId);
$duplicateStmt->execute();
$duplicateResult = $duplicateStmt->get_result();

// If duplicate found, redirect with error
if ($duplicateResult->num_rows > 0) {
    $duplicateStmt->close();
    header('Location: /pampeers/public/user/editProfile.php?error=account_exists');
    exit();
}
$duplicateStmt->close();

/*
|--------------------------------------------------------------------------
// Handle optional profile picture upload
|--------------------------------------------------------------------------
*/
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    // Set upload directory
    $uploadDir = __DIR__ . '/../../uploads/profiles/';

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Get file details
    $originalName  = $_FILES['profilePic']['name'];
    $tmpName       = $_FILES['profilePic']['tmp_name'];
    $fileSize      = $_FILES['profilePic']['size'];
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Check allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileExtension, $allowedTypes, true)) {
        header('Location: /pampeers/public/user/editProfile.php?error=invalid_image_type');
        exit();
    }

    // Check file size (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        header('Location: /pampeers/public/user/editProfile.php?error=image_too_large');
        exit();
    }

    // Generate unique filename
    $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
    $targetPath = $uploadDir . $newFileName;

    // Move uploaded file
    if (!move_uploaded_file($tmpName, $targetPath)) {
        header('Location: /pampeers/public/user/editProfile.php?error=upload_failed');
        exit();
    }

    // Delete old profile pic if it exists and isn't default
    if (
        !empty($profilePic) &&
        $profilePic !== 'default.jpg' &&
        file_exists($uploadDir . $profilePic)
    ) {
        unlink($uploadDir . $profilePic);
    }

    // Set new profile pic filename
    $profilePic = $newFileName;
}

/*
|--------------------------------------------------------------------------
// Update user profile in database
|--------------------------------------------------------------------------
*/
$updateStmt = $conn->prepare("
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

// Bind all the parameters
$updateStmt->bind_param(
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

// If update succeeds, update session and redirect
if ($updateStmt->execute()) {
    $updateStmt->close();

    // Update session with new name
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;

    header('Location: /pampeers/public/user/dashboard.php?update=success');
    exit();
}

// If failed, close statement and redirect with error
$updateStmt->close();
header('Location: /pampeers/public/user/editProfile.php?error=update_failed');
exit();
?>