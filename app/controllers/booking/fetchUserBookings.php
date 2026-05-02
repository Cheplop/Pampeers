<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the user ID from the session
$userId = $_SESSION['user_id'];
// Initialize an empty array to store the bookings
$userBookings = [];

// Prepare a query to get all bookings for this user, joining with sitters and users table for sitter details
$stmt = $conn->prepare("
    SELECT
        b.bookingID,
        b.uuid,
        b.bookingDate,
        b.startTime,
        b.endTime,
        b.hoursRequested,
        b.totalAmount,
        b.status,
        b.notes,
        b.createdAt,

        s.sitterID,
        u.firstName,
        u.middleName,
        u.lastName,
        u.suffix,
        u.profilePic,
        u.cityMunicipality,
        u.province
    FROM bookings b
    INNER JOIN sitters s ON b.sitterID = s.sitterID
    INNER JOIN users u ON s.userID = u.id
    WHERE b.userID = ?
      AND u.isActive = 1
    ORDER BY b.createdAt DESC
");
// Bind the user ID
$stmt->bind_param("i", $userId);
// Run the query
$stmt->execute();
$result = $stmt->get_result();

// Loop through each booking result
while ($row = $result->fetch_assoc()) {
    // Build the full name from first, middle, last, suffix
    $fullName = trim(
        $row['firstName'] .
        (!empty($row['middleName']) ? ' ' . $row['middleName'] : '') .
        ' ' . $row['lastName'] .
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );

    // Add the booking data to the array
    $userBookings[] = [
        'bookingID'      => $row['bookingID'],
        'uuid'           => $row['uuid'],
        'sitterID'       => $row['sitterID'],
        'sitterName'     => $fullName,
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

// Close the statement
$stmt->close();
?>