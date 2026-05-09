<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? 0;

// 1. Grab the search inputs from the URL
$location = trim($_GET['location'] ?? '');
$date     = trim($_GET['date'] ?? '');
$who      = trim($_GET['keyword'] ?? '');

// 2. Start the base SQL query (Only get verified, active, and available sitters)
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
    $params[] = "%" . $location . "%"; // % allows partial matches
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
    // Hide sitters who are already booked on this exact date
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

// 7. Send the results back to the Javascript on the dashboard
echo json_encode($sitters);
exit();
?>