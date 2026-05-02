<?php

/*
|--------------------------------------------------------------------------
| GET SITTER DATA
|--------------------------------------------------------------------------
*/
function getSitter(mysqli $conn, int $userId)
{
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
function isSitter(mysqli $conn, int $userId): bool
{
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