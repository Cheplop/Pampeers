<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Pampeers/public/editProfile.php');
    exit();
}

$userId = $_SESSION['user_id'];

/*
|---------------------------------------
| INPUTS
|---------------------------------------
*/
$firstName        = trim($_POST['firstName'] ?? '');
$middleName       = trim($_POST['middleName'] ?? '');
$lastName         = trim($_POST['lastName'] ?? '');
$suffix           = trim($_POST['suffix'] ?? '');
$birthDate        = trim($_POST['birthDate'] ?? '');
$sex              = trim($_POST['sex'] ?? '');

$contactNumber    = trim($_POST['contactNumber'] ?? '');

/* EMAIL IS IGNORED FOR SECURITY */
$emailAddress     = ''; // DO NOT UPDATE EMAIL

$usernameInput    = trim($_POST['username'] ?? '');

$streetAddress    = trim($_POST['streetAddress'] ?? '');
$barangay         = trim($_POST['barangay'] ?? '');
$cityMunicipality = trim($_POST['cityMunicipality'] ?? '');
$province         = trim($_POST['province'] ?? '');
$country          = trim($_POST['country'] ?? '');
$zipCode          = trim($_POST['zipCode'] ?? '');

/*
|---------------------------------------
| VALIDATION
|---------------------------------------
*/
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

/*
|---------------------------------------
| GET CURRENT IMAGE
|---------------------------------------
*/
$stmt = $conn->prepare("SELECT profilePic FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$currentPic = $res['profilePic'] ?? 'default.jpg';

/*
|---------------------------------------
| UPLOAD IMAGE
|---------------------------------------
*/
if (!empty($_FILES['profilePic']['name'])) {

    $uploadDir = __DIR__ . '/../../../app/uploads/profiles/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = $_FILES['profilePic']['name'];
    $tmp      = $_FILES['profilePic']['tmp_name'];
    $size     = $_FILES['profilePic']['size'];
    $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $allowed)) {
        header('Location: /Pampeers/public/editProfile.php?error=invalid_image');
        exit();
    }

    if ($size > 5 * 1024 * 1024) {
        header('Location: /Pampeers/public/editProfile.php?error=image_too_large');
        exit();
    }

    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target  = $uploadDir . $newName;

    if (move_uploaded_file($tmp, $target)) {

        if ($currentPic !== 'default.jpg' && file_exists($uploadDir . $currentPic)) {
            unlink($uploadDir . $currentPic);
        }

        $currentPic = $newName;
    }
}

/*
|---------------------------------------
| UPDATE USER
|---------------------------------------
*/
$update = $conn->prepare("
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

$update->bind_param(
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

if ($update->execute()) {
    $update->close();

    $_SESSION['username'] = $usernameInput;

    header('Location: /Pampeers/public/profile.php?update=success');
    exit();
}

$update->close();

header('Location: /Pampeers/public/editProfile.php?error=update_failed');
exit();