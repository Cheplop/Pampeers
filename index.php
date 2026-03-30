<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
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

header("Location: /Pampeers_copyRepo/login");
exit();
?>