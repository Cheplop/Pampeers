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
// Get the payment ID from form, convert to integer
$paymentId = (int) ($_POST['paymentID'] ?? 0);
// Get the payment status from form, remove extra spaces
$paymentStatus = trim($_POST['paymentStatus'] ?? '');

// List of allowed payment statuses
$allowedStatuses = ['pending', 'paid', 'failed', 'refunded'];

// Check if payment ID is valid and status is allowed
if ($paymentId <= 0 || !in_array($paymentStatus, $allowedStatuses, true)) {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_payment_status');
    exit();
}

/*
|--------------------------------------------------------------------------
// Make sure the payment belongs to a booking of the logged-in user
|--------------------------------------------------------------------------
*/
$checkStmt = $conn->prepare("
    SELECT p.paymentID
    FROM payments p
    INNER JOIN bookings b ON p.bookingID = b.bookingID
    WHERE p.paymentID = ? AND b.userID = ?
    LIMIT 1
");
// Bind payment ID and user ID
$checkStmt->bind_param("ii", $paymentId, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

// If payment not found, redirect with error
if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?error=payment_not_found');
    exit();
}
$checkStmt->close();

// Set payment date to current time if status is paid, else null
$paymentDate = null;
if ($paymentStatus === 'paid') {
    $paymentDate = date('Y-m-d H:i:s');
}

// Prepare to update the payment status and date
$updateStmt = $conn->prepare("
    UPDATE payments
    SET paymentStatus = ?, paymentDate = ?
    WHERE paymentID = ?
");
// Bind status, date, and payment ID
$updateStmt->bind_param("ssi", $paymentStatus, $paymentDate, $paymentId);

// If update succeeds, redirect with success
if ($updateStmt->execute()) {
    $updateStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?payment=status_updated');
    exit();
}

// If failed, redirect with error
$updateStmt->close();
header('Location: /pampeers/public/user/dashboard.php?error=payment_update_failed');
exit();
?>