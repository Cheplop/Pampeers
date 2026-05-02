<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth($allowedRoles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: /Pampeers/public/guestDashboard.php?error=unauthorized");
        exit();
    }

    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        redirectToOwnDashboard($_SESSION['role']);
    }
}

function redirectToOwnDashboard($role) {
    switch ($role) {
        case 'admin':
            header("Location: /Pampeers/public/admin/adminDashboard.php");
            break;
        case 'guardian':
            header("Location: /Pampeers/public/guardian/guardianDashboard.php");
            break;
        case 'sitter':
            header("Location: /Pampeers/public/sitter/sitterDashboard.php");
            break;
        default:
            session_unset();
            session_destroy();
            header("Location: /Pampeers/public/guestDashboard.php?error=invalid_role");
            break;
    }
    exit();
}
?>