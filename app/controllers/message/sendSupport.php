<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Check if user is logged in, if not, redirect to login
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

// Get the user ID from the session (who is sending the message)
$senderID = $_SESSION['user_id'];
// Get the message text from the form, remove extra spaces
$messageText = trim($_POST['messageText'] ?? '');

// If the message is empty, redirect with error
if ($messageText === '') {
    header('Location: /pampeers/public/user/dashboard.php?error=empty_support_message');
    exit();
}

/*
|--------------------------------------------------------------------------
| Find one active admin
|--------------------------------------------------------------------------
*/
$adminStmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE role = 'admin'
      AND isActive = 1
      AND deletedAt IS NULL
    ORDER BY id ASC
    LIMIT 1
");
// Run the query to find the admin
$adminStmt->execute();
$adminResult = $adminStmt->get_result();

// If no admin found, close query and redirect with error
if ($adminResult->num_rows === 0) {
    $adminStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?error=no_admin_found');
    exit();
}

// Get the admin's ID from the result
$admin = $adminResult->fetch_assoc();
$adminStmt->close();

// Set the receiver ID to the admin's ID
$receiverID = (int) $admin['id'];

// If sender is the same as receiver (admin sending to self), redirect with error
if ($receiverID === $senderID) {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_support_route');
    exit();
}

// Create a unique ID for this message
$messageUUID = generateUUIDv4();

// Prepare to insert the message into the database
$insertStmt = $conn->prepare("
    INSERT INTO messages (
        uuid,
        senderID,
        receiverID,
        bookingID,
        messageText
    ) VALUES (?, ?, ?, NULL, ?)
");

// Bind the values to the query (UUID, sender, receiver, null for booking, message text)
$insertStmt->bind_param(
    "siis",
    $messageUUID,
    $senderID,
    $receiverID,
    $messageText
);

// If the insert works, close the statement and redirect with success
if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?support=sent');
    exit();
}

// If insert failed, close statement and redirect with error
$insertStmt->close();
header('Location: /pampeers/public/user/dashboard.php?error=support_send_failed');
exit();
?>