<?php
require_once __DIR__ . '/../../app/middleware/authCheck.php';
require_once __DIR__ . '/../../app/controllers/guardian/guardianFetchData.php';
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchAvail.php';

// Fetch sitters near the guardian
$userCity = $user['city'] ?? '';
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchNear.php';

// Prevent undefined errors
$sitters = $sitters ?? [];
$sittersNear = $sittersNear ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampeers - Guardian Dashboard</title>

    <link rel="icon" type="image/png" href="/Pampeers/app/uploads/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <div class="search-bar d-flex align-items-center justify-content-between">
            <div class="search-labels d-flex align-items-center gap-3 flex-grow-1">
                <span>Where</span>
                <div class="divider"></div>
                <span>When</span>
                <div class="divider"></div>
                <span>Who</span>
            </div>
            <button class="search-btn">
                <img src="/Pampeers/app/uploads/search.png" alt="search" width="16">
            </button>
        </div>

        <div class="right-side-p d-flex align-items-center gap-3">
             <a href="../../app/controllers/logout.php" class="logout-btn">Logout</a>

            <?php $userPic = !empty($user['profilePic']) ? $user['profilePic'] : 'default.jpg'; ?>
            <div class="profile-wrapper">
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($userPic); ?>" class="profile-img" alt="Profile">
            </div>
        </div>
    </div>
</header>

<main class="container-fluid mt-2 px-4">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title">Available Babysitters</div>
        <div class="arrow-controls">
            <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', -2)"> < </button>
            <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', 2)"> > </button>
        </div>
    </div>

    <?php if (!empty($sitters)): ?>
    <div class="carousel-wrapper" id="avail-carousel">
        <?php foreach ($sitters as $peer): ?>
        <div class="carousel-card">
            <div class="small-card">
                <div class="card-img-container">
                    <button class="like-btn" aria-label="Like">
                        <i class="fa-regular fa-heart"></i> 
                    </button>
                    <img src="../../app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                </div>
                <h6><?= htmlspecialchars($peer['name'] ?? 'Unknown') ?></h6>
                <p class="city"><?= htmlspecialchars($peer['city'] ?? 'Location N/A') ?></p>
                <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center text-muted mt-4">No available sitters found.</p>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
        <div class="section-title">Near You</div>
        <div class="arrow-controls">
            <button class="arrow-btn" onclick="scrollCarousel('near-carousel', -1)"> < </button>
            <button class="arrow-btn" onclick="scrollCarousel('near-carousel', 1)"> > </button>
        </div>
    </div>

    <?php if (!empty($sittersNear)): ?>
    <div class="carousel-wrapper" id="near-carousel">
        <?php foreach ($sittersNear as $peer): ?>
        <div class="carousel-card">
            <div class="small-card">
                <div class="card-img-container">
                    <button class="like-btn" aria-label="Like">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                    <img src="../../app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                </div>
                <h6><?= htmlspecialchars($peer['name'] ?? 'Unknown') ?></h6>
                <p class="city"><?= htmlspecialchars($peer['city'] ?? 'Location N/A') ?></p>
                <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center text-muted mt-2">No sitters found in your city.</p>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Scroll Function for Arrows
function scrollCarousel(carouselId, direction) {
    const container = document.getElementById(carouselId);
    const card = container.querySelector('.carousel-card');
    if (card) {
        // Scroll by 1 card width + 20px gap
        const scrollAmount = card.offsetWidth + 20;
        container.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
}

// Existing Like Button Logic
document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const icon = this.querySelector('i');
        
        icon.classList.toggle('fa-regular');
        icon.classList.toggle('fa-solid');
        
        this.classList.add('heart-pop');
        setTimeout(() => {
            this.classList.remove('heart-pop');
        }, 300);
    });
});
</script>

</body>
</html>