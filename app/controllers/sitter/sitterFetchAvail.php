<?php
require_once __DIR__ . '/../../config/config.php';

$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.firstName,
        u.lastName,
        u.profilePic,
        u.cityMunicipality,
        s.hourlyRate,
        s.bio
    FROM users u
    INNER JOIN sitters s ON u.id = s.userID
    WHERE s.isAvailable = 1
");

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