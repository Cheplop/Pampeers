<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the current user ID from session
$currentUserId = $_SESSION['user_id'];
// Get the other user ID from GET parameters (receiverID or userID)
$otherUserId   = (int) ($_GET['receiverID'] ?? $_GET['userID'] ?? 0);

// Initialize empty array for conversation messages
$conversation = [];
// Initialize variable for other user's info
$otherUser = null;

// If other user ID is valid
if ($otherUserId > 0) {
    // Query to get the other user's details
    $userStmt = $conn->prepare("
        SELECT id, firstName, middleName, lastName, suffix, profilePic, isActive
        FROM users
        WHERE id = ?
          AND isActive = 1
        LIMIT 1
    ");
    // Bind the other user ID
    $userStmt->bind_param("i", $otherUserId);
    // Run the query
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    // If user found, get their data
    if ($userResult->num_rows > 0) {
        $otherUser = $userResult->fetch_assoc();
    }
    // Close the statement
    $userStmt->close();

    // If other user exists
    if ($otherUser) {
        // Query to get all messages between current user and other user
        $msgStmt = $conn->prepare("
            SELECT
                m.messageID,
                m.uuid,
                m.senderID,
                m.receiverID,
                m.bookingID,
                m.messageText,
                m.sentAt
            FROM messages m
            WHERE (m.senderID = ? AND m.receiverID = ?)
               OR (m.senderID = ? AND m.receiverID = ?)
            ORDER BY m.sentAt ASC
        ");
        // Bind current and other user IDs twice for the OR conditions
        $msgStmt->bind_param("iiii", $currentUserId, $otherUserId, $otherUserId, $currentUserId);
        // Run the query
        $msgStmt->execute();
        $msgResult = $msgStmt->get_result();

        // Loop through each message and add to conversation array
        while ($row = $msgResult->fetch_assoc()) {
            $conversation[] = $row;
        }

        // Close the statement
        $msgStmt->close();
    }
}
?>