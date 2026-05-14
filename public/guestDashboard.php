<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/config.php';

// FIXED: Database Logic for Guests
$sitters = [];
$sittersNear = [];
$userCity = "Cagayan De Oro"; // Default for guest view

try {
    // 1. Fetch Available Sitters
    $query = "SELECT s.sitterID, s.hourlyRate as rate, u.firstName, u.lastName, u.profilePic as img, u.cityMunicipality as city 
              FROM sitters s 
              JOIN users u ON s.userID = u.id 
              WHERE s.verificationStatus = 'verified' AND u.isActive = 1 AND s.isAvailable = 1
              LIMIT 12";
    $res = $conn->query($query);
    while ($row = $res->fetch_assoc()) {
        $row['name'] = trim($row['firstName'] . ' ' . $row['lastName']);
        $sitters[] = $row;
    }

    // 2. Fetch Near Sitters
    $nearStmt = $conn->prepare("SELECT s.sitterID, s.hourlyRate as rate, u.firstName, u.lastName, u.profilePic as img, u.cityMunicipality as city 
                                FROM sitters s 
                                JOIN users u ON s.userID = u.id 
                                WHERE s.verificationStatus = 'verified' AND u.isActive = 1 AND u.cityMunicipality = ?
                                LIMIT 8");
    $nearStmt->bind_param("s", $userCity);
    $nearStmt->execute();
    $resNear = $nearStmt->get_result();
    while ($row = $resNear->fetch_assoc()) {
        $row['name'] = trim($row['firstName'] . ' ' . $row['lastName']);
        $sittersNear[] = $row;
    }
} catch (Exception $e) {
    // Silent fail to keep page loading
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampeers - Guest Dashboard</title>

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
                <div class="field-group">
                    <label for="input-where">Where</label>
                    <input type="text" id="input-where" placeholder="City or area" autocomplete="off" />
                </div>
                <div class="divider"></div>
                <div class="field-group">
                    <label for="input-when">When</label>
                    <input type="date" id="input-when" />
                </div>
                <div class="divider"></div>
                <div class="field-group">
                    <label for="input-who">Who</label>
                    <select id="input-who" class="form-control border-0 bg-transparent p-0 shadow-none text-muted">
                        <option value="">Any Age</option>
                        <option value="Baby">Baby (0-1 yrs)</option>
                        <option value="Toddler">Toddler (1-3 yrs)</option>
                        <option value="Child">Child (4-8 yrs)</option>
                        <option value="Kid">Kid (9+ yrs)</option>
                    </select>
                </div>
            </div>
            <button class="search-btn" id="search-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
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
                    <form action="../app/controllers/auth/login.php" method="POST">
                        <div class="mb-2">
                            <label class="form-label">Username</label>
                            <div class="input-container">
                                <span class="input-icon"><i class="fa-regular fa-user"></i></span>
                                <input type="text" placeholder="Username or Email" name="login" class="custom-input" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <div class="input-container">
                                <span class="input-icon"><i class="fa-solid fa-key"></i></span>
                                <input type="password" placeholder="Enter your password" name="password" id="passwordInput" class="custom-input" required>
                                <span class="input-icon toggle-password" style="cursor: pointer;" onclick="togglePasswordVisibility()">
                                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>
                        <button type="submit" class="btn-login">Log In</button>
                        <div class="text-center mt-3">
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

    <div class="carousel-wrapper" id="avail-carousel">
        <?php if (!empty($sitters)): ?>
            <?php foreach ($sitters as $peer): ?>
            <div class="carousel-card">
                <div class="small-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                    <div class="card-img-container">
                        <button class="like-btn" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                        <img src="../app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                    </div>
                    <h6><?= htmlspecialchars($peer['name']) ?></h6>
                    <p class="city"><?= htmlspecialchars($peer['city']) ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <p class="m-0 fw-bold">₱<?= htmlspecialchars($peer['rate']) ?>/hr</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted w-100">No available sitters found.</p>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 mb-2" id="nearby-header">
        <div class="section-title">Peers in <?= htmlspecialchars($userCity) ?></div>
        <div class="arrow-controls">
            <button class="arrow-btn" onclick="scrollCarousel('near-carousel', -1)"> < </button>
            <button class="arrow-btn" onclick="scrollCarousel('near-carousel', 1)"> > </button>
        </div>
    </div>

    <div class="carousel-wrapper" id="near-carousel">
        <?php if (!empty($sittersNear)): ?>
            <?php foreach ($sittersNear as $peer): ?>
                <div class="carousel-card">
                    <div class="small-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                        <div class="card-img-container">
                            <button class="like-btn" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                            <img src="../app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                        </div>
                        <h6><?= htmlspecialchars($peer['name']) ?></h6>
                        <p class="city"><?= htmlspecialchars($peer['city']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <p class="m-0 fw-bold">₱<?= htmlspecialchars($peer['rate']) ?>/hr</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted w-100" id="near-empty">No sitters found in this city.</p>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function scrollCarousel(carouselId, direction) {
    const container = document.getElementById(carouselId);
    const card = container.querySelector('.carousel-card');
    if (card) {
        const scrollAmount = card.offsetWidth + 20;
        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }
}

document.getElementById('search-button').addEventListener('click', function(e) {
    e.preventDefault();
    const where = document.getElementById('input-where').value.trim();
    const when = document.getElementById('input-when').value; 
    const who = document.getElementById('input-who').value.trim();
    const params = new URLSearchParams({ location: where, date: when, keyword: who });

    fetch(`/Pampeers/app/controllers/user/search.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('avail-carousel');
            container.innerHTML = ''; 
            
            // Toggle visibility of the "Nearby" section on search results
            document.getElementById('nearby-header').style.display = 'none';
            document.getElementById('near-carousel').style.display = 'none';

            if (data.length > 0) {
                data.forEach(sitter => {
                    container.innerHTML += `
                        <div class="carousel-card">
                            <div class="small-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                <div class="card-img-container">
                                    <button class="like-btn" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="fa-regular fa-heart"></i></button>
                                    <img src="../app/uploads/profiles/${sitter.profilePic || 'default.jpg'}" alt="Sitter">
                                </div>
                                <h6>${sitter.firstName} ${sitter.lastName}</h6>
                                <p class="city">${sitter.cityMunicipality}</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <p class="m-0 fw-bold">₱${sitter.hourlyRate}/hr</p>
                                    <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Book</button>
                                </div>
                            </div>
                        </div>`;
                });
            } else {
                container.innerHTML = '<div class="text-muted p-5 text-center w-100">No results found.</div>';
            }
        });
});

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>