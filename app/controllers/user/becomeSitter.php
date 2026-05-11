<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the paths are perfectly pointing to your config and auth files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/login.php");
    exit();
}

// 1. Check if the user has already applied or is already a sitter
$stmt = $conn->prepare("SELECT sitterID, verificationStatus FROM sitters WHERE userID = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $_SESSION['sitter_id'] = $row['sitterID'];
    $stmt->close();
    // Redirect back to profile if they somehow bypass the UI
    header("Location: /Pampeers/public/profile.php?info=already_sitter");
    exit();
}
$stmt->close();

// 2. Generate standard UUID v4 for the sitter profile
function generateUUIDv4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$uuid = generateUUIDv4();

// 3. Insert new sitter record with 'pending' status
// FIX: Explicitly setting default values for all required database columns so it doesn't crash
$insert = $conn->prepare("
    INSERT INTO sitters (uuid, userID, hourlyRate, experience, ratingAverage, isAvailable, verificationStatus) 
    VALUES (?, ?, 0.00, 0, 0.0, 1, 'pending')
");

if ($insert) {
    $insert->bind_param("si", $uuid, $userId);
    
    if ($insert->execute()) {
        $insert->close();
        
        // Send them right back to the profile with a success message!
        header("Location: /Pampeers/public/profile.php?info=application_submitted");
        exit();
    } else {
        $error = $insert->error;
        $insert->close();
        // Fallback for debugging if it still fails
        header("Location: /Pampeers/public/profile.php?error=application_failed&details=" . urlencode($error));
        exit();
    }
} else {
    // Database prepare failed
    header("Location: /Pampeers/public/profile.php?error=db_prepare_failed");
    exit();
}
?>