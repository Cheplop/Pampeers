<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? 0;
$keyword = trim($_GET['keyword'] ?? '');

/* ================= BASE QUERY ================= */

$sql = "
    SELECT 
        s.sitterID,
        s.hourlyRate,

        u.firstName,
        u.lastName,
        u.username,
        u.profilePic,
        u.cityMunicipality,

        (
            SELECT COUNT(*)
            FROM favourites f
            WHERE f.sitter_id = s.sitterID
            AND f.guardian_id = ?
        ) AS isFav

    FROM sitters s

    INNER JOIN users u
        ON s.userID = u.id

    WHERE
        s.verificationStatus = 'verified'
        AND s.isAvailable = 1
        AND u.isActive = 1
        AND u.id != ?
";

$params = [$userId, $userId];
$types = "ii";

/* ================= SEARCH FILTER ================= */

if (!empty($keyword)) {

    $sql .= "
        AND (
            CONCAT(u.firstName, ' ', u.lastName) LIKE ?
            OR u.firstName LIKE ?
            OR u.lastName LIKE ?
            OR u.username LIKE ?
            OR u.cityMunicipality LIKE ?
            OR u.province LIKE ?
        )
    ";

    $search = "%" . $keyword . "%";

    for ($i = 0; $i < 6; $i++) {
        $params[] = $search;
    }

    $types .= "ssssss";
}

/* ================= ORDER ================= */

$sql .= "
    ORDER BY
        s.ratingAverage DESC,
        s.createdAt DESC
";

/* ================= PREPARE ================= */

$stmt = $conn->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "status" => "error",
        "message" => $conn->error
    ]);

    exit();
}

/* ================= BIND ================= */

$stmt->bind_param($types, ...$params);

/* ================= EXECUTE ================= */

if (!$stmt->execute()) {

    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);

    exit();
}

/* ================= FETCH ================= */

$result = $stmt->get_result();

$sitters = [];

while ($row = $result->fetch_assoc()) {

    $sitters[] = [
        "sitterID" => $row['sitterID'],
        "hourlyRate" => $row['hourlyRate'],
        "firstName" => $row['firstName'],
        "lastName" => $row['lastName'],
        "username" => $row['username'],
        "profilePic" => $row['profilePic'],
        "cityMunicipality" => $row['cityMunicipality'],
        "isFav" => $row['isFav']
    ];
}

$stmt->close();

/* ================= RETURN ================= */

echo json_encode($sitters);

exit();
?>