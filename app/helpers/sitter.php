<?php

/*
|--------------------------------------------------------------------------
| SITTER HELPERS primary purpose is Authorization and Identity
|--------------------------------------------------------------------------
| GET SITTER DATA
|--------------------------------------------------------------------------
*/
function getSitter(mysqli $conn, int $userId) {
    // We join users and sitters to get the full profile
    $stmt = $conn->prepare("
        SELECT s.*, u.*
        FROM sitters s
        JOIN users u ON s.userID = u.id
        WHERE u.id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

/*
|--------------------------------------------------------------------------
| CHECK IF USER IS A SITTER (ANY STATUS)
|--------------------------------------------------------------------------
*/
function isSitter($conn, $userId) {
    $stmt = $conn->prepare("SELECT 1 FROM sitters WHERE userID = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/*
|--------------------------------------------------------------------------
| CHECK IF VERIFIED
|--------------------------------------------------------------------------
*/
function isVerifiedSitter(mysqli $conn, int $userId): bool
{
    $stmt = $conn->prepare("SELECT verificationStatus FROM sitters WHERE userID = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return ($row['verificationStatus'] ?? '') === 'verified';
}

/*
|--------------------------------------------------------------------------
| CHECK IF PENDING
|--------------------------------------------------------------------------
*/
function isPendingSitter(mysqli $conn, int $userId): bool
{
    $stmt = $conn->prepare("SELECT verificationStatus FROM sitters WHERE userID = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return ($row['verificationStatus'] ?? '') === 'pending';
}

/*
|--------------------------------------------------------------------------
| GET COMBINED ROLES (For Display)
|--------------------------------------------------------------------------
*/
function getUserRoles(mysqli $conn, int $userId): string
{
    // 1. Get base role from users table
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $baseRole = ucfirst($user['role'] ?? 'guardian');

    // 2. Check if they have a sitter record
    $stmtSitter = $conn->prepare("SELECT 1 FROM sitters WHERE userID = ? LIMIT 1");
    $stmtSitter->bind_param("i", $userId);
    $stmtSitter->execute();
    
    if ($stmtSitter->get_result()->num_rows > 0) {
        return ($baseRole === 'Sitter') ? 'Sitter' : $baseRole . ' / Sitter';
    }

    return $baseRole;
}