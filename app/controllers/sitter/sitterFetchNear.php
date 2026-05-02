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
    FROM sitters s
    INNER JOIN users u ON s.userID = u.id
    WHERE s.isAvailable = 1
      AND u.cityMunicipality = ?
");

$stmt->bind_param('s', $userCity);
$stmt->execute();
$result = $stmt->get_result();

$sittersNear = [];

while ($row = $result->fetch_assoc()) {
    $sittersNear[] = [
        'id'   => $row['id'],
        'name' => trim($row['firstName'] . ' ' . $row['lastName']),
        'img'  => $row['profilePic'] ?: 'default.jpg',
        'city' => $row['cityMunicipality'],
        'rate' => $row['hourlyRate'],
        'bio'  => $row['bio']
    ];
}

$stmt->close();