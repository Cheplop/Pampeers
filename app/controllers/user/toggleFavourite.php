<?php
// 1. We must start the session before accessing $_SESSION!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

header('Content-Type: application/json');

// 2. Safely grab the user ID
$userId = $_SESSION['user_id'] ?? null;

// 3. Prevent fatal SQL errors by stopping if the user isn't found in the session
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in or session expired.']);
    exit();
}

$sitterId = $_POST['sitterId'] ?? $_POST['sitterID'] ?? null;

if (!$sitterId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Sitter ID']);
    exit();
}

// Check if already favorited
$check = $conn->prepare("SELECT favouriteID FROM favourites WHERE guardian_id = ? AND sitter_id = ?");
$check->bind_param("ii", $userId, $sitterId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Remove if exists
    $delete = $conn->prepare("DELETE FROM favourites WHERE guardian_id = ? AND sitter_id = ?");
    $delete->bind_param("ii", $userId, $sitterId);
    $delete->execute();
    echo json_encode(['status' => 'removed']);
} else {
    // Add if not exists
    $insert = $conn->prepare("INSERT INTO favourites (guardian_id, sitter_id) VALUES (?, ?)");
    $insert->bind_param("ii", $userId, $sitterId);
    $insert->execute();
    echo json_encode(['status' => 'added']);
}