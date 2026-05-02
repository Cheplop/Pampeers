<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request");
}

/* REQUIRED FIELDS (NO ROLE) */
$requiredFields = [
    'firstName',
    'lastName',
    'email',
    'password',
    'birthDate',
    'sex',
    'streetAddress',
    'barangay',
    'cityMunicipality',
    'province',
    'country',
    'zipCode',
    'contactNumber',
    'username'
];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === "") {
        exit("Missing field: " . $field);
    }
}

/* INPUTS */
$firstName = trim($_POST['firstName']);
$lastName = trim($_POST['lastName']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];

$birthDate = $_POST['birthDate'];
$sex = $_POST['sex'];

$streetAddress = $_POST['streetAddress'];
$barangay = $_POST['barangay'];
$cityMunicipality = $_POST['cityMunicipality'];
$province = $_POST['province'];
$country = $_POST['country'];
$zipCode = $_POST['zipCode'];

$contactNumber = $_POST['contactNumber'];

/* FORCE ROLE */
$role = "guardian";

/* EMAIL CHECK */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Invalid email");
}

$check = $conn->prepare("SELECT id FROM users WHERE emailAddress = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    exit("Email already exists");
}
$check->close();

/* PROFILE PIC */
$profilePic = "default.jpg";

if (!empty($_FILES['profilePic']['name'])) {
    $uploadDir = __DIR__ . '/../../uploads/profiles/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (in_array($ext, $allowed)) {
        $filename = time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['profilePic']['tmp_name'], $uploadDir . $filename);
        $profilePic = $filename;
    }
}

/* PASSWORD */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* INSERT */
$stmt = $conn->prepare("
INSERT INTO users (
    uuid,
    firstName,
    lastName,
    birthDate,
    sex,
    role,
    contactNumber,
    emailAddress,
    username,
    password,
    streetAddress,
    barangay,
    cityMunicipality,
    province,
    country,
    zipCode,
    profilePic
)
VALUES (UUID(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "ssssssssssssssss",
    $firstName,
    $lastName,
    $birthDate,
    $sex,
    $role,
    $contactNumber,
    $email,
    $username,
    $hashedPassword,
    $streetAddress,
    $barangay,
    $cityMunicipality,
    $province,
    $country,
    $zipCode,
    $profilePic
);

if (!$stmt->execute()) {
    exit("Insert failed: " . $stmt->error);
}

$stmt->close();

/* NO SITTER LOGIC HERE (UPGRADE ONLY LATER) */

header("Location: /Pampeers/public/guestDashboard.php?success=registered");
exit();
?>