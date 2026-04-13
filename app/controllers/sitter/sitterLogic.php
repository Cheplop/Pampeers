<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('sitter');

$userId = $_SESSION['user_id'];

// TOGGLE AVAILABILITY
if (isset($_POST['toggle_availability'])) {
    $status = isset($_POST['isAvailable']) ? (int) $_POST['isAvailable'] : 0;
    $status = $status === 1 ? 1 : 0;

    $stmt = $conn->prepare("UPDATE sitters SET isAvailable = ? WHERE uID = ?");
    $stmt->bind_param("ii", $status, $userId);

    if ($stmt->execute()) {
        header("Location: sitterDashboard.php?status=availability_updated");
    } else {
        header("Location: sitterDashboard.php?status=error");
    }

    $stmt->close();
    exit();
}

// UPDATE SITTER PROFILE
if (isset($_POST['update_profile'])) {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $sex = trim($_POST['sex'] ?? '');

    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $hourlyRate = isset($_POST['hourlyRate']) && $_POST['hourlyRate'] !== '' ? (float) $_POST['hourlyRate'] : 0.00;
    $bio = trim($_POST['bio'] ?? '');
    $experience = isset($_POST['experience']) && $_POST['experience'] !== '' ? (int) $_POST['experience'] : 0;

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

        $sitterStmt = $conn->prepare("
            UPDATE sitters
            SET hourlyRate = ?, bio = ?, experience = ?
            WHERE uID = ?
        ");
        $sitterStmt->bind_param(
            "dsii",
            $hourlyRate,
            $bio,
            $experience,
            $userId
        );
        $sitterStmt->execute();
        $sitterStmt->close();

        $conn->commit();
        header("Location: updateProfile.php?status=updated");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: updateProfile.php?status=error");
        exit();
    }
}

// PLACEHOLDER FOR FUTURE BOOKING ACTIONS
if (isset($_POST['accept_booking'])) {
    header("Location: sitterDashboard.php?status=booking_placeholder");
    exit();
}

header("Location: sitterDashboard.php");
exit();
?>