<?php
// Use __DIR__ to ensure the path is relative to THIS file's location
require_once __DIR__ . '/../config/config.php';

/**
 * Function to check if user is logged in
 */
function requireAuth(): void
{
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        // Redirect to the centralized login/guest dashboard
        header('Location: /Pampeers/public/guestDashboard.php?error=login_required');
        exit();
    }
}

/**
 * Function to check if user has a specific role
 */
function requireRole(string $role): void
{
    requireAuth();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: /Pampeers/public/guestDashboard.php?error=unauthorized');
        exit();
    }
}
?>