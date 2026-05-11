<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Ensure the user is logged in
requireAuth();

// 1. Get the booking ID and the new status from the URL
$bookingId = $_GET['id'] ?? null;
$newStatus = $_GET['status'] ?? null;
$userId    = $_SESSION['user_id'];

// 2. Validate input and allowed status values
$allowedStatuses = ['accepted', 'declined', 'completed', 'cancelled'];
if (!$bookingId || !in_array($newStatus, $allowedStatuses)) {
    header("Location: /Pampeers/public/guestDashboard.php?error=invalid_request");
    exit();
}

/**
 * 3. SECURITY & PERMISSION CHECK
 * We now check authorization based on the ACTION, not the primary session role!
 */
if (in_array($newStatus, ['accepted', 'declined', 'completed'])) {
    
    // ACTION: Sitter responding to a booking
    $sitterQuery = $conn->prepare("SELECT sitterID FROM sitters WHERE userID = ? LIMIT 1");
    $sitterQuery->bind_param("i", $userId);
    $sitterQuery->execute();
    $sitterResult = $sitterQuery->get_result()->fetch_assoc();
    $sitterQuery->close();

    if (!$sitterResult) {
        header("Location: /Pampeers/public/sitter/sitterDashboard.php?error=not_a_sitter");
        exit();
    }

    $sitterId = $sitterResult['sitterID'];

    // Update query restricted to the sitter's ID
    $updateStmt = $conn->prepare("
        UPDATE bookings 
        SET status = ? 
        WHERE bookingID = ? AND sitterID = ?
    ");
    $updateStmt->bind_param("sii", $newStatus, $bookingId, $sitterId);
    $redirectPath = "/Pampeers/public/sitter/sitterDashboard.php?booking=updated";

} else {
    
    // ACTION: Guardian cancelling a booking
    if ($newStatus !== 'cancelled') {
        header("Location: /Pampeers/public/guardian/guardianDashboard.php?error=unauthorized_action");
        exit();
    }

    // Update query restricted to the guardian's (user) ID
    $updateStmt = $conn->prepare("
        UPDATE bookings 
        SET status = ? 
        WHERE bookingID = ? AND userID = ?
    ");
    $updateStmt->bind_param("sii", $newStatus, $bookingId, $userId);
    $redirectPath = "/Pampeers/public/guardian/guardianDashboard.php?booking=cancelled";
}

/**
 * 4. EXECUTE & REDIRECT
 */
if ($updateStmt->execute()) {
    $updateStmt->close();
    header("Location: " . $redirectPath);
} else {
    $updateStmt->close();
    header("Location: /Pampeers/public/guestDashboard.php?error=update_failed");
}
exit();
?>