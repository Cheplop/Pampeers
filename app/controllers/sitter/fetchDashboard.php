<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT
        id,
        firstName,
        middleName,
        lastName,
        suffix,
        cityMunicipality,
        province,
        profilePic,
        role,
        bio
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header('Location: /Pampeers/public/guestDashboard.php?error=session_expired');
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

header('Content-Type: application/json');
echo json_encode($user);