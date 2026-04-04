<?php
require_once __DIR__ . '/../../app/middleware/authCheck.php';
require_once __DIR__ . '/../../app/controllers/guardian/guardianFetchData.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Available Sitters</title>

<link href="https://fonts.googleapis.com/css2?family=Ribeye&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/guardianDashboard.css">

</head>

<body>
<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-4">

        <!-- Brand -->
        <div class="d-flex justify-content-center align-items-center gap-2">
            <img src="../../app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class ="brand m-0">Pampeers</p>
        </div>

        <!-- Search Bar -->
        <div class="search-bar d-flex justify-content-center align-items-center gap-5">
            <span>Where</span>
            <div class="divider"></div>
            <span>When</span>
            <div class="divider"></div>
            <span>Who</span>
            <button class="search-btn">🔍</button>
        </div>

        <!-- Right Side -->
        <div class="d-flex align-items-center gap-3">
            <a href="../../app/controllers/logout.php" class="logout-btn">
                Logout
            </a>
            <?php $userPic = !empty($user['profilePic']) ? $user['profilePic'] : 'default.jpg'; ?>
            <img src="../../app/uploads/profiles/<?php echo htmlspecialchars($userPic); ?>" class="profile-img" alt="Profile Picture">
        </div>
    </div>
</header>

<!-- AVAILABLE SITTERS -->
<div class="container-fluid mt-4">
    <div class="section-title text-start">Available Babysitters</div>

    <?php if (!empty($sitters)): ?>
    <div class="carousel-wrapper ">
        <?php foreach ($sitters as $peer): ?>
        <div class="carousel-card">
            <div class="small-card">

                <!-- IMAGE (with fallback) -->
                <img src="/pampeers/app/uploads/profiles/<?php echo !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter Profile Picture">

                <h6><?php echo htmlspecialchars($peer['name']); ?></h6>

                <p class="city">
                    <?= htmlspecialchars($peer['city']) ?>
                </p>
                
                <p>₱<?= htmlspecialchars($peer['rate']) ?>/hr</p>

                <button class="btn">
                    GET IN TOUCH
                </button>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center mt-4">No available sitters found.</p>
    <?php endif; ?>

</div>

<div class="container-fluid mt-4">
    <div class="section-title text-start">Near You</div>

    <?php if (!empty($sittersNear)): ?>
    <div class="carousel-wrapper">
        <?php foreach ($sittersNear as $peer): ?>
        <div class="carousel-card">
            <div class="small-card">

                <!-- IMAGE (with fallback) -->
                <img src="../../app/uploads/profiles/<?php echo !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter Profile Picture">

                <h6><?php echo htmlspecialchars($peer['name']); ?></h6>

                <p class="city">
                    <?= htmlspecialchars($peer['city']) ?>
                </p>
                
                <p>₱<?= htmlspecialchars($peer['rate']) ?>/hr</p>

                <button class="btn">
                    GET IN TOUCH
                </button>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center mt-4">No available sitters found.</p>
    <?php endif; ?>

</div>

</body>
</html>