<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

if (!$conn) {
    header("Location: /Pampeers/public/guestDashboard.php?error=db");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Reset session (fresh login)
    session_unset();
    session_destroy();
    session_start();

    if (!empty($_POST['email']) && !empty($_POST['password'])) {

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /Pampeers/public/guestDashboard.php?error=invalid");
            exit();
        }

        // ✅ FIXED: correct column names
        $stmt = $conn->prepare("
            SELECT id, firstName, password, role
            FROM users
            WHERE emailAddress = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {

            if (password_verify($password, $user['password'])) {

                // ✅ FIXED: use id instead of uID
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['firstName'] = $user['firstName'];

                redirectBasedOnRole($user['role']);
            }
        }

        $stmt->close();
    }

    header("Location: /Pampeers/public/guestDashboard.php?error=invalid");
    exit();
}

// Existing session redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirectBasedOnRole($_SESSION['role']);
} else {
    header("Location: /Pampeers/public/guestDashboard.php");
    exit();
}

// ==============================
// ROLE REDIRECTION
// ==============================
function redirectBasedOnRole($role) {
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
            header("Location: /Pampeers/public/guestDashboard.php?error=role_not_found");
            break;
    }
    exit();
}
?>