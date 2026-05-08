<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

$userId = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        s.sitterID, -- We need this specific ID for the bookings table
        u.firstName,
        u.lastName,
        u.profilePic,
        u.cityMunicipality,
        s.hourlyRate,
        u.bio, -- CHANGED from u.bio to u.bio because you moved it to the users table!
        s.verificationStatus,
        s.isAvailable
    FROM users u
    INNER JOIN sitters s ON u.id = s.userID
    WHERE s.isAvailable = 1
      AND s.verificationStatus = 'verified'
      AND u.isActive = 1
      AND s.userID != ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$sitters = [];

while ($row = $result->fetch_assoc()) {
    $sitters[] = [
        'sitterID' => $row['sitterID'],
        'name'     => trim($row['firstName'] . ' ' . $row['lastName']),
        'img'      => $row['profilePic'] ?: 'default.jpg',
        'city'     => $row['cityMunicipality'],
        'rate'     => $row['hourlyRate'],
        'bio'      => $row['bio']
    ];
}

$stmt->close();