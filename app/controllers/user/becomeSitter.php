<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];

// 1. Check if the user has already applied or is already a sitter
$stmt = $conn->prepare("SELECT sitterID, verificationStatus FROM sitters WHERE userID = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $_SESSION['sitter_id'] = $row['sitterID'];
    // Redirect back to profile with a message
    header("Location: /Pampeers/public/profile.php?info=already_sitter");
    exit();
}
$stmt->close();

// 2. Function to generate standard UUID v4 for the sitter profile
function generateUUIDv4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$uuid = generateUUIDv4();

// 3. Insert new sitter record with 'pending' status
// FIX: Removed 'bio' since it now belongs to the 'users' table
$insert = $conn->prepare("
    INSERT INTO sitters (uuid, userID, hourlyRate, experience, isAvailable, verificationStatus)
    VALUES (?, ?, 0, 0, 1, 'pending')
");

$insert->bind_param("si", $uuid, $userId);

if ($insert->execute()) {
    // Set the sitter session ID for immediate use if needed
    $_SESSION['sitter_id'] = $conn->insert_id;
    header("Location: /Pampeers/public/profile.php?success=applied_sitter");
} else {
    header("Location: /Pampeers/public/profile.php?error=application_failed");
}

$insert->close();
exit();