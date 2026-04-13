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
        uuid,
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
        dateCreated,
        isActive
    FROM users
    WHERE id = ?
    LIMIT 1
");
// Bind user ID
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();

// If user not found, logout
if ($userResult->num_rows === 0) {
    $userStmt->close();
    session_unset();
    session_destroy();
    header('Location: /pampeers/public/login.php?error=user_not_found');
    exit();
}

// Fetch user data
$user = $userResult->fetch_assoc();
$userStmt->close();

// If user is not active, logout
if ((int)$user['isActive'] !== 1) {
    session_unset();
    session_destroy();
    header('Location: /pampeers/public/login.php?error=account_deactivated');
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

// Loop through results and build sitter array
while ($row = $sittersResult->fetch_assoc()) {
    // Build full name from parts
    $fullName = trim(
        $row['firstName'] . ' ' .
        (!empty($row['middleName']) ? $row['middleName'] . ' ' : '') .
        $row['lastName'] .
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );

    // Add sitter to array
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
$nearStmt = $conn->prepare("
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
      AND u.cityMunicipality = ?
      AND u.id != ?
    ORDER BY s.createdAt DESC
");
// Bind city and exclude current user ID
$nearStmt->bind_param("si", $user['cityMunicipality'], $userId);
$nearStmt->execute();
$nearResult = $nearStmt->get_result();

// Loop through results and build near sitters array
while ($row = $nearResult->fetch_assoc()) {
    // Build full name from parts
    $fullName = trim(
        $row['firstName'] . ' ' .
        (!empty($row['middleName']) ? $row['middleName'] . ' ' : '') .
        $row['lastName'] .
        (!empty($row['suffix']) ? ' ' . $row['suffix'] : '')
    );

    // Add sitter to near array
    $sittersNear[] = [
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
$nearStmt->close();
?>