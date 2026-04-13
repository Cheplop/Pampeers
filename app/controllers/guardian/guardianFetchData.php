<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('guardian');

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT
        u.firstName,
        u.lastName,
        u.email,
        u.birthdate,
        u.profilePic,
        u.street,
        u.city,
        u.country,
        u.contactNumber
    FROM users u
    INNER JOIN guardians g ON u.uID = g.uID
    WHERE u.uID = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Guardian profile not found.");
}

$user = $result->fetch_assoc();
$stmt->close();

$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));

$locationParts = array_filter([
    $user['street'] ?? '',
    $user['city'] ?? '',
    $user['country'] ?? ''
]);
$location = !empty($locationParts) ? implode(', ', $locationParts) : 'N/A';

$age = 'N/A';
if (!empty($user['birthdate'])) {
    $bday = new DateTime($user['birthdate']);
    $today = new DateTime('today');
    $age = $bday->diff($today)->y;
}
?>