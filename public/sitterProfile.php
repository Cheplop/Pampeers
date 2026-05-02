<?php
require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/config/config.php';

requireAuth();

/* ROLE CHECK (replacement for checkAuth) */
if (!in_array($_SESSION['role'], ['sitter', 'guardian'])) {
    header("Location: /Pampeers/public/guestDashboard.php");
    exit();
}

// If viewing another sitter (guardian view)
if (isset($_GET['id'])) {
    $uID = (int) $_GET['id'];

    $stmt = $conn->prepare("
        SELECT 
            u.*,
            s.hourlyRate,
            s.bio,
            s.experience
        FROM users u
        LEFT JOIN sitters s ON u.id = s.id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $uID);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        die("User not found.");
    }

    $user = $result->fetch_assoc();
    $stmt->close();

} else {
    // If sitter is viewing their own profile
    $uID = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT 
            u.*,
            s.hourlyRate,
            s.bio,
            s.experience
        FROM users u
        LEFT JOIN sitters s ON u.id = s.id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $uID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Derived values (needed by HIS UI)
$fullName = $user['firstName'] . ' ' . $user['lastName'];

// Age calculation
$age = 0;
if (!empty($user['birthdate'])) {
    $birthDate = new DateTime($user['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
}

// Location
$location = $user['city'] . ', ' . $user['country'];

// Profile pic fallback
$user['profilePic'] = !empty($user['profilePic']) ? $user['profilePic'] : 'default.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitter Dashboard - Pampeers</title>
    <link rel="icon" type="image/x-icon" href="/Pampeers/app/uploads/pampeerlogo.png">
    
    <!-- KEEP HIS CSS -->
    <link rel="stylesheet" href="css/sitterProfile.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ribeye&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<!-- HEADER -->
<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">

        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <div class="right-side-p d-flex align-items-center gap-1">

            <button type="button" class="btn btn-link">
                <a href="../sitterProfile.php">
                    <div class="profile-wrapper">
                        <img
                            src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic']); ?>"
                            class="profile-img-small"
                        >
                    </div>
                </a>
            </button>

            <div class="dropdown">
                <button class="btn" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item">Favourites</button></li>
                    <li><button class="dropdown-item">Messages</button></li>
                    <a class="dropdown-item" href="../sitterProfile.php">Profile</a>
                    <li>
                        <a class="dropdown-item" href="../../app/controllers/logout.php">Logout</a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</header>

<div class="row g-3 mt-0 g-2 mb-3 p-2 mx-4">

    <!-- LEFT CARD -->
    <div class="col-lg-7">
        <div class="card-profile p-4 d-flex align-items-center h-100">

            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic']) ?>" 
                 class="profile-img mb-3">

            <div class="m-5">
                <p class="mb-0"><?= htmlspecialchars($user['bio'] ?? '') ?></p>
                <h2 class="fw-bold mb-0"><?= htmlspecialchars($fullName) ?></h2>
                <p class="text-muted small"><?= htmlspecialchars($user['email']) ?></p>

                <!-- ADDED (your backend data) -->
                <div class="price-tag mt-2">
                    <span class="currency">₱</span>
                    <?= number_format($user['hourlyRate'] ?? 0, 2) ?>/hr
                </div>
            </div>

        </div>
    </div>

    <!-- RIGHT CARD -->
    <div class="col-lg-5">
        <div class="card-profile d-flex flex-column justify-content-center p-4 h-100">

            <div class="info-item divider m-3">
                <img src="/Pampeers/app/uploads/age.png"> Age: <?= $age ?>
            </div>

            <div class="info-item divider m-3">
                <img src="/Pampeers/app/uploads/location.png"> Location: <?= htmlspecialchars($location) ?>
            </div>

            <div class="info-item divider m-3">
                <img src="/Pampeers/app/uploads/exp.png">
                Experience: <?= htmlspecialchars($user['experience'] ?? 0) ?> years
            </div>

        </div>
    </div>
</div>

<!-- KEEP HIS STATIC UI BELOW (queue + reviews) -->

<div class="queue d-flex justify-content-between align-items-center mt-0 mb-3">
    <h5 class="fw-bold m-0">Work Queue</h5>
    <a href="#" class="view-all">View all ></a>
</div>

<div class="card-white p-4 shadow-sm mb-5">
    <h6 class="fw-bold mb-3">Recent Reviews</h6>
    <p class="mb-1 fst-italic">No reviews yet.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>