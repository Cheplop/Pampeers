<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

$bookingID = $_GET['bookingID'] ?? null;
$userID = $_SESSION['user_id'];

if ($bookingID) {
    // Make sure the booking belongs to this user before cancelling it
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE bookingID = ? AND userID = ?");
    $stmt->bind_param("ii", $bookingID, $userID);
    $stmt->execute();
    $stmt->close();
}

// Automatically redirect back to the bookings page
header("Location: /Pampeers/public/guardian/myBookings.php");
exit();
?>