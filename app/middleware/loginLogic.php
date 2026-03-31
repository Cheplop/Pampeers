<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirectBasedOnRole($_SESSION['role']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /Pampeers_copyRepo/login?error=invalid");
            exit();
        }

        $stmt = $conn->prepare("SELECT uID, firstName, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['uID'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['firstName'] = $user['firstName'];

                redirectBasedOnRole($user['role']);
            }
        }

        $stmt->close();
    }

    header("Location: /Pampeers_copyRepo/login?error=invalid");
    exit();
}

function redirectBasedOnRole($role) {
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
            header("Location: /Pampeers_copyRepo/login?error=role_not_found");
            break;
    }
    exit();
}
?>