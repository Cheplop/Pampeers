<?php
// Include the config file for database connection
require_once __DIR__ . '/../../config/config.php';

// Check if the request is a POST request, if not, redirect to register page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pampeers/public/register.php');
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
$firstName        = trim($_POST['firstName'] ?? ''); // User's first name
$middleName       = trim($_POST['middleName'] ?? ''); // Middle name (optional)
$lastName         = trim($_POST['lastName'] ?? ''); // Last name
$suffix           = trim($_POST['suffix'] ?? ''); // Name suffix like Jr.
$birthDate        = trim($_POST['birthDate'] ?? ''); // Date of birth
$sex              = trim($_POST['sex'] ?? ''); // Gender
$role             = 'user'; // Default role is user

$contactNumber    = trim($_POST['contactNumber'] ?? ''); // Phone number
$emailAddress     = trim($_POST['emailAddress'] ?? ''); // Email address
$usernameInput    = trim($_POST['username'] ?? ''); // Username
$plainPassword    = $_POST['password'] ?? ''; // Password as entered

$streetAddress    = trim($_POST['streetAddress'] ?? ''); // Street address
$barangay         = trim($_POST['barangay'] ?? ''); // Barangay (local area)
$cityMunicipality = trim($_POST['cityMunicipality'] ?? ''); // City or municipality
$province         = trim($_POST['province'] ?? ''); // Province
$country          = trim($_POST['country'] ?? ''); // Country
$zipCode          = trim($_POST['zipCode'] ?? ''); // Zip code

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
    'password'         => $plainPassword,
    'streetAddress'    => $streetAddress,
    'barangay'         => $barangay,
    'cityMunicipality' => $cityMunicipality,
    'province'         => $province,
    'country'          => $country,
    'zipCode'          => $zipCode,
];

// Loop through each required field and check if it's empty
foreach ($requiredFields as $field => $value) {
    if ($value === '') {
        // Redirect with error for the missing field
        header('Location: /pampeers/public/register.php?error=missing_' . urlencode($field));
        exit();
    }
}

/*
|--------------------------------------------------------------------------
// Extra validation checks
|--------------------------------------------------------------------------
*/
// Check if email is valid format
if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
    header('Location: /pampeers/public/register.php?error=invalid_email');
    exit();
}

// Check if sex is one of the allowed values
$allowedSex = ['male', 'female', 'other'];
if (!in_array(strtolower($sex), $allowedSex, true)) {
    header('Location: /pampeers/public/register.php?error=invalid_sex');
    exit();
}

// Check if password is at least 8 characters
if (strlen($plainPassword) < 8) {
    header('Location: /pampeers/public/register.php?error=weak_password');
    exit();
}

/*
|--------------------------------------------------------------------------
// Check if email or username already exists
|--------------------------------------------------------------------------
*/
$checkStmt = $conn->prepare("SELECT id FROM users WHERE emailAddress = ? OR username = ?");
// Bind the email and username to check
$checkStmt->bind_param("ss", $emailAddress, $usernameInput);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

// If any result found, account already exists
if ($checkResult->num_rows > 0) {
    $checkStmt->close();
    header('Location: /pampeers/public/register.php?error=account_exists');
    exit();
}
$checkStmt->close();

/*
|--------------------------------------------------------------------------
// Prepare the values for inserting into database
|--------------------------------------------------------------------------
*/
$uuid = generateUUIDv4(); // Create unique ID
$passwordHashed = password_hash($plainPassword, PASSWORD_DEFAULT); // Hash the password for security
$profilePic = 'default.jpg'; // Default profile picture

/*
|--------------------------------------------------------------------------
// Insert the new user into the database
|--------------------------------------------------------------------------
*/
$insertStmt = $conn->prepare("
    INSERT INTO users (
        uuid,
        firstName,
        middleName,
        lastName,
        suffix,
        birthDate,
        sex,
        role,
        contactNumber,
        emailAddress,
        username,
        password,
        streetAddress,
        barangay,
        cityMunicipality,
        province,
        country,
        zipCode,
        profilePic
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Bind all the values to the query
$insertStmt->bind_param(
    "sssssssssssssssssss",
    $uuid,
    $firstName,
    $middleName,
    $lastName,
    $suffix,
    $birthDate,
    $sex,
    $role,
    $contactNumber,
    $emailAddress,
    $usernameInput,
    $passwordHashed,
    $streetAddress,
    $barangay,
    $cityMunicipality,
    $province,
    $country,
    $zipCode,
    $profilePic
);

// If insert succeeds, redirect to login with success message
if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: /pampeers/public/login.php?registration=success');
    exit();
}

// If insert failed, redirect with error
$insertStmt->close();
header('Location: /pampeers/public/register.php?error=registration_failed');
exit();
?>