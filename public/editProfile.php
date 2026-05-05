<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/guestDashboard.php");
    exit();
}

/* ================= USER ================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

$role = $user['role'] ?? 'guardian';

/* ================= SITTER CHECK ================= */
$sitterStmt = $conn->prepare("
    SELECT sitterID, verificationStatus, bio, hourlyRate, experience
    FROM sitters
    WHERE userID = ?
    LIMIT 1
");
$sitterStmt->bind_param("i", $userId);
$sitterStmt->execute();
$sitterData = $sitterStmt->get_result()->fetch_assoc();
$sitterStmt->close();

$isSitter = !empty($sitterData);
$isVerifiedSitter = $isSitter && $sitterData['verificationStatus'] === 'verified';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile - Pampeers</title>

<link rel="icon" href="/Pampeers/app/uploads/pampeerlogo.png">
<link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/Pampeers/public/css/dashboard.css">
</head>

<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">

        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <div class="right-side-p d-flex gap-3">
            <a href="/Pampeers/public/profile.php" class="signup-btn">Back</a>
        </div>

    </div>
</header>

<main class="container-fluid mt-4 px-4">

<div class="section-title">Edit Profile</div>

<div class="row justify-content-center">
<div class="col-lg-8">

<div class="small-card p-4">

<form action="/Pampeers/app/controllers/user/updateProfile.php" method="POST" enctype="multipart/form-data">

<!-- PROFILE PIC -->
<div class="text-center mb-3">
    <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic'] ?? 'default.jpg') ?>"
         style="width:120px;height:120px;border-radius:50%;object-fit:cover;">
</div>

<div class="mb-3">
    <label>Profile Picture</label>
    <input type="file" name="profilePic" class="form-control">
</div>

<hr>

<!-- NAME -->
<div class="row">
    <div class="col">
        <label>First Name</label>
        <input type="text" name="firstName" class="form-control"
               value="<?= htmlspecialchars($user['firstName'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Middle Name</label>
        <input type="text" name="middleName" class="form-control"
               value="<?= htmlspecialchars($user['middleName'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Last Name</label>
        <input type="text" name="lastName" class="form-control"
               value="<?= htmlspecialchars($user['lastName'] ?? '') ?>">
    </div>
</div>

<div class="row mt-2">
    <div class="col">
        <label>Suffix</label>
        <input type="text" name="suffix" class="form-control"
               value="<?= htmlspecialchars($user['suffix'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Birth Date</label>
        <input type="date" name="birthDate" class="form-control"
               value="<?= htmlspecialchars($user['birthDate'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Sex</label>
        <select name="sex" class="form-control">
            <option value="male" <?= ($user['sex'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= ($user['sex'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="other" <?= ($user['sex'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
        </select>
    </div>
</div>

<hr>

<!-- CONTACT -->
<div class="row">
    <div class="col">
        <label>Contact Number</label>
        <input type="text" name="contactNumber" class="form-control"
               value="<?= htmlspecialchars($user['contactNumber'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Email</label>
        <input type="text" class="form-control"
               value="<?= htmlspecialchars($user['emailAddress'] ?? '') ?>" disabled>
    </div>

    <div class="col">
        <label>Username</label>
        <input type="text" name="username" class="form-control"
               value="<?= htmlspecialchars($user['username'] ?? '') ?>">
    </div>
</div>

<hr>

<!-- ADDRESS -->
<div class="row">
    <div class="col">
        <label>Street Address</label>
        <input type="text" name="streetAddress" class="form-control"
               value="<?= htmlspecialchars($user['streetAddress'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Barangay</label>
        <input type="text" name="barangay" class="form-control"
               value="<?= htmlspecialchars($user['barangay'] ?? '') ?>">
    </div>
</div>

<div class="row mt-2">
    <div class="col">
        <label>City</label>
        <input type="text" name="cityMunicipality" class="form-control"
               value="<?= htmlspecialchars($user['cityMunicipality'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Province</label>
        <input type="text" name="province" class="form-control"
               value="<?= htmlspecialchars($user['province'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Country</label>
        <input type="text" name="country" class="form-control"
               value="<?= htmlspecialchars($user['country'] ?? '') ?>">
    </div>

    <div class="col">
        <label>Zip Code</label>
        <input type="text" name="zipCode" class="form-control"
               value="<?= htmlspecialchars($user['zipCode'] ?? '') ?>">
    </div>
</div>

<hr>

<!-- ROLE INFO -->
<div class="mb-3">
    <strong>Role:</strong> Guardian
    <?php if ($isVerifiedSitter): ?>
        (Verified Sitter)
    <?php endif; ?><br>
    <strong>Status:</strong> <?= ((int)$user['isActive']) ? 'Active' : 'Inactive' ?>
</div>

<hr>

<!-- ================= SITTER SECTION ================= -->
<?php if ($isVerifiedSitter): ?>

    <div class="section-title mt-3">Sitter Profile</div>

    <div class="mb-3">
        <label>Bio</label>
        <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($sitterData['bio'] ?? '') ?></textarea>
    </div>

    <div class="row">
        <div class="col">
            <label>Hourly Rate (₱)</label>
            <input type="number" step="0.01" name="hourlyRate"
                   class="form-control"
                   value="<?= htmlspecialchars($sitterData['hourlyRate'] ?? 0) ?>">
        </div>

        <div class="col">
            <label>Experience (years)</label>
            <input type="number" name="experience"
                   class="form-control"
                   value="<?= htmlspecialchars($sitterData['experience'] ?? 0) ?>">
        </div>
    </div>

    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="isAvailable"
            <?= (!empty($sitterData['isAvailable'])) ? 'checked' : '' ?>>
        <label class="form-check-label">Available for booking</label>
    </div>

<?php elseif ($isSitter): ?>

    <div class="alert alert-warning mt-3">
        Sitter account is pending verification.
    </div>

<?php else: ?>

    <a href="/Pampeers/app/controllers/user/becomeSitter.php"
       class="btn btn-success btn-sm mt-2">
        Become a Sitter
    </a>

<?php endif; ?>

<hr>

<button type="submit" class="btn btn-success">
    Save Changes
</button>

</form>

</div>
</div>
</div>
</main>

</body>
</html>