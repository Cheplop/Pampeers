<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Ensure the user is logged in
requireAuth();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');

    // Update the bio in the users table
    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->bind_param("si", $bio, $userId);

    if ($stmt->execute()) {
        // Redirect back to profile with a success message
        header("Location: /Pampeers/public/profile.php?success=bio_updated");
    } else {
        // Redirect back with an error
        header("Location: /Pampeers/public/profile.php?error=update_failed");
    }
    
    $stmt->close();
}
exit();