<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';

$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

if ($name === '' && $location === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Enter a name or location.'
    ]);
    exit();
}

try {
    $sql = "SELECT 
                sitters.sitterID,
                sitters.userID,
                users.uuid AS userUUID,
                users.firstName,
                users.middleName,
                users.lastName,
                users.username,
                users.barangay,
                users.cityMunicipality,
                users.province,
                sitters.bio,
                sitters.hourlyRate,
                sitters.experience,
                sitters.isAvailable,
                sitters.ratingAverage,
                sitters.verificationStatus
            FROM sitters
            INNER JOIN users ON sitters.userID = users.id
            WHERE 1 = 1";

    $params = [];
    $types = "";

    if ($name !== '') {
        $sql .= " AND (
                    users.firstName LIKE ?
                    OR users.middleName LIKE ?
                    OR users.lastName LIKE ?
                    OR users.username LIKE ?
                )";

        $searchName = "%$name%";
        $params[] = $searchName;
        $params[] = $searchName;
        $params[] = $searchName;
        $params[] = $searchName;
        $types .= "ssss";
    }

    if ($location !== '') {
        $sql .= " AND (
                    users.barangay LIKE ?
                    OR users.cityMunicipality LIKE ?
                    OR users.province LIKE ?
                )";

        $searchLocation = "%$location%";
        $params[] = $searchLocation;
        $params[] = $searchLocation;
        $params[] = $searchLocation;
        $types .= "sss";
    }

    $sql .= " ORDER BY sitters.ratingAverage DESC, sitters.createdAt DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'SQL prepare failed: ' . $conn->error
        ]);
        exit();
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['fullName'] = trim(
            $row['firstName'] . ' ' .
            ($row['middleName'] ? $row['middleName'] . ' ' : '') .
            $row['lastName']
        );

        $row['location'] = trim(
            $row['barangay'] . ', ' .
            $row['cityMunicipality'] . ', ' .
            $row['province']
        );

        $data[] = $row;
    }

    echo json_encode([
        'success' => count($data) > 0,
        'data' => $data,
        'message' => count($data) > 0 ? 'Sitters found' : 'No sitters found'
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}