<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// Clear any existing session to prevent login confusion
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requiredFields = [
        'firstName',
        'lastName',
        'email',
        'password',
        'role',
        'birthdate',
        'sex',
        'street',
        'city',
        'country',
        'contactNumber'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            die("Error: Missing required field: " . $field);
        }
    }

    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $plainPassword = $_POST['password'];
    $role = trim($_POST['role']);

    $birthdate = trim($_POST['birthdate']);
    $sex = trim($_POST['sex']);

    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $contactNumber = trim($_POST['contactNumber']);

    $hourlyRate = isset($_POST['hourlyRate']) && $_POST['hourlyRate'] !== ''
        ? (float) $_POST['hourlyRate']
        : 0.00;

    $bio = !empty($_POST['bio']) ? trim($_POST['bio']) : '';
    $experience = isset($_POST['experience']) && $_POST['experience'] !== ''
        ? (int) $_POST['experience']
        : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email address.");
    }

    $allowedRoles = ['guardian', 'sitter', 'admin'];
    if (!in_array($role, $allowedRoles, true)) {
        die("Error: Invalid role selected.");
    }

    $allowedSex = ['male', 'female', 'other'];
    if (!in_array(strtolower($sex), $allowedSex, true)) {
        die("Error: Invalid sex selected.");
    }

    $checkStmt = $conn->prepare("SELECT uID FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: /pampeers/public/register.php?error=email_exists");
        exit();
    }
    $checkStmt->close();

    $profilePic = "default.jpg";

    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === 0) {
        $uploadDir = __DIR__ . '/../uploads/profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = $_FILES['profilePic']['name'];
        $tmpName = $_FILES['profilePic']['tmp_name'];
        $fileSize = $_FILES['profilePic']['size'];

        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExtension, $allowedExtensions, true)) {
            die("Error: Only JPG, JPEG, PNG, and WEBP files are allowed.");
        }

        if ($fileSize > 5 * 1024 * 1024) {
            die("Error: Profile picture must not exceed 5MB.");
        }

        $newFileName = time() . "_" . uniqid() . "." . $fileExtension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $profilePic = $newFileName;
        }
    }

    $passwordHashed = password_hash($plainPassword, PASSWORD_DEFAULT);

    $conn->begin_transaction();

    try {
        $userStmt = $conn->prepare("
            INSERT INTO users (
                firstName,
                lastName,
                email,
                birthdate,
                sex,
                password,
                role,
                profilePic,
                street,
                city,
                country,
                contactNumber
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $userStmt->bind_param(
            "ssssssssssss",
            $firstName,
            $lastName,
            $email,
            $birthdate,
            $sex,
            $passwordHashed,
            $role,
            $profilePic,
            $street,
            $city,
            $country,
            $contactNumber
        );

        $userStmt->execute();
        $uID = $conn->insert_id;
        $userStmt->close();

        if ($role === 'guardian') {
            $guardianStmt = $conn->prepare("
                INSERT INTO guardians (uID)
                VALUES (?)
            ");
            $guardianStmt->bind_param("i", $uID);
            $guardianStmt->execute();
            $guardianStmt->close();
        }

        if ($role === 'sitter') {
            $sitterStmt = $conn->prepare("
                INSERT INTO sitters (uID, hourlyRate, bio, experience, isAvailable)
                VALUES (?, ?, ?, ?, ?)
            ");

            $isAvailable = 1;

            $sitterStmt->bind_param(
                "idsii",
                $uID,
                $hourlyRate,
                $bio,
                $experience,
                $isAvailable
            );
            $sitterStmt->execute();
            $sitterStmt->close();
        }

        $conn->commit();
        header("Location: ../../public/login.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Database Error: " . $e->getMessage());
    }
}
?>