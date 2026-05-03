<?php

/*
|--------------------------------------------------------------------------
| GET SITTER DATA
|--------------------------------------------------------------------------
*/
function getSitter(mysqli $conn, int $userId) {
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
| CHECK IF USER IS A SITTER
|--------------------------------------------------------------------------
*/
function isSitter($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT 1 FROM sitters WHERE userID = ? LIMIT 1
    ");
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
    $stmt = $conn->prepare("
        SELECT verificationStatus
        FROM sitters
        WHERE userID = ?
        LIMIT 1
    ");

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
    $stmt = $conn->prepare("
        SELECT verificationStatus
        FROM sitters
        WHERE userID = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();

    return ($row['verificationStatus'] ?? '') === 'pending';
}

function getUserRoles(mysqli $conn, int $userId): string
{
    // get base role
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $role = ucfirst($user['role'] ?? 'guardian');

    // check if also sitter
    $stmt = $conn->prepare("SELECT 1 FROM sitters WHERE userID = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $role .= ', Sitter';
    }

    return $role;
}