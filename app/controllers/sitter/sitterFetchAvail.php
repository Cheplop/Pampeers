<?php

require_once __DIR__ . '/../../config/db_connect.php';

$stmt = $conn->prepare("
    SELECT 
        u.uID,
        u.firstName,
        u.lastName,
        u.profilePic,
        s.hourlyRate,
        s.bio,
        s.city,
        s.isAvailable
    FROM users u
    INNER JOIN sitters s ON u.uID = s.uID
    WHERE s.isAvailable = 1
");

$stmt->execute();
$result = $stmt->get_result();

$sitters = [];

while ($row = $result->fetch_assoc()) {

    $fullName = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));

    $sitters[] = [
        'id' => $row['uID'],
        'name' => $fullName,
        'img' => $row['profilePic'],
        'rate' => $row['hourlyRate'],
        'bio' => $row['bio'],
        'city' => $row['city']
    ];
}

$stmt->close();