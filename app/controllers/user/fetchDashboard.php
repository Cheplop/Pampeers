<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the user ID from session
$userId = $_SESSION['user_id'];


// Prepare statement to get user details
$userStmt = $conn->prepare("
    SELECT
        id,
        firstName,
        middleName,
        lastName,
        suffix,
        birthDate,
        sex,
        role,
        contactNumber,
        emailAddress,
        username,
        streetAddress,
        barangay,
        cityMunicipality,
        province,
        country,
        zipCode,
        profilePic,
        isActive
    FROM users
    WHERE id = ?
    LIMIT 1
");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

// If user not found, logout
if ($userResult->num_rows === 0) {
    $userStmt->close();
    session_unset();
    session_destroy();
    // Show message and redirect to guestDashboard after 3 seconds
    echo '<!DOCTYPE html><html><head>';
    echo '<meta http-equiv="refresh" content="3;url=/Pampeers/public/guestDashboard.php">';
    echo '<title>User Not Found</title></head><body>';
    echo '<h2>User not found. Redirecting to guest dashboard...</h2>';
    echo '</body></html>';
    exit();
}

// Fetch user data
$user = $userResult->fetch_assoc();
$userStmt->close();

// If user is not active, logout
if ((int)$user['isActive'] !== 1) {
    session_unset();
    session_destroy();
    header('Location: /Pampeers/public/login.php?error=account_deactivated');
    exit();
}

// Initialize array for all available sitters
$sitters = [];

// Prepare statement to get all available sitters with user info
$sittersStmt = $conn->prepare("
    SELECT
        s.sitterID,
        s.uuid AS sitterUUID,
        s.bio,
        s.hourlyRate,
        s.experience,
        s.isAvailable,
        s.ratingAverage,
        s.verificationStatus,
        u.id AS userID,
        u.firstName,
        u.middleName,
        u.lastName,
        u.suffix,
        u.cityMunicipality,
        u.province,
        u.profilePic
    FROM sitters s
    INNER JOIN users u ON s.userID = u.id
    WHERE s.isAvailable = 1
      AND u.isActive = 1
    ORDER BY s.createdAt DESC
");
$sittersStmt->execute();
$sittersResult = $sittersStmt->get_result();
$sitters = [];
while ($row = $sittersResult->fetch_assoc()) {
    $fullName = trim(
        $row['firstName'] . ' ' .
        (!empty($row['middleName']) ? $row['middleName'] . ' ' : '') .
        $row['lastName'] .
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );
    $sitters[] = [
        'sitterID'   => $row['sitterID'],
        'uuid'       => $row['sitterUUID'],
        'userID'     => $row['userID'],
        'name'       => $fullName,
        'bio'        => $row['bio'],
        'rate'       => $row['hourlyRate'],
        'experience' => $row['experience'],
        'available'  => $row['isAvailable'],
        'rating'     => $row['ratingAverage'],
        'verified'   => $row['verificationStatus'],
        'city'       => $row['cityMunicipality'],
        'province'   => $row['province'],
        'img'        => $row['profilePic'] ?: 'default.jpg'
    ];
}
$sittersStmt->close();

// Initialize array for sitters near the user
$sittersNear = [];

// Prepare statement to get sitters in same city, excluding current user
// ======================
// ALL AVAILABLE SITTERS
// ======================
$sitters = [];


$sittersStmt = $conn->prepare("
    SELECT
        s.sitterID,
        s.bio,
        s.hourlyRate,
        s.experience,
        s.isAvailable,
        u.id,
        u.firstName,
        u.middleName,
        u.lastName,
        u.suffix,
        u.cityMunicipality,
        u.province,
        u.profilePic
    FROM sitters s
    INNER JOIN users u ON s.userID = u.id
    WHERE s.isAvailable = 1
    ORDER BY s.sitterID DESC
");

$sittersStmt->execute();
$sittersResult = $sittersStmt->get_result();

while ($row = $sittersResult->fetch_assoc()) {
    $fullName = trim(
        $row['firstName'] . ' ' .
        (!empty($row['middleName']) ? $row['middleName'] . ' ' : '') .
        $row['lastName'] .
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );

    $sitters[] = [
        'id'   => $row['id'],
        'name' => $fullName,
        'img'  => $row['profilePic'] ?: 'default.jpg',
        'city' => $row['cityMunicipality'] ?? '',
        'rate' => $row['hourlyRate'],
        'bio'  => $row['bio']
    ];
}
$sittersStmt->close();


// ======================
// SITTERS NEAR USER
// ======================
$sittersNear = [];

$nearStmt = $conn->prepare("
    SELECT
        s.sitterID,
        s.bio,
        s.hourlyRate,
        u.id,
        u.firstName,
        u.middleName,
        u.lastName,
        u.suffix,
        u.cityMunicipality,
        u.profilePic
    FROM sitters s
    INNER JOIN users u ON s.userID = u.id
    WHERE s.isAvailable = 1
      AND u.cityMunicipality = ?
");

$nearStmt->bind_param("s", $user['cityMunicipality']);
$nearStmt->execute();
$nearResult = $nearStmt->get_result();

while ($row = $nearResult->fetch_assoc()) {
    $fullName = trim(
        $row['firstName'] . ' ' .
        (!empty($row['middleName']) ? $row['middleName'] . ' ' : '') .
        $row['lastName']
    );

    $sittersNear[] = [
        'id'   => $row['id'],
        'name' => $fullName,
        'img'  => $row['profilePic'] ?: 'default.jpg',
        'city' => $row['cityMunicipality'] ?? '',
        'rate' => $row['hourlyRate'],
        'bio'  => $row['bio']
    ];
}
$nearStmt->close();