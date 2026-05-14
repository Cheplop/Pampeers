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

/* ================= USER DATA ================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

$role = $user['role'] ?? 'guardian';

/* ================= SITTER DATA ================= */
$sitterStmt = $conn->prepare("
    SELECT sitterID, verificationStatus, hourlyRate, experience, isAvailable
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

// Determine the back link based on role
$backLink = ($role === 'sitter') ? 'profile.php' : 'profile.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Pampeers</title>

    <link rel="icon" type="image/x-icon" href="/Pampeers/app/uploads/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="/Pampeers/public/css/register.css">

</head>

<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="signup-panel w-100">

        <div class="text-center mb-4">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="Logo" style="width: 70px;">
            <p class="small text-muted mb-0">Update your information</p>
            <h1 class="brand-name" style="font-size: 2rem; font-family: 'Poppins', sans-serif; font-weight: 600; margin-top: 0.5rem;">Edit Profile</h1>
        </div>

        <form action="/Pampeers/app/controllers/user/updateProfile.php" 
              method="POST" 
              enctype="multipart/form-data" 
              class="row g-3">
            
            <div class="col-12 text-center mb-2">
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic'] ?? 'default.jpg') ?>"
                     class="rounded-circle shadow-sm border mb-3"
                     style="width:120px;height:120px;object-fit:cover;">
                <div class="d-flex justify-content-center">
                    <input type="file" name="profilePic" class="form-control form-control-sm" style="max-width: 250px;">
                </div>
            </div>

            <div class="col-12 mb-2">
                <label class="form-label">About Me</label>
                <textarea name="bio" class="form-control" rows="3" placeholder="Tell everyone a little bit about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label">First Name</label>
                <input type="text" name="firstName" class="form-control" value="<?= htmlspecialchars($user['firstName'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Middle Name</label>
                <input type="text" name="middleName" class="form-control" value="<?= htmlspecialchars($user['middleName'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Last Name</label>
                <input type="text" name="lastName" class="form-control" value="<?= htmlspecialchars($user['lastName'] ?? '') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Suffix</label>
                <input type="text" name="suffix" class="form-control" placeholder="e.g. Jr." value="<?= htmlspecialchars($user['suffix'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Birth Date</label>
                <input type="date" name="birthDate" class="form-control" value="<?= htmlspecialchars($user['birthDate'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sex</label>
                <select name="sex" class="form-select">
                    <option value="male" <?= ($user['sex'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= ($user['sex'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= ($user['sex'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email Address</label>
                <input type="email" name="emailAddress" class="form-control" value="<?= htmlspecialchars($user['emailAddress'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contactNumber" class="form-control" value="<?= htmlspecialchars($user['contactNumber'] ?? '') ?>" required>
            </div>

            <div class="col-md-12">
                <label class="form-label">Street Address</label>
                <input type="text" name="streetAddress" class="form-control" value="<?= htmlspecialchars($user['streetAddress'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Barangay</label>
                <input type="text" name="barangay" class="form-control" value="<?= htmlspecialchars($user['barangay'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">City/Municipality</label>
                <input type="text" name="cityMunicipality" class="form-control" value="<?= htmlspecialchars($user['cityMunicipality'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Province</label>
                <input type="text" name="province" class="form-control" value="<?= htmlspecialchars($user['province'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Zip Code</label>
                <input type="text" name="zipCode" class="form-control" value="<?= htmlspecialchars($user['zipCode'] ?? '') ?>">
            </div>

            <?php if ($isVerifiedSitter): ?>
                <div class="col-12 mt-4 mb-1">
                    <h5 class="fw-bold grey m-0" style="font-family: 'Poppins', sans-serif;"><i class="fa-regular fa-calendar me-2"></i>Sitter Settings</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Hourly Rate (₱)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">₱</span>
                        <input type="number" step="0.01" name="hourlyRate" class="form-control" value="<?= htmlspecialchars($sitterData['hourlyRate'] ?? '0.00') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Years of Experience</label>
                    <input type="number" name="experience" class="form-control" value="<?= htmlspecialchars($sitterData['experience'] ?? '0') ?>">
                </div>

                <div class="col-12 mt-3">
                    <div class="form-check form-switch bg-none p-3 rounded-3 border">
                        <input class="form-check-input ms-1 me-3" type="checkbox" name="isAvailable" id="availSwitch" <?= ($sitterData['isAvailable']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="availSwitch">Currently Available for Bookings</label>
                    </div>
                </div>

            <?php elseif ($isSitter): ?>
                <div class="col-12 mt-4">
                    <div class="alert alert-info rounded-3 border-0 py-3">
                        <i class="fa-solid fa-circle-info me-2"></i> Your sitter application is currently <strong>Pending Verification</strong>. Once approved, you can set your rates.
                    </div>
                </div>
            <?php else: ?>
                <div class="col-12 mt-4">
                    <div class="text-center p-3 bg-light rounded-3 border">
                        <p class="mb-2 text-muted fw-bold">Want to earn money as a sitter?</p>
                        <a href="/Pampeers/app/controllers/user/becomeSitter.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Apply Now</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="border-radius: 10px;">SAVE CHANGES</button>
            </div>

        </form>

        <div class="text-center mt-4">
            <a href="/Pampeers/public/<?= $backLink ?>" class="text-decoration-none text-muted fw-bold"><i class="fa-solid fa-arrow-left me-2"></i>Back to Profile</a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>