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

/* SITTER FIELDS */
$bio         = trim($_POST['bio'] ?? '');
$hourlyRate  = trim($_POST['hourlyRate'] ?? '0.00');
$experience  = trim($_POST['experience'] ?? '0');
$isAvailable = isset($_POST['isAvailable']) ? 1 : 0;

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
$userRole   = $current['role']; // Store role for redirection
$sitterId   = $current['sitterID'] ?? null;
$isVerifiedSitter = ($current['verificationStatus'] ?? '') === 'verified';

/* ================= IMAGE UPLOAD ================= */
if (!empty($_FILES['profilePic']['name'])) {
    // Corrected path based on your pampeersFolder_4.txt[cite: 3]
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
    $updateUser = $conn->prepare("
        UPDATE users SET firstName=?, middleName=?, lastName=?, suffix=?, birthDate=?, sex=?, 
        contactNumber=?, username=?, streetAddress=?, barangay=?, cityMunicipality=?, 
        province=?, country=?, zipCode=?, profilePic=? WHERE id=?
    ");
    $updateUser->bind_param("sssssssssssssssi", $firstName, $middleName, $lastName, $suffix, $birthDate, $sex, 
        $contactNumber, $usernameInput, $streetAddress, $barangay, $cityMunicipality, 
        $province, $country, $zipCode, $currentPic, $userId);
    $updateUser->execute();

    // Only update sitters table if they are a verified sitter
    if ($sitterId && $isVerifiedSitter) {
        $updateSitter = $conn->prepare("UPDATE sitters SET bio=?, hourlyRate=?, experience=?, isAvailable=? WHERE sitterID=?");
        $rate = (float)$hourlyRate;
        $exp  = (int)$experience;
        $updateSitter->bind_param("sdiii", $bio, $rate, $exp, $isAvailable, $sitterId);
        $updateSitter->execute();
    }

    $conn->commit();
    $_SESSION['username'] = $usernameInput;

    // Redirect based on role[cite: 3]
    $redirectPage = ($userRole === 'sitter') ? 'profile.php' : 'profile.php';
    header("Location: /Pampeers/public/$redirectPage?update=success");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: /Pampeers/public/editProfile.php?error=update_failed');
    exit();
}