<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

// Use the correct Guardian Dashboard path for all redirects
$guardianDash = '/Pampeers/public/guardian/guardianDashboard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $guardianDash");
    exit();
}

function generateUUIDv4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$userId      = $_SESSION['user_id'];
$sitterId    = (int) ($_POST['sitterID'] ?? 0);
$bookingDate = trim($_POST['bookingDate'] ?? '');
$startTime   = trim($_POST['startTime'] ?? '');
$endTime     = trim($_POST['endTime'] ?? '');
$notes       = trim($_POST['notes'] ?? '');

if ($sitterId <= 0 || $bookingDate === '' || $startTime === '' || $endTime === '') {
    header("Location: $guardianDash?error=missing_fields");
    exit();
}

$startTimestamp = strtotime($bookingDate . ' ' . $startTime);
$endTimestamp   = strtotime($bookingDate . ' ' . $endTime);

if ($startTimestamp === false || $endTimestamp === false || $endTimestamp <= $startTimestamp) {
    header("Location: $guardianDash?error=invalid_booking_time");
    exit();
}

// Fetch sitter rate
$sitterStmt = $conn->prepare("SELECT hourlyRate FROM sitters WHERE sitterID = ? LIMIT 1");
$sitterStmt->bind_param("i", $sitterId);
$sitterStmt->execute();
$sitterData = $sitterStmt->get_result()->fetch_assoc();

if (!$sitterData) {
    header("Location: $guardianDash?error=sitter_not_found");
    exit();
}

$hoursRequested = ($endTimestamp - $startTimestamp) / 3600;
$totalAmount = $hoursRequested * (float)$sitterData['hourlyRate'];
$sitterStmt->close();

$bookingUUID = generateUUIDv4();
$status = 'pending';

$insertStmt = $conn->prepare("
    INSERT INTO bookings (uuid, userID, sitterID, bookingDate, startTime, endTime, hoursRequested, totalAmount, status, notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insertStmt->bind_param("siisssddss", $bookingUUID, $userId, $sitterId, $bookingDate, $startTime, $endTime, $hoursRequested, $totalAmount, $status, $notes);

if ($insertStmt->execute()) {
    $insertStmt->close();
    header("Location: $guardianDash?booking=success");
    exit();
}

$insertStmt->close();
header("Location: $guardianDash?error=booking_failed");
exit();