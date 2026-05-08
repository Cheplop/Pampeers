<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/guestDashboard.php');
    exit();
}

// Accepts either 'login' or 'username' depending on how the form named it
$loginInput = trim($_POST['login'] ?? $_POST['username'] ?? '');
$password   = $_POST['password'] ?? '';

if ($loginInput === '' || $password === '') {
    header('Location: /Pampeers/public/guestDashboard.php?error=missing');
    exit();
}

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE emailAddress = ? OR username = ?
    LIMIT 1
");

$stmt->bind_param("ss", $loginInput, $loginInput);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /Pampeers/public/guestDashboard.php?error=invalid');
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

/* ================= CHECK ACCOUNT ================= */
if ((int)$user['isActive'] === 0) {
    header('Location: /Pampeers/public/guestDashboard.php?error=deactivated');
    exit();
}

if (!password_verify($password, $user['password'])) {
    header('Location: /Pampeers/public/guestDashboard.php?error=wrongpass');
    exit();
}

/* ================= SESSION ================= */
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['firstName'];

/* ================= SITTER CHECK ================= */
$stmt = $conn->prepare("SELECT sitterID FROM sitters WHERE userID = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $_SESSION['sitter_id'] = $res->fetch_assoc()['sitterID'];
}

/* ================= REDIRECT ================= */
if ($user['role'] === 'admin') {
    header("Location: /Pampeers/public/admin/adminDashboard.php");
} else {
    header("Location: /Pampeers/public/guardian/guardianDashboard.php");
}

exit();