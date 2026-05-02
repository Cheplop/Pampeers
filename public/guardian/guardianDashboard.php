<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/config/config.php';

requireAuth();

/* ================= USER ================= */
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/guestDashboard.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

/* ================= BLOCK ADMIN ================= */
if (($user['role'] ?? '') === 'admin') {
    header("Location: /Pampeers/public/admin/adminDashboard.php");
    exit();
}

$userCity = $user['cityMunicipality'] ?? 'Cagayan de Oro';

/* ================= SITTERS ================= */
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchAvail.php';
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchNear.php';

$sitters = $sitters ?? [];
$sittersNear = $sittersNear ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Dashboard</title>

    <link rel="icon" href="/Pampeers/app/uploads/pampeerlogo.png">

    <!-- MATCH GUEST EXACTLY -->
    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SAME CSS AS GUEST -->
    <link rel="stylesheet" href="/Pampeers/public/css/dashboard.css">
</head>

<body>

<!-- HEADER (MATCH GUEST) -->
<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3 gap-3">
        <!-- LEFT -->
        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <!-- CENTER SEARCH -->
        <!-- CENTER SEARCH (FIXED STRUCTURE) -->
        <div class="search-bar d-flex align-items-center justify-content-between">

            <div class="search-labels">

                <div class="field-group">
                    <label>Where</label>
                    <input type="text" placeholder="City">
                </div>

                <div class="divider"></div>

                <div class="field-group">
                    <label>When</label>
                    <input type="text" placeholder="Date">
                </div>

                <div class="divider"></div>

                <div class="field-group">
                    <label>Who</label>
                    <input type="text" placeholder="People">
                </div>

            </div>

            <button class="search-btn">
                <img src="/Pampeers/app/uploads/search.png" width="16">
            </button>

        </div>

        <!-- RIGHT -->
        <div class="right-side-p d-flex align-items-center gap-3">

            <a href="/Pampeers/public/profile.php" class="profile-wrapper">
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic'] ?? 'default.jpg') ?>"
                     class="profile-img"
                     alt="Profile">
            </a>

            <a href="/Pampeers/app/controllers/auth/logout.php" class="signup-btn">
                Logout
            </a>

        </div>

    </div>
</header>

<!-- CONTENT -->
<main class="container-fluid mt-4 px-4">

    <div class="section-title">Available Sitters</div>

    <?php if (!empty($sitters)): ?>
        <div class="carousel-wrapper">
            <?php foreach ($sitters as $s): ?>
                <div class="carousel-card">
                    <div class="small-card">

                        <div class="card-img-container">
                            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($s['profilePic'] ?? 'default.jpg') ?>">
                            <button class="like-btn">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>

                        <h6><?= htmlspecialchars($s['firstName'] ?? '') ?></h6>
                        <p class="city"><?= htmlspecialchars($s['cityMunicipality'] ?? '') ?></p>
                        <p>₱<?= htmlspecialchars($s['hourlyRate'] ?? 0) ?>/hr</p>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted ms-5">No available sitters found.</p>
    <?php endif; ?>

    <div class="section-title mt-5">
        Sitters in <?= htmlspecialchars($userCity) ?>
    </div>

    <?php if (!empty($sittersNear)): ?>
        <div class="carousel-wrapper">
            <?php foreach ($sittersNear as $s): ?>
                <div class="carousel-card">
                    <div class="small-card">

                        <div class="card-img-container">
                            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($s['profilePic'] ?? 'default.jpg') ?>">
                            <button class="like-btn">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>

                        <h6><?= htmlspecialchars($s['firstName'] ?? '') ?></h6>
                        <p class="city"><?= htmlspecialchars($s['cityMunicipality'] ?? '') ?></p>
                        <p>₱<?= htmlspecialchars($s['hourlyRate'] ?? 0) ?>/hr</p>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted ms-5">No nearby sitters.</p>
    <?php endif; ?>

</main>

<script>
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const icon = btn.querySelector('i');
        icon.classList.toggle('fa-regular');
        icon.classList.toggle('fa-solid');
        btn.classList.add('heart-pop');
        setTimeout(() => btn.classList.remove('heart-pop'), 300);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
