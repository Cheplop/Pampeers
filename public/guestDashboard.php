<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB connection (IMPORTANT — keep this)
require_once __DIR__ . '/../app/config/config.php';

// Fetch available sitters
require_once __DIR__ . '/../app/controllers/sitter/sitterFetchAvail.php';

// Fetch sitters near the guardian
$userCity = isset($user['city']) ? $user['city'] : 'Cagayan De Oro';
require_once __DIR__ . '/../app/controllers/sitter/sitterFetchNear.php';

// prevent undefined errors
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

    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

                <!-- Search Bar -->
        <!-- YOUR ORIGINAL .search-bar div — class names preserved -->
        <div class="search-bar d-flex align-items-center justify-content-between">
        
            <!-- YOUR ORIGINAL .search-labels div — class names preserved -->
            <div class="search-labels d-flex align-items-center gap-3 flex-grow-1">
        
            <!-- FIELD 1: Where (was a plain <span>) -->
            <div class="field-group">
                <label for="input-where">Where</label>
                <!-- type="text" lets the user type any city name -->
                <input
                type="text"
                id="input-where"
                placeholder="City or area"
                autocomplete="off"
                />
            </div>
        
            <!-- YOUR ORIGINAL divider -->
            <div class="divider"></div>
        
            <!-- FIELD 2: When (was a plain <span>) -->
            <div class="field-group">
                <label for="input-when">When</label>
                <!-- type="date" gives a built-in calendar picker -->
                <input
                type="date"
                id="input-when"
                />
            </div>
        
            <!-- YOUR ORIGINAL divider -->
            <div class="divider"></div>
        
            <!-- FIELD 3: Who / Service type (was a plain <span>) -->
            <div class="field-group">
                <label for="input-who">Who</label>
                <input
                type="text"
                id="input-who"
                placeholder="e.g. newborn, toddler"
                autocomplete="off"
                />
            </div>
        
            </div><!-- end .search-labels -->
        
            <!-- YOUR ORIGINAL search button -->
            <button class="search-btn" id="search-button" aria-label="Search">
            <!-- Using a simple SVG so no broken image if path is wrong -->
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                viewBox="0 0 24 24" fill="none" stroke="black"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            </button>
        
        </div><!-- end .search-bar -->

        <div class="right-side-p d-flex align-items-center gap-3">
            <a href="/Pampeers/public/register.php" class="signup-btn">Sign up</a>
            <button type="button" class="login-btn" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Login</button>
        </div>
    </div>
</header>

<div class="modal fade" id="staticBackdrop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-login-modal">            
            <div class="modal-body p-5">
                <div class="login-panel">
                    <h2 class="login-title">LOGIN</h2>
                    <form action="../app/middleware/loginLogic.php" method="POST">
                        
                        <div class="mb-2">
                            <label class="form-label">Username</label>
                            <div class="input-container ">
                                <span class="input-icon"><i class="fa-regular fa-user"></i></span>
                                <input type="text" placeholder="juandelacruz@gmail.com" name="email" class="custom-input" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <div class="input-container">
                                <span class="input-icon"><i class="fa-solid fa-key"></i></span>
                                <input type="password" placeholder="Enter your password" name="password" class="custom-input" required>
                            </div>
                        </div>

                        <div class="form-label mb-2">
                            <a href="#" class="sub-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn-login">Log In</button>
                        
                        <div class="text-center mt-3 mb-2">
                            <span class="footer-text">New User? <a href="/Pampeers/public/register.php" class="signup-link">Sign up now</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<main class="container-fluid mt-2 px-4">
    
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="section-title">Available Sitters</div>
    <div class="arrow-controls">
        <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', -1)"> < </button>
        <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', 1)"> > </button>
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
                        <img src="../app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                    </div>
                    <h6><?= htmlspecialchars($peer['name'] ?? 'Sitter') ?></h6>
                    <p class="city"><?= htmlspecialchars($peer['city'] ?? '') ?></p>
                    <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-muted">No available sitters found.</p>
    <?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-2 mb-2">
    <div class="section-title">Peers in <?= htmlspecialchars($userCity) ?></div>
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
                        <img src="../app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                    </div>
                    <h6><?= htmlspecialchars($peer['name'] ?? 'Sitter') ?></h6>
                    <p class="city"><?= htmlspecialchars($peer['city'] ?? '') ?></p>
                    <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
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
</script>

</body>
</html>