<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php'; // Adjust path if necessary

// 1. Match the exact parameters sent by the JavaScript fetch()
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : 'sitter';

// We need the logged-in user to check if they favourited these sitters
$userId = $_SESSION['user_id'] ?? 0;

try {
    $params = [];
    $types = "";

    if ($type === 'sitter') {
        // 2. We use a LEFT JOIN to check if this user has favourited the sitter
        $sql = "SELECT 
                    s.sitterID, s.userID, u.uuid AS userUUID,
                    u.firstName, u.middleName, u.lastName, u.username, u.profilePic,
                    u.barangay, u.cityMunicipality, u.province,
                    s.bio, s.hourlyRate, s.experience, s.isAvailable,
                    s.ratingAverage, s.verificationStatus,
                    IF(f.id IS NOT NULL, 1, 0) AS isFavourite
                FROM sitters s
                INNER JOIN users u ON s.userID = u.id
                LEFT JOIN favourites f ON s.sitterID = f.sitter_id AND f.guardian_id = ?
                WHERE u.isActive = 1 
                  AND s.isAvailable = 1 
                  AND s.verificationStatus = 'verified'";
        
        $params[] = $userId;
        $types .= "i";
    } else {
        $sql = "SELECT id, uuid, firstName, middleName, lastName, username, profilePic, barangay, cityMunicipality, province FROM users WHERE isActive = 1";
    }

    // Dynamic Filter: Keyword (Who)
    if ($keyword !== '') {
        $sql .= ($type === 'sitter') 
            ? " AND (u.firstName LIKE ? OR u.lastName LIKE ? OR u.username LIKE ?)"
            : " AND (firstName LIKE ? OR lastName LIKE ? OR username LIKE ?)";
        
        $searchKeyword = "%$keyword%";
        for ($i = 0; $i < 3; $i++) {
            $params[] = $searchKeyword;
            $types .= "s";
        }
    }

    // Dynamic Filter: Location (Where)
    if ($location !== '') {
        $sql .= ($type === 'sitter')
            ? " AND (u.barangay LIKE ? OR u.cityMunicipality LIKE ? OR u.province LIKE ?)"
            : " AND (barangay LIKE ? OR cityMunicipality LIKE ? OR province LIKE ?)";
        
        $searchLocation = "%$location%";
        for ($i = 0; $i < 3; $i++) {
            $params[] = $searchLocation;
            $types .= "s";
        }
    }

    $sql .= ($type === 'sitter') 
        ? " ORDER BY s.ratingAverage DESC, s.createdAt DESC"
        : " ORDER BY dateCreated DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('SQL prepare failed: ' . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['fullName'] = trim($row['firstName'] . ' ' . $row['lastName']);
        $row['location'] = trim($row['cityMunicipality'] . ', ' . $row['province']);
        $row['profilePic'] = $row['profilePic'] ?: 'default.jpg';
        
        // Ensure the boolean flag is properly passed to JS
        if (isset($row['isFavourite'])) {
            $row['isFavourite'] = (bool) $row['isFavourite']; 
        }

        $data[] = $row;
    }

    echo json_encode([
        'success' => true,
        'type' => $type,
        'data' => $data,
        'message' => count($data) > 0 ? count($data) . ' sitter(s) found' : 'No available sitters found.'
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}