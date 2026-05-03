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

/* ✅ FIXED: use id instead of uID */
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

/* ================= LOCATION ================= */
$userCity = $user['cityMunicipality'] ?? '';

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

        <!-- Brand Logo -->
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

        <!-- Right Side: Profile + Menu -->
        <div class="right-side-p d-flex align-items-center gap-1">

            <!-- Profile Picture Link -->
            <button type="button" class="btn btn-link">
                <a href="../guardianProfile.php">
                    <?php $userPic = !empty($user['profilePic']) ? $user['profilePic'] : 'default.jpg'; ?>
                    <div class="profile-wrapper">
                        <img
                            src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($userPic); ?>"
                            class="profile-img"
                            alt="Profile"
                        >
                    </div>
                </a>
            </button>

            <!-- Hamburger Dropdown Menu -->
            <div class="dropdown">
                <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" type="button">Favourites</button></li>
                    <li><button class="dropdown-item" type="button">Messages</button></li>
                    <a class="dropdown-item" href="../guardianProfile.php">
                        Profile
                    </a>
                    </li>
                    <li>
                    <a class="login" href="../../app/controllers/auth/logout.php">
                        Logout
                    </a>
                    </li>
                </ul>
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