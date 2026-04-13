<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check if user is logged in
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Get the user ID from session
$userId = $_SESSION['user_id'];

// Prepare query to get user and sitter details
$stmt = $conn->prepare("
    SELECT
        u.id,
        u.uuid,
        u.firstName,
        u.middleName,
        u.lastName,
        u.suffix,
        u.birthDate,
        u.sex,
        u.role,
        u.contactNumber,
        u.emailAddress,
        u.username,
        u.streetAddress,
        u.barangay,
        u.cityMunicipality,
        u.province,
        u.country,
        u.zipCode,
        u.profilePic,
        u.dateCreated,
        u.isActive,

        s.sitterID,
        s.bio,
        s.hourlyRate,
        s.experience,
        s.isAvailable,
        s.ratingAverage,
        s.verificationStatus,
        s.createdAt AS sitterCreatedAt
    FROM users u
    INNER JOIN sitters s ON s.userID = u.id
    WHERE u.id = ?
      AND u.isActive = 1
    LIMIT 1
");
// Bind user ID
$stmt->bind_param("i", $userId);
// Run the query
$stmt->execute();
$result = $stmt->get_result();

// If no result, user is not active, logout
if ($result->num_rows === 0) {
    $stmt->close();
    session_unset();
    session_destroy();
    header('Location: /pampeers/public/login.php?error=account_deactivated');
    exit();
}

// Get the user data
$user = $result->fetch_assoc();
$stmt->close();

// Build the full name
$fullName = trim(
    $user['firstName'] .
    (!empty($user['middleName']) ? ' ' . $user['middleName'] : '') .
    ' ' . $user['lastName'] .
    (!empty($user['suffix']) ? ' ' . $user['suffix'] : '')
);

// Calculate age from birth date
$age = 'N/A';

if (!empty($user['birthDate'])) {
    try {
        $birthDate = new DateTime($user['birthDate']);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;
    } catch (Exception $e) {
        $age = 'N/A';
    }
}

// Build location string from address parts
$locationParts = array_filter([
    $user['barangay'] ?? '',
    $user['cityMunicipality'] ?? '',
    $user['province'] ?? '',
    $user['country'] ?? ''
]);

$location = !empty($locationParts) ? implode(', ', $locationParts) : 'N/A';
// Set availability text
$availability = ((int) $user['isAvailable'] === 1) ? 'Available' : 'Unavailable';
?>