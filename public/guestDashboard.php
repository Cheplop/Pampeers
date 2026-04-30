<?php
session_start();

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

<main class="container-fluid mt-4 px-4">
    
    <div class="section-title">Available Sitters</div>
    <?php if (!empty($sitters)): ?>
    <div class="carousel-wrapper">
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
                <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center text-muted">No available sitters found.</p>
    <?php endif; ?>

    <div class="section-title mt-5">Peers in <?= htmlspecialchars($userCity) ?></div>
    <?php if (!empty($sittersNear)): ?>
    <div class="carousel-wrapper">
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
                <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const icon = this.querySelector('i');
        
        // Toggle Heart state
        icon.classList.toggle('fa-regular');
        icon.classList.toggle('fa-solid');
        
        // Trigger Pop Animation
        this.classList.add('heart-pop');
        setTimeout(() => {
            this.classList.remove('heart-pop');
        }, 300);
    });
});
</script>

</body>
</html>