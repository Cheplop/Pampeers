<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('guardian');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $userId = $_SESSION['user_id'];

    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $sex = !empty($_POST['sex']) ? trim($_POST['sex']) : null;

    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');

    if ($firstName === '' || $lastName === '' || $email === '') {
        header("Location: guardianDashboard.php?status=missing_fields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: guardianDashboard.php?status=invalid_email");
        exit();
    }

    $allowedSex = ['male', 'female', 'other', ''];
    if (!in_array($sex ?? '', $allowedSex, true)) {
        header("Location: guardianDashboard.php?status=invalid_sex");
        exit();
    }

    $checkStmt = $conn->prepare("SELECT uID FROM users WHERE email = ? AND uID != ?");
    $checkStmt->bind_param("si", $email, $userId);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: guardianDashboard.php?status=email_exists");
        exit();
    }
    $checkStmt->close();

    $profileStmt = $conn->prepare("SELECT profilePic FROM users WHERE uID = ? LIMIT 1");
    $profileStmt->bind_param("i", $userId);
    $profileStmt->execute();
    $profileResult = $profileStmt->get_result();
    $currentUser = $profileResult->fetch_assoc();
    $profileStmt->close();

    $profilePic = $currentUser['profilePic'] ?? 'default.jpg';

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
            header("Location: guardianDashboard.php?status=invalid_image");
            exit();
        }

        if ($fileSize > 5 * 1024 * 1024) {
            header("Location: guardianDashboard.php?status=image_too_large");
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
            SET firstName = ?, lastName = ?, email = ?, birthdate = ?, sex = ?, profilePic = ?
            WHERE uID = ?
        ");
        $userStmt->bind_param(
            "ssssssi",
            $firstName,
            $lastName,
            $email,
            $birthdate,
            $sex,
            $profilePic,
            $userId
        );
        $userStmt->execute();
        $userStmt->close();

        $guardianStmt = $conn->prepare("
            UPDATE guardians
            SET street = ?, city = ?, country = ?, contactNumber = ?
            WHERE uID = ?
        ");
        $guardianStmt->bind_param(
            "ssssi",
            $street,
            $city,
            $country,
            $contactNumber,
            $userId
        );
        $guardianStmt->execute();
        $guardianStmt->close();

        $conn->commit();
        header("Location: guardianDashboard.php?status=profile_updated");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: guardianDashboard.php?status=error");
        exit();
    }
}

header("Location: guardianDashboard.php");
exit();
?>