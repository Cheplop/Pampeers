<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('admin');

$adminId = $_SESSION['user_id'];

$adminStmt = $conn->prepare("SELECT firstName, lastName, profilePic FROM users WHERE uID = ? LIMIT 1");
$adminStmt->bind_param("i", $adminId);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();
$admin = $adminResult->fetch_assoc();
$adminStmt->close();

$userCountQuery = "SELECT COUNT(*) AS total FROM users";
$userResult = $conn->query($userCountQuery);
$userData = $userResult->fetch_assoc();
$totalUsers = $userData['total'] ?? 0;

$guardianCountQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'guardian'";
$guardianResult = $conn->query($guardianCountQuery);
$guardianData = $guardianResult->fetch_assoc();
$totalGuardians = $guardianData['total'] ?? 0;

$sitterCountQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'sitter'";
$sitterResult = $conn->query($sitterCountQuery);
$sitterData = $sitterResult->fetch_assoc();
$totalSitters = $sitterData['total'] ?? 0;

$recentUsersQuery = "SELECT firstName, lastName, email, role, dateCreated FROM users ORDER BY dateCreated DESC LIMIT 5";
$recentUsersResult = $conn->query($recentUsersQuery);
?>