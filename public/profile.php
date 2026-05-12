<?php
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

// 1. AUTOMATIC AGE CALCULATION
$age = 'N/A';
if (!empty($user['birthDate'])) {
    $dob = new DateTime($user['birthDate']);
    $today = new DateTime('today');
    $age = $dob->diff($today)->y;
}

// 2. AUTOMATIC JOINED TIME CALCULATION
$joinedText = 'N/A';
if (!empty($user['createdAt'])) {
    $created = new DateTime($user['createdAt']);
    $today = new DateTime('today');
    $diff = $created->diff($today);
    
    if ($diff->y > 0) $joinedText = $diff->y . " year" . ($diff->y > 1 ? "s" : "") . " ago";
    elseif ($diff->m > 0) $joinedText = $diff->m . " month" . ($diff->m > 1 ? "s" : "") . " ago";
    else $joinedText = $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
}

// 3. Format Address
$city = $user['cityMunicipality'] ?? '';
$prov = $user['province'] ?? '';
$location = trim(implode(', ', array_filter([$city, $prov])));

// Variables for HTML
$profilePic = $user['profilePic'] ?? 'default.jpg';
$emailAddress = $user['emailAddress'] ?? 'No email set';
$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
$bio = $user['bio'] ?? '';

/* ================= SITTER DATA & REVIEWS ================= */
$sitterData = getSitter($conn, $userId);
$isSitter = !empty($sitterData);
$verificationStatus = $sitterData['verificationStatus'] ?? null;

$recentReviews = null;
if ($isSitter) {
    // Fetch the single latest feedback
    $revStmt = $conn->prepare("
        SELECT r.comment, u.firstName 
        FROM reviews r 
        JOIN users u ON r.userID = u.id 
        WHERE r.sitterID = ? 
        ORDER BY r.createdAt DESC LIMIT 1
    ");
    $revStmt->bind_param("i", $sitterData['sitterID']);
    $revStmt->execute();
    $recentReviews = $revStmt->get_result()->fetch_assoc();
    $revStmt->close();
}

/* ================= FETCH ACTIVE BOOKINGS ONLY ================= */
$bookings = [];
if ($isSitter) {
    // Only show pending or accepted (hides completed automatically)
    $bookStmt = $conn->prepare("
        SELECT b.*, u.firstName, u.profilePic, u.cityMunicipality 
        FROM bookings b 
        JOIN users u ON b.userID = u.id 
        WHERE b.sitterID = ? AND b.status IN ('pending', 'accepted')
        ORDER BY b.startDateTime ASC LIMIT 1
    ");
    $bookStmt->bind_param("i", $sitterData['sitterID']);
    $bookStmt->execute();
    $bookings = $bookStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $bookStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Pampeers</title>
    <link rel="icon" href="/Pampeers/app/uploads/pampeerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Pampeers/public/css/guardianProfile.css">
</head>
<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">
        <div class="d-none d-lg-flex align-items-center gap-2">
            <a href="/Pampeers/public/guardian/guardianDashboard.php">
                <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img" alt="Pampeers Logo">
            </a>
            <p class="brand m-0"><a href="/Pampeers/public/guardian/guardianDashboard.php">Pampeers</a></p>
        </div>

        <div class="right-side-p d-flex align-items-center justify-content-end gap-3 ms-auto">
            <div class="sitter-status-actions d-flex align-items-center gap-2">
                <?php if ($isSitter): ?>
                    <?php if ($verificationStatus === 'verified'): ?>
                        <a href="/Pampeers/public/sitter/sitterDashboard.php" class="verified-btn">verified sitter</a>
                    <?php elseif ($verificationStatus === 'pending'): ?>
                        <span class="btn btn-secondary btn-sm disabled">Sitter Pending</span>
                    <?php else: ?>
                        <span class="btn btn-danger btn-sm disabled">Not Verified</span>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/Pampeers/app/controllers/user/becomeSitter.php" class="btnbecome">become a Sitter</a>
                <?php endif; ?>
            </div>
            
            <div class="nav-btn d-flex align-items-center gap-2">
                <a href="profile.php" class="text-decoration-none">
                    <div class="profile-wrapper">
                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic); ?>" class="profile-img" alt="Profile">
                    </div>
                </a>
                <div class="dropdown">
                    <button class="btn-dropdown border-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fa-regular fa-user me-2"></i>View Profile</a></li>
                        <li><a class="dropdown-item" href="guardian/myFavourites.php"><i class="fa-regular fa-heart me-2"></i>Favourites</a></li>
                        <li><a class="dropdown-item" href="/Pampeers/public/guardian/myBookings.php"><i class="fa-regular fa-calendar me-2"></i>Bookings</a></li>
                        <li class="logout"><a class="dropdown-item" href="/Pampeers/app/controllers/auth/logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="container-fluid-lg mt-4 px-4 pb-5">
    <div class="row justify-content-center gx-4 mb-4">
        <div class="col-lg-5 col-md-6 col-sm-12 mb-3 mb-lg-0">
            <div class="profile-card d-flex flex-column flex-md-row gap-4 p-3 h-100"> 
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" class="profile-img">
                <div class="d-flex flex-column justify-content-center">
                    <div>
                        <p class="text-muted m-0" style="font-size: 0.9rem;"><?= htmlspecialchars($emailAddress) ?></p>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="mb-1"><?= htmlspecialchars($fullName) ?></h4>
                            <a href="/Pampeers/public/editProfile.php" class="edit-icon"><i class="fa-solid fa-pen"></i></a>
                        </div>
                        <p class="mb-3"><?= !empty($bio) ? htmlspecialchars($bio) : 'Bio place here' ?></p>
                    </div>
                </div>  
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="details-card d-flex p-3 h-100">
                <div class="d-flex flex-column justify-content-center w-100">
                    <p class="m-2 py-2"><i class="fa-solid fa-cake-candles me-2"></i> Age: <?= $age ?></p>
                    <hr class="m-0">
                    <p class="m-2 py-2"><i class="fa-solid fa-location-arrow me-2"></i> Address: <?= htmlspecialchars($location) ?></p>
                    <hr class="m-0">
                    <p class="m-2 py-2"><i class="fa-solid fa-users me-2"></i> Joined: <?= $joinedText ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center profile-content-row mb-5">
        <div class="col-lg-9">
            <div class="row gx-4">
                <div class="col-lg-5 col-md-6 mb-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3 booking-header">
                        <p class="m-0 fw-light">Booking Requests</p>
                        <a href="/Pampeers/public/sitter/sitterDashboard.php" class="see-all-text fw-bold">
                            All <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="flex-grow-1">
                        <?php if ($isSitter && $verificationStatus === 'verified'): ?>
                            <?php if (empty($bookings)): ?>
                                <div class="card p-4 text-center rounded-4 h-100 d-flex align-items-center justify-content-center">
                                    <small class="text-muted">No active requests</small>
                                </div>
                            <?php else: ?>
                                <?php $latest = $bookings[0]; ?>
                                <div class="card rounded-4 p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($latest['profilePic'] ?: 'default.jpg') ?>" class="booking-avatar-preview me-3" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                                        <div>
                                            <h6 class="m-0 fw-bold"><?= htmlspecialchars($latest['firstName']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($latest['cityMunicipality']) ?></small>
                                        </div>
                                    </div>
                                    <div class="booking-sub-box p-3 rounded-3 mb-3 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Date:</span>
                                            <strong><?= date('M d', strtotime($latest['startDateTime'])) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Payout:</span>
                                            <strong>₱<?= number_format($latest['totalAmount'], 2) ?></strong>
                                        </div>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="/Pampeers/public/sitter/sitterDashboard.php" class="btn w-100 rounded-pill fw-bold" style="background-color: #ff914d; color: white; border: none;">Manage</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="p-4 text-center border rounded-4 bg-light h-100 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-shield-cat text-muted opacity-25 fa-3x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-7 col-md-6 mb-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="m-0 fw-light">Recent Reviews</p>
                    </div>
                    <div class="card p-4 rounded-4 flex-grow-1">
                        <div class="d-flex flex-column h-100">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-quote-left review-quote-icon me-2"></i> Latest Feedback</h6>
                            <?php if ($recentReviews): ?>
                                <p class="mb-3 fst-italic small">"<?= htmlspecialchars($recentReviews['comment']) ?>"</p>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">~ <?= htmlspecialchars($recentReviews['firstName']) ?></span>
                                    <a href="#" class="see-all-text text-decoration-none fw-bold">Read More</a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small">No reviews yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>