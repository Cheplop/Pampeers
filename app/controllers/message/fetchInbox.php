<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the current user ID from session
$currentUserId = $_SESSION['user_id'];
// Initialize empty array for inbox conversations
$inbox = [];

// Prepare complex query to get latest messages from each conversation
// This query finds the most recent message for each unique conversation pair
$stmt = $conn->prepare("
    SELECT
        u.id AS otherUserID,
        u.firstName,
        u.middleName,
        u.lastName,
        u.suffix,
        u.profilePic,
        u.isActive,
        m.messageText,
        m.sentAt,
        m.senderID
    FROM messages m
    INNER JOIN users u
        ON u.id = CASE
            WHEN m.senderID = ? THEN m.receiverID
            ELSE m.senderID
        END
    INNER JOIN (
        SELECT
            CASE
                WHEN senderID < receiverID THEN senderID
                ELSE receiverID
            END AS user_one,
            CASE
                WHEN senderID < receiverID THEN receiverID
                ELSE senderID
            END AS user_two,
            MAX(sentAt) AS latestSentAt
        FROM messages
        WHERE senderID = ? OR receiverID = ?
        GROUP BY
            CASE
                WHEN senderID < receiverID THEN senderID
                ELSE receiverID
            END,
            CASE
                WHEN senderID < receiverID THEN receiverID
                ELSE senderID
            END
    ) latest
        ON (
            (
                CASE
                    WHEN m.senderID < m.receiverID THEN m.senderID
                    ELSE m.receiverID
                END = latest.user_one
            )
            AND
            (
                CASE
                    WHEN m.senderID < m.receiverID THEN m.receiverID
                    ELSE m.senderID
                END = latest.user_two
            )
            AND m.sentAt = latest.latestSentAt
        )
    WHERE (m.senderID = ? OR m.receiverID = ?)
      AND u.isActive = 1
    ORDER BY m.sentAt DESC
");

// Bind the current user ID five times for the query parameters
$stmt->bind_param("iiiii", $currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

// Array to track users we've already processed to avoid duplicates
$seenUsers = [];

// Loop through each conversation result
while ($row = $result->fetch_assoc()) {
    // Skip if we've already processed this user
    if (in_array($row['otherUserID'], $seenUsers, true)) {
        continue;
    }

    // Build full name from name parts
    $fullName = trim(
        $row['firstName'] .
        (!empty($row['middleName']) ? ' ' . $row['middleName'] : '') .
        ' ' . $row['lastName'] .
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );

    // Add conversation to inbox array
    $inbox[] = [
        'userID'      => $row['otherUserID'],
        'name'        => $fullName,
        'profilePic'  => $row['profilePic'] ?: 'default.jpg',
        'lastMessage' => $row['messageText'],
        'sentAt'      => $row['sentAt'],
        'isMine'      => ((int)$row['senderID'] === $currentUserId), // Check if current user sent the message
    ];

    // Mark this user as seen
    $seenUsers[] = $row['otherUserID'];
}

// Close the statement
$stmt->close();
?>