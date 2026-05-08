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
    <link rel="stylesheet" href="/Pampeers/public/css/sitterDashboard.css">
</head>

<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">

        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img" alt="Pampeers Logo">
            <p class="brand m-0">Pampeers</p>
        </div>

        <div class="right-side-p d-flex align-items-center gap-3">
            
            <a href="/Pampeers/public/guardian/guardianDashboard.php" class="signup-btn">
                Guardian Dashboard
            </a>

            <?php if ($isSitter): ?>
                <?php if ($verificationStatus === 'verified'): ?>
                    <a href="/Pampeers/public/sitter/sitterDashboard.php" class="signup-btn">
                        Sitter Dashboard
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

            <a href="profile.php" class="text-decoration-none">
                <div class="profile-wrapper">
                    <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic); ?>" class="profile-img" alt="Profile" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                </div>
            </a>

            <div class="dropdown">
                <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" type="button">Favourites</button></li>
                    <li>
                        <a class="dropdown-item" href="profile.php"><b>Profile</b></a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="/Pampeers/app/controllers/auth/logout.php">Logout</a>
                    </li>
                </ul>
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

        <div class="col-lg-5 col-sm-12 align-self-center">
            <div class="profile-card d-flex flex-column flex-md-row gap-4 border p-3"> 
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" style="width:120px;height:120px;border-radius:50%;object-fit:cover;">
                
                <div class="flex-column">
                    <p class="text-muted m-0"><?= htmlspecialchars($email) ?></p>
                    <h4><?= htmlspecialchars($fullName) ?></h4>
                    <p><?= !empty($bio) ? htmlspecialchars($bio) : 'Bio place here' ?></p>
                        
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="/Pampeers/public/editProfile.php" class="btnedit btn btn-primary btn-sm">Edit Profile</a>

                        <?php if ($isSitter): ?>
                            <a href="/Pampeers/public/editProfile.php" class="btn btn-warning btn-sm">Edit Sitter Profile</a>

                            <?php if ($verificationStatus === 'pending'): ?>
                                <span class="btn btn-secondary btn-sm disabled">Pending Verification</span>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <a href="/Pampeers/app/controllers/user/becomeSitter.php" class="btnbecome btn btn-success btn-sm">Become a Sitter</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-sm-12 align-self-center mt-3 mt-lg-0">
            <div class="details-card d-flex gap-4 border p-3 h-100">
                <div class="d-flex flex-column w-100 justify-content-center">
                    <p class="m-0 py-2"><i class="fa-solid fa-cake-candles me-2"></i> Age: 67</p>
                    <hr class="m-0">
                    <p class="m-0 py-2"><i class="fa-solid fa-location-arrow me-2"></i> Address: <?= htmlspecialchars($location) ?></p>
                    <hr class="m-0">
                    <p class="m-0 py-2"><i class="fa-solid fa-users me-2"></i> Joined: 67 Years ago</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center mb-5">
        <div class="col-lg-9">
            <div class="review-card border p-4 rounded">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-quote-left text-primary me-2"></i> Recent Reviews</h6>
                <p class="mb-2 fst-italic">"Charles was very kind and gentle to my kids. He is loved and favorites. I would work with him again"</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">~ Jared</span>
                    <a href="#" class="read-more text-decoration-none small">Read More</a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isSitter && $verificationStatus === 'verified'): ?>
        <hr class="my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0" style="font-family: 'Poppins', sans-serif;">Booking Requests</h4>
        </div>

        <?php if (isset($_GET['status_updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                Booking marked as <strong><?= htmlspecialchars($_GET['status_updated']) ?></strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="alert alert-light text-center p-5 rounded-4 shadow-sm border-0 mt-5">
                <i class="fa-regular fa-calendar-xmark fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No booking requests yet.</h5>
                <p class="text-muted small">When guardians book you, their requests will appear here.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($bookings as $booking): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                            
                            <div class="d-flex align-items-center mb-3">
                                <img src="/Pampeers/app/uploads/profiles/<?= !empty($booking['profilePic']) ? htmlspecialchars($booking['profilePic']) : 'default.jpg' ?>" 
                                     class="rounded-circle me-3" 
                                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #eee;">
                                <div>
                                    <h6 class="m-0 fw-bold"><?= htmlspecialchars($booking['firstName'] . ' ' . $booking['lastName']) ?></h6>
                                    <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($booking['cityMunicipality']) ?></small>
                                </div>
                            </div>
                            
                            <div class="bg-light p-3 rounded-3 mb-3 small" style="font-family: 'Poppins', sans-serif;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Date:</span> 
                                    <strong><?= date('M d, Y', strtotime($booking['bookingDate'])) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Time:</span> 
                                    <strong><?= date('h:i A', strtotime($booking['startTime'])) ?> - <?= date('h:i A', strtotime($booking['endTime'])) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Hours:</span> 
                                    <strong><?= htmlspecialchars($booking['hoursRequested']) ?> hrs</strong>
                                </div>
                                <?php if (!empty($booking['notes'])): ?>
                                    <div class="mt-2 text-muted italic">
                                        <i class="fa-solid fa-quote-left me-1 opacity-50"></i><?= htmlspecialchars($booking['notes']) ?>
                                    </div>
                                <?php endif; ?>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Payout:</span> 
                                    <strong class="text-primary fs-6">₱<?= number_format($booking['totalAmount'], 2) ?></strong>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <div class="d-flex gap-2">
                                        <a href="/Pampeers/app/controllers/booking/updateStatus.php?id=<?= $booking['bookingID'] ?>&status=accepted" 
                                        class="btn btn-primary btn-sm w-100 rounded-pill fw-bold py-2 shadow-sm">Accept</a>
                                        
                                        <a href="/Pampeers/app/controllers/booking/updateStatus.php?id=<?= $booking['bookingID'] ?>&status=declined" 
                                        class="btn btn-outline-danger btn-sm w-100 rounded-pill fw-bold py-2">Decline</a>
                                    </div>
                                <?php elseif ($booking['status'] === 'accepted'): ?>
                                    <div class="d-grid gap-2">
                                        <div class="badge bg-success py-2 rounded-pill mb-2"><i class="fa-solid fa-check-circle me-1"></i> Accepted</div>
                                        
                                        <a href="/Pampeers/app/controllers/booking/updateStatus.php?id=<?= $booking['bookingID'] ?>&status=completed" 
                                        class="btn btn-outline-primary btn-sm rounded-pill fw-bold py-2">Mark as Completed</a>
                                    </div>
                                <?php else: ?>
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        if ($booking['status'] === 'declined' || $booking['status'] === 'cancelled') $badgeClass = 'bg-danger-subtle text-danger';
                                        if ($booking['status'] === 'completed') $badgeClass = 'bg-info-subtle text-info border border-info';
                                    ?>
                                    <div class="text-center">
                                        <span class="badge rounded-pill <?= $badgeClass ?> w-100 py-2 fs-6">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>