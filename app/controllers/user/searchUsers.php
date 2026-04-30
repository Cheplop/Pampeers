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
                id,
                uuid,
                firstName,
                middleName,
                lastName,
                username,
                barangay,
                cityMunicipality,
                province
            FROM users
            WHERE 1 = 1";

    $params = [];
    $types = "";

    if ($name !== '') {
        $sql .= " AND (
                    firstName LIKE ?
                    OR middleName LIKE ?
                    OR lastName LIKE ?
                    OR username LIKE ?
                )";

        $search = "%$name%";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "ssss";
    }

    if ($location !== '') {
        $sql .= " AND (
                    barangay LIKE ?
                    OR cityMunicipality LIKE ?
                    OR province LIKE ?
                )";

        $search = "%$location%";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }

    $sql .= " ORDER BY dateCreated DESC";

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
        'message' => count($data) > 0 ? 'Users found' : 'No users found'
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}   