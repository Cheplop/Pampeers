<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth($requiredRole) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: /pampeers/login?error=unauthorized");
        exit();
    }

    if ($_SESSION['role'] !== $requiredRole) {
        redirectToOwnDashboard($_SESSION['role']);
    }
}

function redirectToOwnDashboard($role) {
    switch ($role) {
        case 'admin':
            header("Location: /pampeers/public/admin/adminDashboard.php");
            break;
        case 'guardian':
            header("Location: /pampeers/public/guardian/guardianDashboard.php");
            break;
        case 'sitter':
            header("Location: /pampeers/public/sitter/sitterDashboard.php");
            break;
        default:
            session_unset();
            session_destroy();
            header("Location: /pampeers/login?error=invalid_role");
            break;
    }
    exit();
}
?>