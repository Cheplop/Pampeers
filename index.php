<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
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

header("Location: /pampeers/public/login.php");
exit();
?>