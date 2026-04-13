<?php
require_once __DIR__ . '/../../middleware/authCheck.php';
require_once __DIR__ . '/../../config/db_connect.php';

checkAuth('sitter');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $userId = $_SESSION['user_id'];

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
        header("Location: sitterDashboard.php?status=missing_fields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: sitterDashboard.php?status=invalid_email");
        exit();
    }

    $allowedSex = ['male', 'female', 'other'];
    if (!in_array(strtolower($sex), $allowedSex, true)) {
        header("Location: sitterDashboard.php?status=invalid_sex");
        exit();
    }

    $checkStmt = $conn->prepare("SELECT uID FROM users WHERE email = ? AND uID != ?");
    $checkStmt->bind_param("si", $email, $userId);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: sitterDashboard.php?status=email_exists");
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
            header("Location: sitterDashboard.php?status=invalid_image");
            exit();
        }

        if ($fileSize > 5 * 1024 * 1024) {
            header("Location: sitterDashboard.php?status=image_too_large");
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
        header("Location: sitterDashboard.php?status=profile_updated");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: sitterDashboard.php?status=error");
        exit();
    }
}

header("Location: sitterDashboard.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Sitter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .form-section { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn-back { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <a href="sitterDashboard.php" class="btn btn-secondary btn-back">&larr; Back to Dashboard</a>

        <div class="form-section">
            <h2 class="mb-4">Update Your Profile</h2>

            <?php
            $userId = $_SESSION['user_id'];
            $stmt = $conn->prepare("
                SELECT
                    u.firstName,
                    u.lastName,
                    u.email,
                    u.birthdate,
                    u.sex,
                    u.street,
                    u.city,
                    u.country,
                    u.contactNumber,
                    s.hourlyRate,
                    s.bio,
                    s.experience
                FROM users u
                INNER JOIN sitters s ON u.uID = s.uID
                WHERE u.uID = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $stmt->close();
            ?>

            <form action="updateProfile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">

                <h5 class="mb-3">Personal Information</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="firstName" class="form-control" value="<?= htmlspecialchars($userData['firstName'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lastName" class="form-control" value="<?= htmlspecialchars($userData['lastName'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Birthdate</label>
                        <input type="date" name="birthdate" class="form-control" value="<?= htmlspecialchars($userData['birthdate'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="sex" class="form-select" required>
                            <option value="">Choose...</option>
                            <option value="male" <?= (($userData['sex'] ?? '') === 'male' ? 'selected' : '') ?>>Male</option>
                            <option value="female" <?= (($userData['sex'] ?? '') === 'female' ? 'selected' : '') ?>>Female</option>
                            <option value="other" <?= (($userData['sex'] ?? '') === 'other' ? 'selected' : '') ?>>Other</option>
                        </select>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Address Information</h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($userData['country'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($userData['city'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Street</label>
                        <input type="text" name="street" class="form-control" value="<?= htmlspecialchars($userData['street'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contactNumber" class="form-control" placeholder="09xxxxxxxxx" value="<?= htmlspecialchars($userData['contactNumber'] ?? '') ?>" required>
                </div>

                <hr>
                <h5 class="mb-3">Professional Information</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Hourly Rate (₱)</label>
                        <input type="number" step="0.01" min="0" name="hourlyRate" class="form-control" value="<?= htmlspecialchars($userData['hourlyRate'] ?? '0.00') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" min="0" name="experience" class="form-control" value="<?= htmlspecialchars($userData['experience'] ?? '0') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="4" placeholder="Tell clients about yourself..."><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="profilePic" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <small class="text-muted">Max 5MB. Formats: JPG, PNG, WEBP</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="sitterDashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>