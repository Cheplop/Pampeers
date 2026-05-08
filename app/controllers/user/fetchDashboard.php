<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the user ID from session
$userId = $_SESSION['user_id'];

// 1. Fetch User Profile Details
$userStmt = $conn->prepare("
    SELECT
        id, firstName, middleName, lastName, suffix,
        cityMunicipality, province, profilePic, role
    FROM users
    WHERE id = ?
    LIMIT 1
");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    $userStmt->close();
    session_unset();
    session_destroy();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not found']);
    exit();
}

$user = $userResult->fetch_assoc();
$userStmt->close();

// 2. Fetch "Explore" Sitters (Available and Verified)
$sitters = [];
$sittersStmt = $conn->prepare("
    SELECT
        s.sitterID, s.hourlyRate, s.bio,
        u.id as userID, u.firstName, u.middleName, u.lastName, u.suffix,
        u.cityMunicipality, u.profilePic
    FROM sitters s
    INNER JOIN users u ON s.userID = u.id
    WHERE s.isAvailable = 1 
      AND s.verificationStatus = 'verified'
      AND u.isActive = 1
      AND u.id != ?
    LIMIT 12
");
$sittersStmt->bind_param("i", $userId);
$sittersStmt->execute();
$sittersResult = $sittersStmt->get_result();

while ($row = $sittersResult->fetch_assoc()) {
    $fullName = trim($row['firstName'] . ' ' . $row['lastName'] . ' ' . ($row['suffix'] ?? ''));
    $sitters[] = [
        'sitterID' => $row['sitterID'],
        'name'     => $fullName,
        'img'      => $row['profilePic'] ?: 'default.jpg',
        'city'     => $row['cityMunicipality'],
        'rate'     => $row['hourlyRate'],
        'bio'      => $row['bio']
    ];
}
$sittersStmt->close();

// 3. Fetch "Nearby" Sitters (Same City)
$sittersNear = [];
$nearStmt = $conn->prepare("
    SELECT
        s.sitterID, s.hourlyRate, s.bio,
        u.firstName, u.lastName, u.cityMunicipality, u.profilePic
    FROM sitters s
    INNER JOIN users u ON s.userID = u.id
    WHERE s.isAvailable = 1
      AND s.verificationStatus = 'verified'
      AND u.cityMunicipality = ?
      AND u.id != ?
    LIMIT 6
");
$nearStmt->bind_param("si", $user['cityMunicipality'], $userId);
$nearStmt->execute();
$nearResult = $nearStmt->get_result();

while ($row = $nearResult->fetch_assoc()) {
    $sittersNear[] = [
        'sitterID' => $row['sitterID'],
        'name'     => trim($row['firstName'] . ' ' . $row['lastName']),
        'img'      => $row['profilePic'] ?: 'default.jpg',
        'city'     => $row['cityMunicipality'],
        'rate'     => $row['hourlyRate']
    ];
}
$nearStmt->close();

// Return combined data as JSON
header('Content-Type: application/json');
echo json_encode([
    'user'        => $user,
    'sitters'     => $sitters,
    'sittersNear' => $sittersNear
]);