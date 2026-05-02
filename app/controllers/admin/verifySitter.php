<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireRole('admin');

$sitterId = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$sitterId || !$action) {
    header("Location: /Pampeers/public/admin/adminDashboard.php?error=invalid");
    exit();
}

if ($action === 'approve') {
    $stmt = $conn->prepare("
        UPDATE sitters 
        SET verificationStatus = 'verified'
        WHERE sitterID = ?
    ");
    $stmt->bind_param("i", $sitterId);
    $stmt->execute();
    $stmt->close();
}

if ($action === 'reject') {
    $stmt = $conn->prepare("
        UPDATE sitters 
        SET verificationStatus = 'rejected'
        WHERE sitterID = ?
    ");
    $stmt->bind_param("i", $sitterId);
    $stmt->execute();
    $stmt->close();
}

header("Location: /Pampeers/public/admin/adminDashboard.php?success=updated");
exit();