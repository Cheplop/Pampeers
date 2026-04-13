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

// Function to create a unique ID for the payment
function generateUUIDv4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Get the user ID from session
$userId = $_SESSION['user_id'];
// Get the booking ID from form, convert to integer
$bookingId = (int) ($_POST['bookingID'] ?? 0);
// Get the payment method from form, remove extra spaces
$paymentMethod = trim($_POST['paymentMethod'] ?? '');

// List of allowed payment methods
$allowedMethods = ['cash', 'gcash', 'paymaya', 'card', 'bank'];

// Check if booking ID is valid and payment method is allowed
if ($bookingId <= 0 || !in_array($paymentMethod, $allowedMethods, true)) {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_payment');
    exit();
}

/*
|--------------------------------------------------------------------------
// Make sure the booking belongs to the logged-in user
|--------------------------------------------------------------------------
*/
$bookingStmt = $conn->prepare("
    SELECT bookingID, totalAmount, status
    FROM bookings
    WHERE bookingID = ? AND userID = ?
    LIMIT 1
");
// Bind booking ID and user ID
$bookingStmt->bind_param("ii", $bookingId, $userId);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();

// If booking not found, redirect with error
if ($bookingResult->num_rows === 0) {
    $bookingStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?error=booking_not_found');
    exit();
}

// Get the booking data
$booking = $bookingResult->fetch_assoc();
$bookingStmt->close();

// Check if the amount is positive
if ((float)$booking['totalAmount'] <= 0) {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_payment_amount');
    exit();
}

/*
|--------------------------------------------------------------------------
// Prevent creating duplicate active payment record
|--------------------------------------------------------------------------
*/
$checkStmt = $conn->prepare("
    SELECT paymentID
    FROM payments
    WHERE bookingID = ?
      AND paymentStatus IN ('pending', 'paid')
    LIMIT 1
");
// Bind booking ID
$checkStmt->bind_param("i", $bookingId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

// If payment already exists, redirect with error
if ($checkResult->num_rows > 0) {
    $checkStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?error=payment_exists');
    exit();
}
$checkStmt->close();

// Create unique ID for the payment
$paymentUUID = generateUUIDv4();
// Get the amount from the booking
$amount = (float) $booking['totalAmount'];
// Set status to pending
$paymentStatus = 'pending';
// Payment date is null for now
$paymentDate = null;

// Prepare to insert the payment into the database
$insertStmt = $conn->prepare("
    INSERT INTO payments (
        uuid,
        bookingID,
        amount,
        paymentMethod,
        paymentStatus,
        paymentDate
    ) VALUES (?, ?, ?, ?, ?, ?)
");

// Bind the values (UUID, booking ID, amount, method, status, date)
$insertStmt->bind_param(
    "sidsss",
    $paymentUUID,
    $bookingId,
    $amount,
    $paymentMethod,
    $paymentStatus,
    $paymentDate
);

// If insert succeeds, redirect with success
if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?payment=created');
    exit();
}

// If failed, redirect with error
$insertStmt->close();
header('Location: /pampeers/public/user/dashboard.php?error=payment_create_failed');
exit();
?>