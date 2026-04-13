<?php
// Include the config file for database connection
require_once __DIR__ . '/../../config/config.php';

// Check if the request is a POST request, if not, redirect to login page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pampeers/public/login.php');
    exit();
}

// Get the login input (email or username) and password from the form
$loginInput    = trim($_POST['login'] ?? ''); // Remove extra spaces from login input
$plainPassword = $_POST['password'] ?? ''; // Get the password as entered

// If login input or password is empty, redirect with error
if ($loginInput === '' || $plainPassword === '') {
    header('Location: /pampeers/public/login.php?error=missing_credentials');
    exit();
}

// Prepare a query to find the user by email or username
$stmt = $conn->prepare("
    SELECT
        id,
        uuid,
        firstName,
        lastName,
        emailAddress,
        username,
        password,
        role,
        isActive
    FROM users
    WHERE emailAddress = ? OR username = ?
    LIMIT 1
");
// Bind the login input to both email and username parameters
$stmt->bind_param("ss", $loginInput, $loginInput);
// Run the query
$stmt->execute();
$result = $stmt->get_result();

// If no user found, close query and redirect with error
if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: /pampeers/public/login.php?error=invalid_credentials');
    exit();
}

// Get the user data from the result
$user = $result->fetch_assoc();
$stmt->close();

// Check if the user account is active
if ((int)$user['isActive'] !== 1) {
    header('Location: /pampeers/public/login.php?error=account_deactivated');
    exit();
}

// Verify the password matches the stored hash
if (!password_verify($plainPassword, $user['password'])) {
    header('Location: /pampeers/public/login.php?error=invalid_credentials');
    exit();
}

// Regenerate session ID for security
session_regenerate_id(true);

// Store user info in session
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_uuid']  = $user['uuid'];
$_SESSION['first_name'] = $user['firstName'];
$_SESSION['last_name']  = $user['lastName'];
$_SESSION['role']       = $user['role'];

// If user is admin, redirect to admin dashboard
if ($user['role'] === 'admin') {
    header('Location: /pampeers/public/admin/dashboard.php');
    exit();
}

// Check if the user is also a sitter
$sitterCheck = $conn->prepare("
    SELECT sitterID
    FROM sitters
    WHERE userID = ?
    LIMIT 1
");
// Bind the user ID
$sitterCheck->bind_param("i", $user['id']);
$sitterCheck->execute();
$sitterResult = $sitterCheck->get_result();

// If user is a sitter, store sitter ID in session and redirect to sitter dashboard
if ($sitterResult->num_rows > 0) {
    $sitter = $sitterResult->fetch_assoc();
    $_SESSION['sitter_id'] = $sitter['sitterID'];
    $sitterCheck->close();

    header('Location: /pampeers/public/user/sitterDashboard.php');
    exit();
}

$sitterCheck->close();

// Otherwise, redirect to regular user dashboard
header('Location: /pampeers/public/user/dashboard.php');
exit();
?>