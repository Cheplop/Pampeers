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
$userRole  = $_SESSION['role'] ?? 'guardian';

// 2. Validate input and allowed status values
$allowedStatuses = ['accepted', 'declined', 'completed', 'cancelled'];
if (!$bookingId || !in_array($newStatus, $allowedStatuses)) {
    header("Location: /Pampeers/public/guestDashboard.php?error=invalid_request");
    exit();
}

/**
 * 3. SECURITY & PERMISSION CHECK
 * We ensure that users can only update bookings they are part of.
 */
if ($userRole === 'sitter') {
    // Sitters can update any status for bookings assigned to them
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

} else {
    // Guardians can ONLY "cancel" their own bookings
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
}

/**
 * 4. EXECUTE & REDIRECT
 */
if ($updateStmt->execute()) {
    $updateStmt->close();

    // Determine the redirect path based on the role
    $redirectPath = ($userRole === 'sitter') 
        ? '/Pampeers/public/sitter/sitterDashboard.php' 
        : '/Pampeers/public/guardian/guardianDashboard.php';

    header("Location: $redirectPath?success=updated");
    exit();
} else {
    // Handle database failure
    header("Location: /Pampeers/public/guestDashboard.php?error=db_error");
    exit();
}