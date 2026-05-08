<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

// Only allow POST requests for deactivation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/guardian/guardianDashboard.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Update user as inactive and log the deactivation time
$stmt = $conn->prepare("
    UPDATE users
    SET isActive = 0, deactivatedAt = NOW()
    WHERE id = ?
");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $stmt->close();

    // Securely clear and destroy the session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Redirect to the guest/login dashboard
    header('Location: /Pampeers/public/guestDashboard.php?account=deactivated');
    exit();
}

$stmt->close();
header('Location: /Pampeers/public/guardian/guardianDashboard.php?error=deactivate_failed');
exit();