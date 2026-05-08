<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'guardian';

$bookings = [];

// Both queries now select b.* (which includes endDate)
if ($role === 'sitter') {
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
    $query = "
        SELECT 
            b.*, 
            u.id AS targetID, u.firstName, u.middleName, u.lastName, u.suffix, 
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
    $fullName = trim(
        $row['firstName'] . 
        (!empty($row['middleName']) ? ' ' . $row['middleName'] : '') . 
        ' ' . $row['lastName'] . 
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );

    $bookings[] = [
        'bookingID'      => $row['bookingID'],
        'uuid'           => $row['uuid'],
        'targetID'       => $row['targetID'],
        'displayName'    => $fullName,
        'profilePic'     => $row['profilePic'] ?: 'default.jpg',
        'city'           => $row['cityMunicipality'],
        'province'       => $row['province'],
        'startDate'      => $row['bookingDate'], // bookingDate is used as Start Date
        'endDate'        => $row['endDate'],     // Newly added field
        'startTime'      => $row['startTime'],
        'endTime'        => $row['endTime'],
        'totalAmount'    => $row['totalAmount'],
        'status'         => $row['status'],
        'notes'          => $row['notes'],
        'createdAt'      => $row['createdAt']
    ];
}

header('Content-Type: application/json');
echo json_encode($bookings);