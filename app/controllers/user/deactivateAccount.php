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

// Get the user ID from session
$userId = $_SESSION['user_id'];

// Prepare statement to update user as inactive and set deactivation time
$stmt = $conn->prepare("
    UPDATE users
    SET isActive = 0, deactivatedAt = NOW()
    WHERE id = ?
");
// Bind user ID
$stmt->bind_param("i", $userId);

// If update succeeds, close statement, clear session, and redirect to login
if ($stmt->execute()) {
    $stmt->close();

    // Clear all session data
    $_SESSION = [];
    session_unset();
    session_destroy();

    header('Location: /pampeers/public/login.php?account=deactivated');
    exit();
}

// If failed, close statement and redirect with error
$stmt->close();
header('Location: /pampeers/public/user/dashboard.php?error=deactivate_failed');
exit();
?>