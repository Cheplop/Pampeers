<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the user ID from session
$userId = $_SESSION['user_id'];
// Get the booking ID from GET parameters, convert to integer
$bookingId = (int) ($_GET['bookingID'] ?? 0);

// Initialize payment variable to null
$payment = null;

// If booking ID is valid
if ($bookingId > 0) {
    // Prepare query to get payment for this booking, ensuring it belongs to the user
    $stmt = $conn->prepare("
        SELECT
            p.paymentID,
            p.uuid,
            p.bookingID,
            p.amount,
            p.paymentMethod,
            p.paymentStatus,
            p.paymentDate,
            p.createdAt
        FROM payments p
        INNER JOIN bookings b ON p.bookingID = b.bookingID
        WHERE p.bookingID = ?
          AND b.userID = ?
        LIMIT 1
    ");
    // Bind booking ID and user ID
    $stmt->bind_param("ii", $bookingId, $userId);
    // Run the query
    $stmt->execute();
    $result = $stmt->get_result();

    // If payment found, get the data
    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
    }

    // Close the statement
    $stmt->close();
}
?>