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

// Map database columns to the variables used in your design
$profilePic = $user['profilePic'] ?? 'default.jpg';
$email      = $user['email'] ?? 'No email set';
$fullName   = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
$bio        = $user['bio'] ?? '';
$location   = trim(($user['cityMunicipality'] ?? '') . ', ' . ($user['province'] ?? ''));

/* ================= SITTER DATA ================= */
$sitterData = getSitter($conn, $userId);
$isSitter = !empty($sitterData);

$verificationStatus = $sitterData['verificationStatus'] ?? null;

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

        <div class="d-flex d-none d-md-flex align-items-center gap-2">
            <a href="/Pampeers/public/guardian/guardianDashboard.php">
                <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img" alt="Pampeers Logo">
            </a>
            <p class="brand m-0">Pampeers</p>
        </div>

        <div class="right-side-p d-flex align-items-center justify-content-end gap-3">
            
                <?php if ($isSitter): ?>
                    <?php else: ?>
                        <a href="..." class="btnbecome">become a Sitter</a>
                    <?php endif; ?>

            <?php if ($isSitter): ?>
                <?php if ($verificationStatus === 'verified'): ?>
                    <a href="/Pampeers/public/sitter/sitterDashboard.php" class="signup-btn">
                        Verified Sitter
                    </a>
                <?php elseif ($verificationStatus === 'pending'): ?>
                    <span class="btn btn-secondary btn-sm disabled">
                        Sitter Pending
                    </span>
                <?php else: ?>
                    <span class="btn btn-danger btn-sm disabled">
                        Not Verified
                    </span>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="nav-btn d-flex gap-2">
                <a href="profile.php" class="text-decoration-none">
                    <div class="profile-wrapper">
                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic); ?>" class="profile-img" alt="Profile" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    </div>
                </a>

                <div class="dropdown">
                    <button class="btn" type="button" data-bs-toggle="dropdown" data-bs-offset="0,15" =aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="profile.php"><i class="fa-regular fa-user me-2"></i>View Profile</a>
                        </li>
                        <li><button class="dropdown-item" type="button"><i class="fa-regular fa-heart me-2"></i>Favourites</button>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/Pampeers/public/sitter/sitterDashboard.php"><i class="fa-solid fa-baby-carriage me-2"></i>Bookings</a>
                        </li>
                        <li class="logout">
                            <a class="dropdown-item" href="/Pampeers/app/controllers/auth/logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</header>
<main class="container-fluid-lg mt-4 px-4 pb-5">
    
    <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
        <div class="row justify-content-center mb-3">
            <div class="col-lg-9">
                <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> Profile updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center gx-4 mb-4">
        <div class="col-lg-5 col-md-6 col-sm-12 mb-3 mb-lg-0">
            <div class="profile-card d-flex flex-column flex-md-row gap-4 p-3 h-100"> 
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic) ?>" 
                     alt="Profile Picture" class="profile-img">
                
                <div class="d-flex flex-column justify-content-center">
                    <div>
                        <p class="text-muted m-0" style="font-size: 0.9rem;"><?= htmlspecialchars($email) ?></p>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="mb-1"><?= htmlspecialchars($fullName) ?></h4>
                            <a href="/Pampeers/public/editProfile.php" class="edit-icon">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </div>
                        <p class="mb-3"><?= !empty($bio) ? htmlspecialchars($bio) : 'Bio place here' ?></p>
                    </div>
                </div>  
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="details-card d-flex p-3 h-100">
                <div class="d-flex flex-column justify-content-center w-100">
                    <p class="m-2 py-2"><i class="fa-solid fa-cake-candles me-2"></i> Age: 67</p>
                    <hr class="m-0">
                    <p class="m-2 py-2"><i class="fa-solid fa-location-arrow me-2"></i> Address: <?= htmlspecialchars($location) ?></p>
                    <hr class="m-0">
                    <p class="m-2 py-2"><i class="fa-solid fa-users me-2"></i> Joined: 67 Years ago</p>
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
                        <?php if ($isSitter && $verificationStatus === 'verified' && !empty($bookings)): ?>
                            <a href="/Pampeers/public/sitter/sitterDashboard.php" class="see-all-text fw-bold">
                                All <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        <?php endif; ?>
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
                                        <img src="/Pampeers/app/uploads/profiles/<?= !empty($latest['profilePic']) ? htmlspecialchars($latest['profilePic']) : 'default.jpg' ?>" 
                                             class="booking-avatar-preview me-3" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                                        <div>
                                            <h6 class="m-0 fw-bold"><?= htmlspecialchars($latest['firstName']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($latest['cityMunicipality']) ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-sub-box p-3 rounded-3 mb-3 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Date:</span>
                                            <strong><?= date('M d', strtotime($latest['bookingDate'])) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Payout:</span>
                                            <strong class="">₱<?= number_format($latest['totalAmount'], 2) ?></strong>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        <a href="/Pampeers/public/sitter/sitterDashboard.php" class="btn w-100 rounded-pill fw-bold">
                                            Manage
                                        </a>
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
                            <h6 class="fw-bold mb-3">
                                <i class="fa-solid fa-quote-left review-quote-icon me-2"></i> 
                                Latest Feedback
                            </h6>
                            <p class="mb-3 fst-italic small">
                                "Charles was very kind and gentle to my kids. He is loved and favorites. I would work with him again"
                            </p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="text-muted small">~ Jared</span>
                                <a href="#" class="see-all-text text-decoration-none fw-bold">Read More</a>
                            </div>
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