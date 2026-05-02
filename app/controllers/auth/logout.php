<?php
// Include the config file for database connection
require_once __DIR__ . '/../../config/config.php';

/*
|--------------------------------------------------------------------------
// Clear and destroy the user's session
|--------------------------------------------------------------------------
*/
// Empty the session array
$_SESSION = [];

// Unset all session variables
session_unset();
// Destroy the session
session_destroy();

/*
|--------------------------------------------------------------------------
// Redirect to login page with success message
|--------------------------------------------------------------------------
*/
header('Location: /pampeers/public/login.php?logout=success');
exit();
?>