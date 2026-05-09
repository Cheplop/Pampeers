<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/config.php';

// DO NOT put requireAuth() here so Guests can use this file!

header('Content-Type: application/json');

// If logged in, get their ID. If Guest, default to 0.
$userId = $_SESSION['user_id'] ?? 0;

// 1. Grab the search inputs from the URL
$location = trim($_GET['location'] ?? '');
$date     = trim($_GET['date'] ?? '');
$who      = trim($_GET['keyword'] ?? '');

// 2. Start the base SQL query
// If userId is 0 (Guest), `u.id != 0` just means it won't hide any sitters by mistake.
$sql = "SELECT s.sitterID, s.hourlyRate, u.firstName, u.lastName, u.profilePic, u.cityMunicipality,
        (SELECT COUNT(*) FROM favourites f WHERE f.sitter_id = s.sitterID AND f.guardian_id = ?) as isFavourite
        FROM sitters s 
        JOIN users u ON s.userID = u.id 
        WHERE s.verificationStatus = 'verified' AND u.isActive = 1 AND s.isAvailable = 1 AND u.id != ?";

$params = [$userId, $userId];
$types = "ii";

// 3. SMART FILTER: Where (Location)
if ($location !== '') {
    $sql .= " AND u.cityMunicipality LIKE ?";
    $params[] = "%" . $location . "%"; 
    $types .= "s";
}

// 4. SMART FILTER: Who (Accepted Ages)
if ($who !== '') {
    $sql .= " AND s.acceptedAges LIKE ?";
    $params[] = "%" . $who . "%";
    $types .= "s";
}

// 5. SMART FILTER: When (Date Check)
if ($date !== '') {
    $sql .= " AND s.sitterID NOT IN (
                SELECT sitterID FROM bookings 
                WHERE DATE(startDateTime) <= ? AND DATE(endDateTime) >= ? 
                AND status IN ('pending', 'accepted')
              )";
    $params[] = $date;
    $params[] = $date;
    $types .= "ss";
}

// 6. Execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$sitters = [];
while ($row = $result->fetch_assoc()) {
    $sitters[] = $row;
}

$stmt->close();

// 7. Send the results back to the Dashboard
echo json_encode($sitters);
exit();
?>