<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('admin');

// DELETE USER
if (isset($_GET['delete'])) {
    $userId = (int) $_GET['delete'];

    // Prevent deleting yourself
    if ($userId === $_SESSION['user_id']) {
        header("Location: manageUsers.php?status=error_self_delete");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE uID = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        header("Location: manageUsers.php?status=deleted");
    } else {
        header("Location: manageUsers.php?status=error");
    }

    $stmt->close();
    exit();
}

// UPDATE ROLE
if (isset($_POST['updateRole'])) {
    $userId = (int) $_POST['userId'];
    $newRole = $_POST['role'];

    $allowedRoles = ['guardian', 'sitter', 'admin'];

    if (!in_array($newRole, $allowedRoles, true)) {
        header("Location: manageUsers.php?status=invalid_role");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE uID = ?");
    $stmt->bind_param("si", $newRole, $userId);

    if ($stmt->execute()) {
        header("Location: manageUsers.php?status=role_updated");
    } else {
        header("Location: manageUsers.php?status=error");
    }

    $stmt->close();
    exit();
}
?>