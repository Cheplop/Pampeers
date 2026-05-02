<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];

/* CHECK IF ALREADY SITTER */
$stmt = $conn->prepare("SELECT sitterID, verificationStatus FROM sitters WHERE userID = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $_SESSION['sitter_id'] = $res->fetch_assoc()['sitterID'];
    header("Location: /Pampeers/public/profile.php?info=already_sitter");
    exit();
}

/* CREATE SITTER (ALWAYS PENDING) */
$uuid = bin2hex(random_bytes(16));

$insert = $conn->prepare("
    INSERT INTO sitters (uuid, userID, bio, hourlyRate, experience, isAvailable, verificationStatus)
    VALUES (?, ?, '', 0, 0, 1, 'pending')
");

$insert->bind_param("si", $uuid, $userId);

if ($insert->execute()) {
    
    // ✅ CREATE SITTER SESSION ID
    $_SESSION['sitter_id'] = $conn->insert_id;

    header("Location: /Pampeers/public/profile.php?success=waiting_verification");
    exit();
}

header("Location: /Pampeers/public/profile.php?error=failed");
exit();