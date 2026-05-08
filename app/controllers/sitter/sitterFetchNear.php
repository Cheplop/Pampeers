<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

// 1. ADDED: Define $userId so the SQL bind_param doesn't crash!
$userId = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        s.sitterID, -- We need this specific ID for the bookings table
        u.firstName,
        u.lastName,
        u.profilePic,
        u.cityMunicipality,
        s.hourlyRate,
        u.bio, -- 2. CHANGED: from u.bio to u.bio because it's now in the users table
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

// 3. CHANGED: Renamed $sitters to $sittersNear so it doesn't overwrite your available sitters list in the dashboard!
$sittersNear = [];

while ($row = $result->fetch_assoc()) {
    $sittersNear[] = [
        'sitterID' => $row['sitterID'],
        'name'     => trim($row['firstName'] . ' ' . $row['lastName']),
        'img'      => $row['profilePic'] ?: 'default.jpg',
        'city'     => $row['cityMunicipality'],
        'rate'     => $row['hourlyRate'],
        'bio'      => $row['bio']
    ];
}

$stmt->close();