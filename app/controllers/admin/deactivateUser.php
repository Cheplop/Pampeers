<?php
// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';
// Include the auth middleware to check permissions
require_once __DIR__ . '/../../middleware/auth.php';

// Check if the user is an admin, if not, redirect
requireRole('admin');

// Check if the request is a POST request, if not, redirect to admin dashboard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pampeers/public/admin/dashboard.php');
    exit();
}

// Get the user ID to deactivate from the form, convert to integer
$targetUserId = (int)($_POST['userID'] ?? 0);

// If the user ID is not valid (less than or equal to 0), redirect with error
if ($targetUserId <= 0) {
    header('Location: /pampeers/public/admin/dashboard.php?error=invalid_user');
    exit();
}

// Prepare a query to update the user: set isActive to 0 and add deactivation time, but only if not admin
$stmt = $conn->prepare("
    UPDATE users
    SET isActive = 0, deactivatedAt = NOW()
    WHERE id = ? AND role != 'admin'
");
// Bind the user ID to the query
$stmt->bind_param("i", $targetUserId);

// If the update succeeds, close the statement and redirect with success message
if ($stmt->execute()) {
    $stmt->close();
    header('Location: /pampeers/public/admin/dashboard.php?success=user_deactivated');
    exit();
}

// If update failed, close statement and redirect with error
$stmt->close();
header('Location: /pampeers/public/admin/dashboard.php?error=deactivate_failed');
exit();
?>