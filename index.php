<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
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

header("Location: /Pampeers/public/guestDashboard.php");
exit();
?>