<?php

/*
|--------------------------------------------------------------------------
| CHECK IF USER IS A SITTER
|--------------------------------------------------------------------------
*/
function isSitter(mysqli $conn, int $userId): bool
{
    // check role first
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (($user['role'] ?? '') === 'admin') {
        return false; // admin is NEVER sitter
    }

    // then check sitters table
    $stmt = $conn->prepare("
        SELECT sitterID 
        FROM sitters 
        WHERE userID = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}

/*
|--------------------------------------------------------------------------
| GET SITTER DATA
|--------------------------------------------------------------------------
*/
function getSitter(mysqli $conn, int $userId): ?array
{
    $stmt = $conn->prepare("
        SELECT *
        FROM sitters
        WHERE userID = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc() ?: null;
}

/*
|--------------------------------------------------------------------------
| CHECK IF VERIFIED SITTER
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
| CHECK IF PENDING SITTER
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

/*
|--------------------------------------------------------------------------
| CAN ACCESS SITTER FEATURES
| RULE: ONLY VERIFIED CAN ACCESS SITTER DASHBOARD FEATURES
|--------------------------------------------------------------------------
*/
function canAccessSitterFeatures(mysqli $conn, int $userId): bool
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
| GET DASHBOARD ROUTE (FIXED LOGIC)
|--------------------------------------------------------------------------
*/
function getDashboardRoute(mysqli $conn, int $userId, string $role): string
{
    if ($role === 'admin') {
        return '/Pampeers/public/admin/adminDashboard.php';
    }

    if ($role === 'sitter') {
        return '/Pampeers/public/sitter/sitterDashboard.php';
    }

    return '/Pampeers/public/guardian/guardianDashboard.php';
}