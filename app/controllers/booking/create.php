<?php
// Include config and auth middleware based on your folder structure
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in[cite: 5]
requireAuth();

// Redirect if not a POST request[cite: 5, 8]
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/guardian/guardianDashboard.php');
    exit();
}

/**
 * Generates a unique ID for the booking[cite: 5]
 */
function generateUUIDv4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// 1. Gather User and Form Data[cite: 5]
$userId      = $_SESSION['user_id'];
$sitterId    = (int) ($_POST['sitterID'] ?? 0);
$bookingDate = trim($_POST['bookingDate'] ?? '');
$startTime   = trim($_POST['startTime'] ?? '');
$endTime     = trim($_POST['endTime'] ?? '');
$notes       = trim($_POST['notes'] ?? '');

// 2. Validation Check[cite: 5, 8]
// This triggers your 'missing_fields' error if any required value is empty[cite: 8]
if ($sitterId <= 0 || $bookingDate === '' || $startTime === '' || $endTime === '') {
    header('Location: /Pampeers/public/guardian/guardianDashboard.php?error=missing_fields');
    exit();
}

// 3. Time Validation and Total Calculation[cite: 5]
$startTimestamp = strtotime($bookingDate . ' ' . $startTime);
$endTimestamp   = strtotime($bookingDate . ' ' . $endTime);

if ($startTimestamp === false || $endTimestamp === false || $endTimestamp <= $startTimestamp) {
    header('Location: /Pampeers/public/guardian/guardianDashboard.php?error=invalid_booking_time');
    exit();
}

// Calculate hours and fetch sitter rate[cite: 5]
$seconds = $endTimestamp - $startTimestamp;
$hoursRequested = $seconds / 3600;

$sitterStmt = $conn->prepare("SELECT hourlyRate FROM sitters WHERE sitterID = ? LIMIT 1");
$sitterStmt->bind_param("i", $sitterId);
$sitterStmt->execute();
$sitterData = $sitterStmt->get_result()->fetch_assoc();

if (!$sitterData) {
    header('Location: /Pampeers/public/guardian/guardianDashboard.php?error=sitter_not_found');
    exit();
}

$totalAmount = $hoursRequested * (float)$sitterData['hourlyRate'];
$sitterStmt->close();

// 4. Prepare Insert Query[cite: 5, 9]
$bookingUUID = generateUUIDv4();
$status = 'pending';

$insertStmt = $conn->prepare("
    INSERT INTO bookings (
        uuid, 
        userID, 
        sitterID, 
        bookingDate, 
        startTime, 
        endTime, 
        hoursRequested, 
        totalAmount, 
        status, 
        notes
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Bind all 10 values to match your pampeers2.sql structure
$insertStmt->bind_param(
    "siisssddss",
    $bookingUUID,
    $userId,
    $sitterId,
    $bookingDate,
    $startTime,
    $endTime,
    $hoursRequested,
    $totalAmount,
    $status,
    $notes
);

// 5. Execute and Redirect[cite: 8]
if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: /Pampeers/public/guardian/guardianDashboard.php?booking=success');
    exit();
}

$insertStmt->close();
header('Location: /Pampeers/public/guardian/guardianDashboard.php?error=booking_failed');
exit();
?>