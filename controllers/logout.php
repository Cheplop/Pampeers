<?php
session_start();

// 1. Clear session variables
$_SESSION = array();

// 2. Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy session
session_destroy();

// 4. FIX: Redirect to login inside the middleware folder
header("Location: ../middleware/login.php");
exit();
?>