<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Check if the request is a POST request, if not, redirect to sitter dashboard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/user/sitterDashboard.php');
    exit();
}

// Get the user ID from session
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
// Check if the user has a sitter record
|--------------------------------------------------------------------------
*/
$checkStmt = $conn->prepare("
    SELECT sitterID, isAvailable
    FROM sitters
    WHERE userID = ?
    LIMIT 1
");
// Bind user ID
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

// If no sitter record, redirect with error
if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    header('Location: /Pampeers/public/user/dashboard.php?error=not_a_sitter');
    exit();
}

// Get the sitter data
$sitter = $checkResult->fetch_assoc();
$checkStmt->close();

/*
|--------------------------------------------------------------------------
// Determine the new availability status from the form
|--------------------------------------------------------------------------
*/
$newAvailability = isset($_POST['isAvailable']) ? 1 : 0;

/*
|--------------------------------------------------------------------------
// Update the availability in the database
|--------------------------------------------------------------------------
*/
$updateStmt = $conn->prepare("
    UPDATE sitters
    SET isAvailable = ?
    WHERE sitterID = ?
");

// Bind new availability and sitter ID
$updateStmt->bind_param("ii", $newAvailability, $sitter['sitterID']);

// If update succeeds, redirect with success
if ($updateStmt->execute()) {
    $updateStmt->close();
    header('Location: /Pampeers/public/user/sitterDashboard.php?update=availability_success');
    exit();
}

// If failed, redirect with error
$updateStmt->close();
header('Location: /Pampeers/public/user/sitterDashboard.php?error=availability_failed');
exit();
?>