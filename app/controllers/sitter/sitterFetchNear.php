<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

$userId = $_SESSION['user_id'] ?? 0;

// 1. First, get the current user's city to find people "Nearby"
$userCity = "";
$cityStmt = $conn->prepare("SELECT cityMunicipality FROM users WHERE id = ?");
$cityStmt->bind_param("i", $userId);
$cityStmt->execute();
$userRes = $cityStmt->get_result()->fetch_assoc();
$userCity = $userRes['cityMunicipality'] ?? '';
$cityStmt->close();

// 2. Fetch sitters in the same city
$stmt = $conn->prepare("
    SELECT 
        s.sitterID, 
        u.firstName,
        u.lastName,
        u.profilePic,
        u.cityMunicipality,
        s.hourlyRate,
        u.bio, 
        s.verificationStatus,
        s.isAvailable
    FROM users u
    INNER JOIN sitters s ON u.id = s.userID
    WHERE s.isAvailable = 1
      AND s.verificationStatus = 'verified'
      AND u.isActive = 1
      AND u.cityMunicipality = ? 
      AND s.userID != ?
");

$stmt->bind_param("si", $userCity, $userId);
$stmt->execute();
$result = $stmt->get_result();

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

header('Content-Type: application/json');
echo json_encode($sittersNear);