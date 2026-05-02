<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/helpers/sitter.php';

requireAuth();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/login.php");
    exit();
}

/* ================= BLOCK NON-SITTERS ================= */
if (!isSitter($conn, $userId)) {
    header("Location: /Pampeers/public/profile.php?error=not_sitter");
    exit();
}

/* ================= BLOCK UNVERIFIED ================= */
if (!isVerifiedSitter($conn, $userId)) {
    header("Location: /Pampeers/public/profile.php?error=not_verified");
    exit();
}

/* ================= GET USER ================= */
$stmt = $conn->prepare("
    SELECT firstName, lastName, profilePic 
    FROM users 
    WHERE id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

$userPic = $user['profilePic'] ?? 'default.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pampeers - Sitter Dashboard</title>

<link rel="icon" href="/Pampeers/app/uploads/pampeerlogo.png">

<!-- SAME AS GUEST -->
<link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/Pampeers/public/css/dashboard.css">
</head>

<body>

<!-- ================= HEADER (MATCH GUEST) ================= -->
<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">

        <!-- LEFT -->
        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <!-- CENTER SEARCH -->
        <div class="search-bar d-flex align-items-center justify-content-between">
            <div class="search-labels d-flex align-items-center gap-3 flex-grow-1">
                <span>Where</span>
                <div class="divider"></div>
                <span>When</span>
                <div class="divider"></div>
                <span>Who</span>
            </div>
            <button class="search-btn">
                <img src="/Pampeers/app/uploads/search.png" width="16">
            </button>
        </div>

        <!-- RIGHT -->
        <div class="right-side-p d-flex align-items-center gap-3">

            <a href="/Pampeers/public/profile.php" class="profile-wrapper">
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($userPic) ?>"
                     class="profile-img">
            </a>

            <a href="/Pampeers/app/controllers/auth/logout.php" class="signup-btn">
                Logout
            </a>

        </div>

    </div>
</header>

<!-- ================= CONTENT ================= -->
<main class="container-fluid mt-4 px-4">

    <div class="section-title">Sitter Dashboard</div>

    <div class="row justify-content-center">

        <div class="col-lg-6">

            <div class="small-card p-4 text-center">

                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($userPic) ?>"
                     style="width:100px;height:100px;border-radius:50%;object-fit:cover;">

                <h4 class="mt-3">
                    <?= htmlspecialchars(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?>
                </h4>

                <p class="text-muted">You are a verified sitter</p>

                <div class="d-flex justify-content-center gap-2 mt-3">

                    <a href="/Pampeers/public/editSitterProfile.php"
                       class="btn btn-warning btn-sm">
                        Edit Sitter Profile
                    </a>

                    <a href="/Pampeers/public/profile.php"
                       class="btn btn-secondary btn-sm">
                        View Profile
                    </a>

                    <form method="POST" action="/Pampeers/app/controllers/sitter/toggleAvailability.php">
                        <button class="btn btn-primary btn-sm">
                            Toggle Availability
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>