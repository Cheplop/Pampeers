<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/admin/adminDashboard.php');
    exit();
}

$targetUserId = (int)($_POST['userID'] ?? 0);
if ($targetUserId <= 0) {
    header('Location: /Pampeers/public/admin/adminDashboard.php?error=invalid_user');
    exit();
}

$stmt = $conn->prepare("UPDATE users SET isActive = 0, deletedAt = NOW() WHERE id = ? AND role != 'admin'");
$stmt->bind_param("i", $targetUserId);
if ($stmt->execute()) {
    header('Location: /Pampeers/public/admin/adminDashboard.php?success=deleted');
} else {
    header('Location: /Pampeers/public/admin/adminDashboard.php?error=delete_failed');
}
$stmt->close();