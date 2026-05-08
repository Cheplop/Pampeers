<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

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
$startDate   = trim($_POST['bookingDate'] ?? ''); 
$endDate     = trim($_POST['endDate'] ?? '');
$startTime   = trim($_POST['startTime'] ?? '');
$endTime     = trim($_POST['endTime'] ?? '');
$notes       = trim($_POST['notes'] ?? '');

// 1. Validation
if ($sitterId <= 0 || empty($startDate) || empty($endDate) || empty($startTime) || empty($endTime)) {
    header("Location: $guardianDash?error=missing_fields");
    exit();
}

// 2. Convert string dates and times to UNIX timestamps for math
$startTimestamp = strtotime($startDate . ' ' . $startTime);
$endTimestamp   = strtotime($endDate . ' ' . $endTime);

if ($startTimestamp === false || $endTimestamp === false || $endTimestamp <= $startTimestamp) {
    header("Location: $guardianDash?error=invalid_booking_range");
    exit();
}

// 3. Format into strict MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)
$startDateTime = date('Y-m-d H:i:s', $startTimestamp);
$endDateTime   = date('Y-m-d H:i:s', $endTimestamp);

// 4. Fetch sitter rate
$sitterStmt = $conn->prepare("SELECT hourlyRate FROM sitters WHERE sitterID = ? LIMIT 1");
$sitterStmt->bind_param("i", $sitterId);
$sitterStmt->execute();
$sitterData = $sitterStmt->get_result()->fetch_assoc();

if (!$sitterData) {
    header("Location: $guardianDash?error=sitter_not_found");
    exit();
}

// 5. Calculate total duration in hours and total amount
$hoursRequested = ($endTimestamp - $startTimestamp) / 3600;
$totalAmount = $hoursRequested * (float)$sitterData['hourlyRate'];
$sitterStmt->close();

$bookingUUID = generateUUIDv4();
$status = 'pending';

// 6. Insert into database using the new startDateTime and endDateTime columns
$insertStmt = $conn->prepare("
    INSERT INTO bookings (uuid, userID, sitterID, startDateTime, endDateTime, hoursRequested, totalAmount, status, notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// siissddss mapping:
// s = string (uuid)
// i = integer (userID)
// i = integer (sitterID)
// s = string (startDateTime)
// s = string (endDateTime)
// d = double/decimal (hoursRequested)
// d = double/decimal (totalAmount)
// s = string (status)
// s = string (notes)
$insertStmt->bind_param("siissddss", 
    $bookingUUID, 
    $userId, 
    $sitterId, 
    $startDateTime, 
    $endDateTime, 
    $hoursRequested, 
    $totalAmount, 
    $status, 
    $notes
);

if ($insertStmt->execute()) {
    header("Location: $guardianDash?booking=success");
} else {
    header("Location: $guardianDash?error=booking_failed");
}

$insertStmt->close();
exit();
?>