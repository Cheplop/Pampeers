<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Include the config file to connect to the database
require_once __DIR__ . '/../../config/config.php';

$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
// Determine if we are searching for 'sitter' or 'user' (defaults to sitter for Pampeers)
$type = isset($_GET['type']) ? trim($_GET['type']) : 'sitter';

if ($name === '' && $location === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Enter a name or location.'
    ]);
    exit();
}

try {
    $params = [];
    $types = "";

    /**
     * Build query based on search type
     * Sitter: Joins users and sitters tables to get rates, bios, and ratings[cite: 2].
     * User: Queries only the users table for general guardian/user info[cite: 2].
     */
    if ($type === 'sitter') {
        $sql = "SELECT 
                    s.sitterID, s.userID, u.uuid AS userUUID,
                    u.firstName, u.middleName, u.lastName, u.username, u.profilePic,
                    u.barangay, u.cityMunicipality, u.province,
                    s.bio, s.hourlyRate, s.experience, s.isAvailable,
                    s.ratingAverage, s.verificationStatus
                FROM sitters s
                INNER JOIN users u ON s.userID = u.id
                WHERE u.isActive = 1"; // Ensure only active users appear
    } else {
        $sql = "SELECT 
                    id, uuid, firstName, middleName, lastName, 
                    username, profilePic, barangay, cityMunicipality, province
                FROM users 
                WHERE isActive = 1";
    }

    // Dynamic Filter: Name
    if ($name !== '') {
        $sql .= ($type === 'sitter') 
            ? " AND (u.firstName LIKE ? OR u.middleName LIKE ? OR u.lastName LIKE ? OR u.username LIKE ?)"
            : " AND (firstName LIKE ? OR middleName LIKE ? OR lastName LIKE ? OR username LIKE ?)";
        
        $searchName = "%$name%";
        for ($i = 0; $i < 4; $i++) {
            $params[] = $searchName;
            $types .= "s";
        }
    }

    // Dynamic Filter: Location
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

    // Final ordering based on type
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
        // Standardize output fields
        $row['fullName'] = trim(
            $row['firstName'] . ' ' .
            (!empty($row['middleName']) ? $row['middleName'] . ' ' : '') .
            $row['lastName']
        );

        $row['location'] = trim(
            $row['barangay'] . ', ' .
            $row['cityMunicipality'] . ', ' .
            $row['province']
        );

        $row['profilePic'] = $row['profilePic'] ?: 'default.jpg';
        $data[] = $row;
    }

    echo json_encode([
        'success' => count($data) > 0,
        'type' => $type,
        'data' => $data,
        'message' => count($data) > 0 ? ucfirst($type) . 's found' : 'No results found'
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}