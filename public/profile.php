<?php
// Include your backend logic here or assume it's already executed at the top of the file.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/sitter.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/guestDashboard.php");
    exit();
}

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

/* ================= SITTER DATA ================= */
$sitterData = getSitter($conn, $userId);
$isSitter = !empty($sitterData);

$verificationStatus = $sitterData['verificationStatus'] ?? null;

/* store sitter_id safely */
if ($isSitter) {
    $_SESSION['sitter_id'] = $sitterData['sitterID'];
}

/* ================= FETCH INCOMING BOOKINGS ================= */
$bookings = [];
if ($isSitter && $verificationStatus === 'verified') {
    $sitterId = $sitterData['sitterID'] ?? 0;
    
    if ($sitterId > 0) {
        $bStmt = $conn->prepare("
            SELECT b.*, u.firstName, u.lastName, u.profilePic, u.cityMunicipality
            FROM bookings b
            JOIN users u ON b.userID = u.id
            WHERE b.sitterID = ?
            ORDER BY 
                CASE WHEN b.status = 'pending' THEN 1 ELSE 2 END, 
                b.bookingDate ASC
        ");
        $bStmt->bind_param("i", $sitterId);
        $bStmt->execute();
        $result = $bStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        $bStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pampeers</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .camera-icon-btn {
            bottom: 0;
            right: 50%;
            transform: translate(150%, 20%);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s ease;
        }
        .camera-icon-btn:hover { background-color: #e9ecef; }
    </style>
</head>
<body class="bg-light">

<header class="sticky-top custom-header bg-white shadow-sm p-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $isSitter ? 'sitter/sitterDashboard.php' : 'guardianDashboard.php' ?>" class="text-decoration-none text-dark">
                <i class="fa-solid fa-arrow-left me-2"></i>
                <span class="fw-bold fs-5">Profile</span>
            </a>
        </div>
    </div>
</header>

<main class="container pb-5" style="max-width: 600px;">
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= $_GET['success'] === 'bio_updated' ? 'Bio updated successfully!' : 'Profile picture updated!' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> Error updating profile.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="text-center mb-4 position-relative d-inline-block w-100">
        <div class="position-relative d-inline-block">
            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic'] ?? 'default.jpg') ?>" 
                 class="rounded-circle shadow-sm" 
                 style="width: 120px; height: 120px; object-fit: cover; border: 4px solid white;">
            
            <form action="../app/controllers/user/uploadProfilePic.php" method="POST" enctype="multipart/form-data">
                <label for="profilePicInput" class="btn btn-light border shadow-sm rounded-circle position-absolute camera-icon-btn text-primary">
                    <i class="fa-solid fa-camera"></i>
                </label>
                <input type="file" id="profilePicInput" name="profile_pic" class="d-none" onchange="this.form.submit()" accept="image/*">
            </form>
        </div>
        <h4 class="fw-bold mt-2 mb-0"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h4>
        <p class="text-muted small">@<?= htmlspecialchars($user['username']) ?></p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="fa-regular fa-id-badge me-2 text-primary"></i>About Me</h5>
        <form action="../app/controllers/user/updateBio.php" method="POST">
            <div class="mb-3">
                <textarea 
                    class="form-control bg-light border-0 shadow-sm rounded-3 p-3" 
                    name="bio" 
                    rows="3" 
                    placeholder="Tell guardians and sitters a little bit about yourself..."
                ><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm">Save Bio</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0">Basic Info</h5>
            <button class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fa-solid fa-pen"></i></button>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted"><i class="fa-regular fa-envelope me-2"></i>Email</span>
            <span class="fw-medium"><?= htmlspecialchars($user['email'] ?? 'Not set') ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted"><i class="fa-solid fa-phone me-2"></i>Phone</span>
            <span class="fw-medium"><?= htmlspecialchars($user['contactNumber'] ?? 'Not set') ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted"><i class="fa-solid fa-location-dot me-2"></i>Location</span>
            <span class="fw-medium"><?= htmlspecialchars($user['cityMunicipality'] ?? 'Not set') ?></span>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-3">Account Type</h5>
        
        <?php if ($isSitter): ?>
            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-3">
                <div>
                    <h6 class="fw-bold m-0 text-primary"><i class="fa-solid fa-baby-carriage me-2"></i> Babysitter Account</h6>
                    <small class="text-muted">You are registered as a Sitter.</small>
                </div>
                <span class="badge <?= $verificationStatus === 'verified' ? 'bg-success' : ($verificationStatus === 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?> rounded-pill px-3 py-2">
                    <?= ucfirst($verificationStatus ?? 'Unverified') ?>
                </span>
            </div>
            
            <div class="card border-0 bg-white shadow-sm p-3 rounded-3">
                <h6 class="fw-bold mb-3">Sitter Details</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Hourly Rate:</span>
                    <span class="fw-bold text-success">₱<?= htmlspecialchars($sitterData['hourlyRate'] ?? '0') ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Availability:</span>
                    <span class="badge <?= ($sitterData['isAvailable'] ?? 0) ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                        <?= ($sitterData['isAvailable'] ?? 0) ? 'Available' : 'Unavailable' ?>
                    </span>
                </div>
            </div>

        <?php else: ?>
            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                <div>
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-shield-halved me-2 text-primary"></i> Guardian Account</h6>
                    <small class="text-muted">Standard user account.</small>
                </div>
            </div>
            <div class="mt-3 text-center">
                <p class="small text-muted mb-2">Want to earn money by babysitting?</p>
                <a href="/Pampeers/public/sitterRegistration.php" class="btn btn-outline-primary rounded-pill px-4">Become a Sitter</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-5 mb-3">
        <a href="../app/controllers/auth/logout.php" class="btn btn-danger rounded-pill px-5 shadow-sm fw-bold">
            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Log Out
        </a>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>