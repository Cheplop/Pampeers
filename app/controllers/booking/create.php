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
$sitterStmt->close();

/* ================= NEW: PREVENT MULTIPLE ACTIVE BOOKINGS ================= */
// This checks if the guardian already has a booking with this sitter that isn't finished
$checkStmt = $conn->prepare("
    SELECT COUNT(*) as activeCount 
    FROM bookings 
    WHERE userID = ? AND sitterID = ? 
    AND status NOT IN ('completed', 'cancelled', 'declined')
");
$checkStmt->bind_param("ii", $userId, $sitterId);
$checkStmt->execute();
$existingBooking = $checkStmt->get_result()->fetch_assoc();

if ($existingBooking['activeCount'] > 0) {
    // Redirect back with a specific error message
    header("Location: $guardianDash?error=already_booked");
    $checkStmt->close();
    exit();
}
$checkStmt->close();
/* ========================================================================= */

// 5. Calculate total duration in hours and total amount
$hoursRequested = ($endTimestamp - $startTimestamp) / 3600;

// RANGE FIX: Ensure hoursRequested does not exceed DECIMAL(5,2) limit (999.99)
if ($hoursRequested > 999.99) {
    header("Location: $guardianDash?error=duration_too_long");
    exit();
}

$hoursRequested = round($hoursRequested, 2);
$totalAmount = round($hoursRequested * (float)$sitterData['hourlyRate'], 2);

$bookingUUID = generateUUIDv4();
$status = 'pending';

// 6. Insert into database
$insertStmt = $conn->prepare("
    INSERT INTO bookings (uuid, userID, sitterID, startDateTime, endDateTime, hoursRequested, totalAmount, status, notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

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