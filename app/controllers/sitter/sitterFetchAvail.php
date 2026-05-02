<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

$userId = $_SESSION['user_id'] ?? 0;

$userId = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.firstName,
        u.lastName,
        u.profilePic,
        u.cityMunicipality,
        s.hourlyRate,
        s.bio,
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
        'id'   => $row['id'],
        'name' => trim($row['firstName'] . ' ' . $row['lastName']),
        'img'  => $row['profilePic'] ?: 'default.jpg',
        'city' => $row['cityMunicipality'],
        'rate' => $row['hourlyRate'],
        'bio'  => $row['bio']
    ];
}

$stmt->close();