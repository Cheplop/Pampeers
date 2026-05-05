<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['sitterID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$uID = $_SESSION['user_id'];
$sitterID = $_POST['sitterID'];

// Check if already in favourites
$check = $conn->prepare("SELECT favID FROM favourites WHERE uID = ? AND sitterID = ?");
$check->bind_param("ii", $uID, $sitterID);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Remove if exists
    $stmt = $conn->prepare("DELETE FROM favourites WHERE uID = ? AND sitterID = ?");
    $action = 'removed';
} else {
    // Add if not exists
    $stmt = $conn->prepare("INSERT INTO favourites (uID, sitterID) VALUES (?, ?)");
    $action = 'added';
}

$stmt->bind_param("ii", $uID, $sitterID);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'action' => $action]);
} else {
    echo json_encode(['status' => 'error']);
}