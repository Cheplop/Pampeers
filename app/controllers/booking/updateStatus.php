<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Ensure the user is logged in
requireAuth();

// Define the correct redirect path for sitters
$sitterDash = '/Pampeers/public/sitter/sitterDashboard.php';

// 1. Get the booking ID and the new status from the URL
$bookingId = $_GET['id'] ?? null;
$newStatus = $_GET['status'] ?? null;
$userId    = $_SESSION['user_id'];

// 2. Validate input and allowed status values
$allowedStatuses = ['accepted', 'declined', 'completed', 'cancelled'];
if (!$bookingId || !in_array($newStatus, $allowedStatuses)) {
    header("Location: $sitterDash?error=invalid_request");
    exit();
}

/**
 * 3. SECURITY CHECK: 
 * Ensure the logged-in user is actually the sitter assigned to this booking.
 * We find the sitterID associated with the current user first.
 */
$sitterQuery = $conn->prepare("SELECT sitterID FROM sitters WHERE userID = ? LIMIT 1");
$sitterQuery->bind_param("i", $userId);
$sitterQuery->execute();
$sitterResult = $sitterQuery->get_result()->fetch_assoc();
$sitterQuery->close();

if (!$sitterResult) {
    header("Location: $sitterDash?error=not_a_sitter");
    exit();
}

$sitterId = $sitterResult['sitterID'];

/**
 * 4. PERFORM UPDATE
 * We include sitterID in the WHERE clause so a sitter can't 
 * update a booking belonging to someone else.
 */
$updateStmt = $conn->prepare("
    UPDATE bookings 
    SET status = ? 
    WHERE bookingID = ? AND sitterID = ?
");
$updateStmt->bind_param("sii", $newStatus, $bookingId, $sitterId);

if ($updateStmt->execute()) {
    if ($updateStmt->affected_rows > 0) {
        $updateStmt->close();
        header("Location: $sitterDash?status_updated=" . $newStatus);
        exit();
    } else {
        // No rows updated (likely booking ID doesn't match this sitter)
        $updateStmt->close();
        header("Location: $sitterDash?error=unauthorized_action");
        exit();
    }
}

$updateStmt->close();
header("Location: $sitterDash?error=database_error");
exit();