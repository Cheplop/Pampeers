<?php
// Include the config file to get database connection and settings
require_once __DIR__ . '/../config/config.php';

// Function to check if user is logged in
function requireAuth(): void
{
    // If user_id is not set in session, they are not logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page with error message
        header('Location: /pampeers/public/login.php?error=login_required');
        exit();
    }
}

// Function to check if user has a specific role (like admin or sitter)
function requireRole(string $role): void
{
    // First, make sure user is logged in
    requireAuth();

    // Check if the user's role matches the required role
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        // If not, redirect to login with unauthorized error
        header('Location: /pampeers/public/login.php?error=unauthorized');
        exit();
    }
}
?>