<?php
// Include config for database and auth for login check
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Make sure user is logged in
requireAuth();

// Check if request is POST, else redirect
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pampeers/public/user/dashboard.php');
    exit();
}

// Function to create unique ID for booking
function generateUUIDv4(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Get user ID from session
$userId      = $_SESSION['user_id'];
// Get sitter ID, date, times, notes from form
$sitterId    = (int) ($_POST['sitterID'] ?? 0);
$bookingDate = trim($_POST['bookingDate'] ?? '');
$startTime   = trim($_POST['startTime'] ?? '');
$endTime     = trim($_POST['endTime'] ?? '');
$notes       = trim($_POST['notes'] ?? '');

// Check if required fields are filled
if ($sitterId <= 0 || $bookingDate === '' || $startTime === '' || $endTime === '') {
    header('Location: /pampeers/public/user/dashboard.php?error=missing_booking_fields');
    exit();
}

// Convert date and time to timestamps for validation
$startTimestamp = strtotime($bookingDate . ' ' . $startTime);
$endTimestamp   = strtotime($bookingDate . ' ' . $endTime);

// Check if times are valid and end is after start
if ($startTimestamp === false || $endTimestamp === false || $endTimestamp <= $startTimestamp) {
    header('Location: /pampeers/public/user/dashboard.php?error=invalid_booking_time');
    exit();
}

// Query to get sitter details
$sitterStmt = $conn->prepare("
    SELECT s.sitterID, s.hourlyRate, s.isAvailable
    FROM sitters s
    INNER JOIN users u ON s.userID = u.id
    WHERE s.sitterID = ?
      AND u.isActive = 1
    LIMIT 1
");
// Bind sitter ID
$sitterStmt->bind_param("i", $sitterId);
$sitterStmt->execute();
$sitterResult = $sitterStmt->get_result();

// If sitter not found, redirect with error
if ($sitterResult->num_rows === 0) {
    $sitterStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?error=sitter_not_found');
    exit();
}

// Get sitter data
$sitter = $sitterResult->fetch_assoc();
$sitterStmt->close();

// Check if sitter is available
if ((int)$sitter['isAvailable'] !== 1) {
    header('Location: /pampeers/public/user/dashboard.php?error=sitter_unavailable');
    exit();
}

// Calculate hours and total cost
$seconds = $endTimestamp - $startTimestamp;
$hoursRequested = $seconds / 3600;
$totalAmount = $hoursRequested * (float)$sitter['hourlyRate'];

// Create unique ID for booking
$bookingUUID = generateUUIDv4();
$status = 'pending'; // Default status

// Prepare insert query for booking
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

// Bind all values
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

// If insert succeeds, redirect with success
if ($insertStmt->execute()) {
    $insertStmt->close();
    header('Location: /pampeers/public/user/dashboard.php?booking=success');
    exit();
}

// If failed, redirect with error
$insertStmt->close();
header('Location: /pampeers/public/user/dashboard.php?error=booking_failed');
exit();
?>