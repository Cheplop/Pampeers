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
    SELECT sitterID, verificationStatus, bio, hourlyRate, experience, isAvailable
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
            <a href="/Pampeers/public/<?= $backLink ?>" class="signup-btn text-decoration-none">Back</a>
        </div>
    </div>
</header>

<main class="container-fluid mt-4 px-4 pb-5">
    <div class="section-title text-center mb-4">Edit Your Profile</div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="small-card p-4 shadow-sm bg-white rounded-4">
                <form action="/Pampeers/app/controllers/user/updateProfile.php" method="POST" enctype="multipart/form-data">
                    
                    <!-- PROFILE PIC SECTION -->
                    <div class="text-center mb-4">
                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic'] ?? 'default.jpg') ?>"
                             class="rounded-circle shadow-sm border"
                             style="width:140px;height:140px;object-fit:cover;">
                        <div class="mt-3">
                            <label class="form-label small fw-bold">Change Profile Picture</label>
                            <input type="file" name="profilePic" class="form-control form-control-sm">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- PERSONAL INFO -->
                    <div class="row g-3">
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
                    </div>

                    <hr class="my-4">

                    <!-- ACCOUNT & CONTACT -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contactNumber" class="form-control" value="<?= htmlspecialchars($user['contactNumber'] ?? '') ?>" required>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- ADDRESS SECTION -->
                    <h6 class="fw-bold mb-3">Address Information</h6>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Street Address</label>
                            <input type="text" name="streetAddress" class="form-control" value="<?= htmlspecialchars($user['streetAddress'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="barangay" class="form-control" value="<?= htmlspecialchars($user['barangay'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City/Municipality</label>
                            <input type="text" name="cityMunicipality" class="form-control" value="<?= htmlspecialchars($user['cityMunicipality'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Province</label>
                            <input type="text" name="province" class="form-control" value="<?= htmlspecialchars($user['province'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="zipCode" class="form-control" value="<?= htmlspecialchars($user['zipCode'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- SITTER SPECIFIC SECTION -->
                    <?php if ($isVerifiedSitter): ?>
                        <hr class="my-4">
                        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-baby-carriage me-2"></i>Sitter Settings</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Professional Bio</label>
                            <textarea name="bio" class="form-control" rows="4" placeholder="Tell parents about your experience and care style..."><?= htmlspecialchars($sitterData['bio'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hourly Rate (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" name="hourlyRate" class="form-control" value="<?= htmlspecialchars($sitterData['hourlyRate'] ?? '0.00') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Years of Experience</label>
                                <input type="number" name="experience" class="form-control" value="<?= htmlspecialchars($sitterData['experience'] ?? '0') ?>">
                            </div>
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="isAvailable" id="availSwitch" <?= ($sitterData['isAvailable']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="availSwitch">Currently Available for Bookings</label>
                        </div>

                    <?php elseif ($isSitter): ?>
                        <div class="alert alert-info mt-4 rounded-3 border-0">
                            <i class="fa-solid fa-circle-info me-2"></i> Your sitter application is currently <strong>Pending Verification</strong>. Once approved, you can set your rates and bio.
                        </div>
                    <?php else: ?>
                        <div class="mt-4 text-center p-3 bg-light rounded-3">
                            <p class="mb-2 text-muted">Want to earn as a sitter?</p>
                            <a href="/Pampeers/app/controllers/user/becomeSitter.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Apply Now</a>
                        </div>
                    <?php endif; ?>

                    <div class="mt-5 text-center">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm">Save Changes</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

</body>
</html>