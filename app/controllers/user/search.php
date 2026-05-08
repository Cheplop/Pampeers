<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : ''; // This is now the Age Group
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : ''; // The When
$type = isset($_GET['type']) ? trim($_GET['type']) : 'sitter';

$userId = $_SESSION['user_id'] ?? 0;

try {
    $params = [$userId];
    $types = "i";

    $sql = "SELECT 
                s.sitterID, s.userID, u.firstName, u.lastName, u.profilePic,
                u.barangay, u.cityMunicipality, u.province,
                s.hourlyRate, s.ratingAverage, s.allowedAges,
                IF(f.id IS NOT NULL, 1, 0) AS isFavourite
            FROM sitters s
            INNER JOIN users u ON s.userID = u.id
            LEFT JOIN favourites f ON s.sitterID = f.sitter_id AND f.guardian_id = ?
            WHERE u.isActive = 1 AND s.isAvailable = 1 AND s.verificationStatus = 'verified'";

    // 1. WHERE: Filter by Location
    if ($location !== '') {
        $sql .= " AND (u.barangay LIKE ? OR u.cityMunicipality LIKE ? OR u.province LIKE ?)";
        $searchLocation = "%$location%";
        for ($i = 0; $i < 3; $i++) { $params[] = $searchLocation; $types .= "s"; }
    }

    // 2. WHO: Filter by Age Group (Baby, Toddler, etc.)
    if ($keyword !== '') {
        $sql .= " AND s.allowedAges LIKE ?";
        $params[] = "%$keyword%";
        $types .= "s";
    }

    // 3. WHEN: Exclude sitters already booked on this exact date
    if ($date !== '') {
        $sql .= " AND s.sitterID NOT IN (
                    SELECT sitterID FROM bookings 
                    WHERE bookingDate = ? AND status IN ('accepted', 'pending')
                  )";
        $params[] = $date;
        $types .= "s";
    }

    $sql .= " ORDER BY s.ratingAverage DESC, s.createdAt DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['profilePic'] = $row['profilePic'] ?: 'default.jpg';
        $row['isFavourite'] = (bool) $row['isFavourite'];
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}