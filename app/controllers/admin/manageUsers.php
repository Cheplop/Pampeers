<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('admin');

// Current admin ID
$currentAdminId = $_SESSION['user_id'];

// Fetch users (exclude current admin)
$stmt = $conn->prepare("
    SELECT 
        u.uID,
        u.firstName,
        u.lastName,
        u.email,
        u.role,
        COALESCE(g.city, s.city) AS city
    FROM users u
    LEFT JOIN guardians g ON u.uID = g.uID
    LEFT JOIN sitters s ON u.uID = s.uID
    WHERE u.uID != ?
    ORDER BY u.lastName ASC
");
$stmt->bind_param("i", $currentAdminId);
$stmt->execute();
$result = $stmt->get_result();

// Status message
$message = "";
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted':
            $message = "User successfully removed from the system.";
            break;
        case 'role_updated':
            $message = "User role has been updated.";
            break;
    }
}
?>