<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth($requiredRole) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: /Pampeers_copyRepo/login?error=unauthorized");
        exit();
    }

    if ($_SESSION['role'] !== $requiredRole) {
        redirectToOwnDashboard($_SESSION['role']);
    }
}

function redirectToOwnDashboard($role) {
    switch ($role) {
        case 'admin':
            header("Location: /Pampeers_copyRepo/app/controllers/admin/adminDashboard.php");
            break;
        case 'guardian':
            header("Location: /Pampeers_copyRepo/app/controllers/guardian/guardianDashboard.php");
            break;
        case 'sitter':
            header("Location: /Pampeers_copyRepo/app/controllers/sitter/sitterDashboard.php");
            break;
        default:
            session_unset();
            session_destroy();
            header("Location: /Pampeers_copyRepo/login?error=invalid_role");
            break;
    }
    exit();
}
?>