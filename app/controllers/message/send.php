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

// Function to create a unique ID for the message
function generateUUIDv4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Get the sender ID from session
$senderID    = $_SESSION['user_id'];
// Get the receiver ID from form, convert to integer
$receiverID  = (int) ($_POST['receiverID'] ?? 0);
// Get the booking ID if provided, else null
$bookingID   = !empty($_POST['bookingID']) ? (int) $_POST['bookingID'] : null;
// Get the message text, remove extra spaces
$messageText = trim($_POST['messageText'] ?? '');

// Check if receiver ID is valid and message is not empty
if ($receiverID <= 0 || $messageText === '') {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_message');
    exit();
}

// Prevent sending message to self
if ($receiverID === $senderID) {
    header('Location: /pampeers/public/user/dashboard.php?error=self_message_not_allowed');
    exit();
}

// Check if the receiver user exists and is active
$checkStmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE id = ?
      AND isActive = 1
    LIMIT 1
");
// Bind the receiver ID
$checkStmt->bind_param("i", $receiverID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

// If receiver not found, redirect with error
if ($checkResult->num_rows === 0) {
    $checkStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?error=receiver_not_found');
    exit();
}
$checkStmt->close();

// Create unique ID for the message
$messageUUID = generateUUIDv4();

// Prepare to insert the message into the database
$insertStmt = $conn->prepare("
    INSERT INTO messages (
        uuid,
        senderID,
        receiverID,
        bookingID,
        messageText
    ) VALUES (?, ?, ?, ?, ?)
");

// Bind the values (UUID, sender, receiver, booking, message)
$insertStmt->bind_param(
    "siiis",
    $messageUUID,
    $senderID,
    $receiverID,
    $bookingID,
    $messageText
);

// If insert succeeds, redirect to messages page with success
if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: /pampeers/public/user/messages.php?send=success&receiverID=' . $receiverID);
    exit();
}

// If failed, redirect with error
$insertStmt->close();
header('Location: /pampeers/public/user/messages.php?error=send_failed');
exit();
?>