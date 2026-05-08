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

/* BIO MOVED TO USERS, RATE/EXP STAY IN SITTERS */
$bio         = trim($_POST['bio'] ?? '');
$hourlyRate  = trim($_POST['hourlyRate'] ?? '0.00');
$experience  = trim($_POST['experience'] ?? '0');
$isAvailable = isset($_POST['isAvailable']) ? 1 : 0;

/* ACCEPTED AGES (ARRAY TO COMMA-SEPARATED STRING) */
$selectedAgesArray = $_POST['acceptedAges'] ?? [];
$agesString        = implode(',', $selectedAgesArray);

/* ================= VALIDATION ================= */
$required = [$firstName, $lastName, $birthDate, $sex, $contactNumber, $usernameInput, $cityMunicipality];
foreach ($required as $value) {
    if ($value === '') {
        header('Location: /Pampeers/public/editProfile.php?error=missing_fields');
        exit();
    }
}

/* ================= GET CURRENT USER & ROLE ================= */
$stmt = $conn->prepare("
    SELECT u.profilePic, u.role, s.sitterID, s.verificationStatus
    FROM users u
    LEFT JOIN sitters s ON s.userID = u.id
    WHERE u.id = ? LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
$stmt->close();

$currentPic = $current['profilePic'] ?? 'default.jpg';
$userRole   = $current['role']; 
$sitterId   = $current['sitterID'] ?? null;
$isVerifiedSitter = ($current['verificationStatus'] ?? '') === 'verified';

/* ================= IMAGE UPLOAD ================= */
if (!empty($_FILES['profilePic']['name'])) {
    $uploadDir = __DIR__ . '/../../uploads/profiles/'; 

    $ext = strtolower(pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (in_array($ext, $allowed) && $_FILES['profilePic']['size'] <= 5 * 1024 * 1024) {
        $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $uploadDir . $newName)) {
            if ($currentPic !== 'default.jpg' && file_exists($uploadDir . $currentPic)) {
                unlink($uploadDir . $currentPic);
            }
            $currentPic = $newName;
        }
    }
}

/* ================= TRANSACTION ================= */
$conn->begin_transaction();
try {
    // Update Users Table
    $updateUser = $conn->prepare("
        UPDATE users SET firstName=?, middleName=?, lastName=?, suffix=?, birthDate=?, sex=?, 
        contactNumber=?, username=?, streetAddress=?, barangay=?, cityMunicipality=?, 
        province=?, country=?, zipCode=?, profilePic=?, bio=? WHERE id=?
    ");
    $updateUser->bind_param("ssssssssssssssssi", $firstName, $middleName, $lastName, $suffix, $birthDate, $sex, 
        $contactNumber, $usernameInput, $streetAddress, $barangay, $cityMunicipality, 
        $province, $country, $zipCode, $currentPic, $bio, $userId);
    $updateUser->execute();

    // Update Sitters Table (Now includes acceptedAges!)
    if ($sitterId && $isVerifiedSitter) {
        $updateSitter = $conn->prepare("UPDATE sitters SET hourlyRate=?, experience=?, isAvailable=?, acceptedAges=? WHERE sitterID=?");
        $rate = (float)$hourlyRate;
        $exp  = (int)$experience;
        
        // diisi: double (rate), int (exp), int (isAvail), string (ages), int (sitterID)
        $updateSitter->bind_param("diisi", $rate, $exp, $isAvailable, $agesString, $sitterId);
        $updateSitter->execute();
    }

    $conn->commit();
    $_SESSION['username'] = $usernameInput;

    $redirectPage = 'profile.php';
    header("Location: /Pampeers/public/$redirectPage?update=success");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: /Pampeers/public/editProfile.php?error=update_failed');
    exit();
}