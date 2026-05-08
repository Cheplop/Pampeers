<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

// Redirect to the correct sitter dashboard path
$sitterDash = '/Pampeers/public/sitter/sitterDashboard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $sitterDash");
    exit();
}

$userId = $_SESSION['user_id'];

// Check if sitter exists
$checkStmt = $conn->prepare("SELECT sitterID FROM sitters WHERE userID = ? LIMIT 1");
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    header("Location: /Pampeers/public/guestDashboard.php?error=not_a_sitter");
    exit();
}

$sitter = $checkResult->fetch_assoc();
$checkStmt->close();

// Toggle logic
$newAvailability = isset($_POST['isAvailable']) ? 1 : 0;

$updateStmt = $conn->prepare("UPDATE sitters SET isAvailable = ? WHERE sitterID = ?");
$updateStmt->bind_param("ii", $newAvailability, $sitter['sitterID']);

if ($updateStmt->execute()) {
    $updateStmt->close();
    header("Location: $sitterDash?update=success");
} else {
    header("Location: $sitterDash?error=db_error");
}
exit();