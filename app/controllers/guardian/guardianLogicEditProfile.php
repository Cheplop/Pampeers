<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('guardian');

$userId = $_SESSION['user_id'];

// FETCH CURRENT GUARDIAN DATA
$stmt = $conn->prepare("
    SELECT
        u.firstName,
        u.lastName,
        u.email,
        u.birthdate,
        u.sex,
        u.profilePic,
        u.street,
        u.city,
        u.country,
        u.contactNumber
    FROM users u
    INNER JOIN guardians g ON u.uID = g.uID
    WHERE u.uID = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$guardian = $result->fetch_assoc();
$stmt->close();

if (!$guardian) {
    die("Guardian data not found.");
}

// UPDATE GUARDIAN PROFILE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $sex = trim($_POST['sex'] ?? '');

    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');

    if (
        $firstName === '' ||
        $lastName === '' ||
        $email === '' ||
        $birthdate === '' ||
        $sex === '' ||
        $street === '' ||
        $city === '' ||
        $country === '' ||
        $contactNumber === ''
    ) {
        header("Location: updateProfile.php?status=missing_fields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: updateProfile.php?status=invalid_email");
        exit();
    }

    $allowedSex = ['male', 'female', 'other'];
    if (!in_array(strtolower($sex), $allowedSex, true)) {
        header("Location: updateProfile.php?status=invalid_sex");
        exit();
    }

    $checkStmt = $conn->prepare("SELECT uID FROM users WHERE email = ? AND uID != ?");
    $checkStmt->bind_param("si", $email, $userId);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: updateProfile.php?status=email_exists");
        exit();
    }
    $checkStmt->close();

    $profilePic = $guardian['profilePic'] ?? 'default.jpg';

    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === 0) {
        $uploadDir = __DIR__ . '/../../../uploads/profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = $_FILES['profilePic']['name'];
        $tmpName = $_FILES['profilePic']['tmp_name'];
        $fileSize = $_FILES['profilePic']['size'];

        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExtension, $allowedExtensions, true)) {
            header("Location: updateProfile.php?status=invalid_image");
            exit();
        }

        if ($fileSize > 5 * 1024 * 1024) {
            header("Location: updateProfile.php?status=image_too_large");
            exit();
        }

        $newFileName = time() . "_" . uniqid() . "." . $fileExtension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $profilePic = $newFileName;
        }
    }

    $conn->begin_transaction();

    try {
        $userStmt = $conn->prepare("
            UPDATE users
            SET firstName = ?, lastName = ?, email = ?, birthdate = ?, sex = ?, profilePic = ?,
                street = ?, city = ?, country = ?, contactNumber = ?
            WHERE uID = ?
        ");
        $userStmt->bind_param(
            "ssssssssssi",
            $firstName,
            $lastName,
            $email,
            $birthdate,
            $sex,
            $profilePic,
            $street,
            $city,
            $country,
            $contactNumber,
            $userId
        );
        $userStmt->execute();
        $userStmt->close();

        $conn->commit();
        header("Location: updateProfile.php?status=updated");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: updateProfile.php?status=error");
        exit();
    }
}
?>