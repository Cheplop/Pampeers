<?php

require_once __DIR__ . '/../../config/db_connect.php';

$stmt = $conn->prepare("
    SELECT 
        u.uID,
        u.firstName,
        u.lastName,
        u.profilePic,
        u.city,
        s.hourlyRate,
        s.bio
    FROM sitters s
    INNER JOIN users u ON s.uID = u.uID
    WHERE u.city = ? AND s.isAvailable = 1
" );

$stmt->bind_param('s', $userCity);

$stmt->execute();
$result = $stmt->get_result();

$sittersNear = [];

while ($row = $result->fetch_assoc()) {

    $fullName = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));

    $sittersNear[] = [
        'id' => $row['uID'],
        'name' => $fullName,
        'img' => $row['profilePic'],
        'city' => $row['city'] ?? '',
        'rate' => $row['hourlyRate'],
        'bio' => $row['bio'],
    ];
}

$stmt->close();