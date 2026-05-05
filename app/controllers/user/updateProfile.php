<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/editProfile.php');
    exit();
}

$userId = $_SESSION['user_id'];

/* ================= INPUTS ================= */
$firstName        = trim($_POST['firstName'] ?? '');
$middleName       = trim($_POST['middleName'] ?? '');
$lastName         = trim($_POST['lastName'] ?? '');
$suffix           = trim($_POST['suffix'] ?? '');
$birthDate        = trim($_POST['birthDate'] ?? '');
$sex              = trim($_POST['sex'] ?? '');

$contactNumber    = trim($_POST['contactNumber'] ?? '');
$usernameInput    = trim($_POST['username'] ?? '');

$streetAddress    = trim($_POST['streetAddress'] ?? '');
$barangay         = trim($_POST['barangay'] ?? '');
$cityMunicipality = trim($_POST['cityMunicipality'] ?? '');
$province         = trim($_POST['province'] ?? '');
$country          = trim($_POST['country'] ?? '');
$zipCode          = trim($_POST['zipCode'] ?? '');

/* SITTER FIELDS (optional) */
$bio        = trim($_POST['bio'] ?? '');
$hourlyRate = trim($_POST['hourlyRate'] ?? '');
$experience = trim($_POST['experience'] ?? '');
$isAvailable = isset($_POST['isAvailable']) ? 1 : 0;

/* ================= VALIDATION ================= */
$required = [
    $firstName, $lastName, $birthDate, $sex,
    $contactNumber, $usernameInput,
    $streetAddress, $barangay, $cityMunicipality,
    $province, $country, $zipCode
];

foreach ($required as $value) {
    if ($value === '') {
        header('Location: /Pampeers/public/editProfile.php?error=missing_fields');
        exit();
    }
}

if (!in_array(strtolower($sex), ['male', 'female', 'other'])) {
    header('Location: /Pampeers/public/editProfile.php?error=invalid_sex');
    exit();
}

/* ================= GET CURRENT USER ================= */
$stmt = $conn->prepare("
    SELECT u.profilePic, s.sitterID, s.verificationStatus
    FROM users u
    LEFT JOIN sitters s ON s.userID = u.id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

$currentPic = $current['profilePic'] ?? 'default.jpg';
$sitterId   = $current['sitterID'] ?? null;
$isVerifiedSitter = ($current['verificationStatus'] ?? '') === 'verified';

/* ================= IMAGE UPLOAD ================= */
if (!empty($_FILES['profilePic']['name'])) {

    $uploadDir = __DIR__ . '/../../../app/uploads/profiles/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $allowed)) {
        header('Location: /Pampeers/public/editProfile.php?error=invalid_image');
        exit();
    }

    if ($_FILES['profilePic']['size'] > 5 * 1024 * 1024) {
        header('Location: /Pampeers/public/editProfile.php?error=image_too_large');
        exit();
    }

    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target  = $uploadDir . $newName;

    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $target)) {

        if ($currentPic !== 'default.jpg' && file_exists($uploadDir . $currentPic)) {
            unlink($uploadDir . $currentPic);
        }

        $currentPic = $newName;
    }
}

/* ================= START TRANSACTION ================= */
$conn->begin_transaction();

try {

    /* ===== UPDATE USERS ===== */
    $updateUser = $conn->prepare("
        UPDATE users SET
            firstName=?,
            middleName=?,
            lastName=?,
            suffix=?,
            birthDate=?,
            sex=?,
            contactNumber=?,
            username=?,
            streetAddress=?,
            barangay=?,
            cityMunicipality=?,
            province=?,
            country=?,
            zipCode=?,
            profilePic=?
        WHERE id=?
    ");

    $updateUser->bind_param(
        "sssssssssssssssi",
        $firstName,
        $middleName,
        $lastName,
        $suffix,
        $birthDate,
        $sex,
        $contactNumber,
        $usernameInput,
        $streetAddress,
        $barangay,
        $cityMunicipality,
        $province,
        $country,
        $zipCode,
        $currentPic,
        $userId
    );

    $updateUser->execute();
    $updateUser->close();

    /* ===== UPDATE SITTER (ONLY IF VERIFIED) ===== */
    if ($isVerifiedSitter && $sitterId) {

        if (!is_numeric($hourlyRate) || $hourlyRate < 0) {
            throw new Exception("Invalid rate");
        }

        if (!ctype_digit($experience) || (int)$experience < 0) {
            throw new Exception("Invalid experience");
        }

        $updateSitter = $conn->prepare("
            UPDATE sitters SET
                bio = ?,
                hourlyRate = ?,
                experience = ?,
                isAvailable = ?
            WHERE sitterID = ?
        ");

        $rate = (float)$hourlyRate;
        $exp  = (int)$experience;

        $updateSitter->bind_param(
            "sdiii",
            $bio,
            $rate,
            $exp,
            $isAvailable,
            $sitterId
        );

        $updateSitter->execute();
        $updateSitter->close();
    }

    $conn->commit();

    $_SESSION['username'] = $usernameInput;

    header('Location: /Pampeers/public/profile.php?update=success');
    exit();

} catch (Exception $e) {

    $conn->rollback();

    header('Location: /Pampeers/public/editProfile.php?error=update_failed');
    exit();
}