<?php
// Include the config file to connect to the database[cite: 2]
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in[cite: 2]
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the user ID and role from the session
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'guardian'; // Default to guardian if not set

$bookings = [];

/**
 * We determine the "Target User" details to fetch:
 * - If I am a Sitter, I want to see the Guardian (User) who booked me.
 * - If I am a Guardian, I want to see the Sitter I booked.
 */
if ($role === 'sitter') {
    // Query for Sitter: Get bookings where someone booked THIS sitter
    $query = "
        SELECT 
            b.*, 
            u.id AS targetID, u.firstName, u.middleName, u.lastName, u.suffix, 
            u.profilePic, u.cityMunicipality, u.province
        FROM bookings b
        INNER JOIN sitters s ON b.sitterID = s.sitterID
        INNER JOIN users u ON b.userID = u.id
        WHERE s.userID = ? AND u.isActive = 1
        ORDER BY b.createdAt DESC";
} else {
    // Query for Guardian: Get bookings where THIS user booked a sitter
    $query = "
        SELECT 
            b.*, 
            s.sitterID AS targetID, u.firstName, u.middleName, u.lastName, u.suffix, 
            u.profilePic, u.cityMunicipality, u.province
        FROM bookings b
        INNER JOIN sitters s ON b.sitterID = s.sitterID
        INNER JOIN users u ON s.userID = u.id
        WHERE b.userID = ? AND u.isActive = 1
        ORDER BY b.createdAt DESC";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Unified Name Construction
    $fullName = trim(
        $row['firstName'] . 
        (!empty($row['middleName']) ? ' ' . $row['middleName'] : '') . 
        ' ' . $row['lastName'] . 
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );

    $bookings[] = [
        'bookingID'      => $row['bookingID'],
        'uuid'           => $row['uuid'],
        'targetID'       => $row['targetID'], // SitterID for Guardians, UserID for Sitters
        'displayName'    => $fullName,
        'profilePic'     => $row['profilePic'] ?: 'default.jpg',
        'city'           => $row['cityMunicipality'],
        'province'       => $row['province'],
        'bookingDate'    => $row['bookingDate'],
        'startTime'      => $row['startTime'],
        'endTime'        => $row['endTime'],
        'hoursRequested' => $row['hoursRequested'],
        'totalAmount'    => $row['totalAmount'],
        'status'         => $row['status'],
        'notes'          => $row['notes'],
        'createdAt'      => $row['createdAt'],
    ];
}

$stmt->close();

// Now $bookings contains everything you need regardless of role.
?>