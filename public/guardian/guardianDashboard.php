<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure correct paths to config and auth middleware
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

requireAuth();
$userId = $_SESSION['user_id'];

/* ================= 1. FETCH GUARDIAN DATA ================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

$userCity = !empty($user['cityMunicipality']) ? $user['cityMunicipality'] : '';
$userPic = !empty($user['profilePic']) ? $user['profilePic'] : 'default.jpg';

/* ================= 2. FETCH AVAILABLE SITTERS ================= */
$sitters = [];
$queryAvail = "SELECT s.sitterID, s.hourlyRate as rate, u.firstName, u.lastName, u.profilePic as img, u.cityMunicipality as city,
              (SELECT COUNT(*) FROM favourites f WHERE f.sitter_id = s.sitterID AND f.guardian_id = ?) as isFav
              FROM sitters s 
              JOIN users u ON s.userID = u.id 
              WHERE s.verificationStatus = 'verified' AND u.isActive = 1 AND s.isAvailable = 1 AND u.id != ?
              ORDER BY s.ratingAverage DESC, s.createdAt DESC LIMIT 12";
$stmtA = $conn->prepare($queryAvail);
$stmtA->bind_param("ii", $userId, $userId);
$stmtA->execute();
$resA = $stmtA->get_result();
while ($row = $resA->fetch_assoc()) {
    $row['name'] = trim($row['firstName'] . ' ' . $row['lastName']);
    $sitters[] = $row;
}
$stmtA->close();

/* ================= 3. FETCH NEARBY SITTERS ================= */
$sittersNear = [];
$queryNear = "SELECT s.sitterID, s.hourlyRate as rate, u.firstName, u.lastName, u.profilePic as img, u.cityMunicipality as city,
              (SELECT COUNT(*) FROM favourites f WHERE f.sitter_id = s.sitterID AND f.guardian_id = ?) as isFav
              FROM sitters s 
              JOIN users u ON s.userID = u.id 
              WHERE s.verificationStatus = 'verified' AND u.isActive = 1 AND s.isAvailable = 1 AND u.cityMunicipality = ? AND u.id != ?
              ORDER BY s.ratingAverage DESC, s.createdAt DESC LIMIT 8";
$stmtN = $conn->prepare($queryNear);
$stmtN->bind_param("isi", $userId, $userCity, $userId);
$stmtN->execute();
$resN = $stmtN->get_result();
while ($row = $resN->fetch_assoc()) {
    $row['name'] = trim($row['firstName'] . ' ' . $row['lastName']);
    $sittersNear[] = $row;
}
$stmtN->close();
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

        <div class="search-bar d-flex align-items-center">

            <input
                type="text"
                id="search-input"
                placeholder="Search babysitters or locations..."
                autocomplete="off"
            >

            <button class="search-btn" id="search-button">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

        </div>

        <div class="right-side-p d-flex align-items-center gap-1">

            <button type="button" class="btn btn-link p-0 border-0">
                <a href="../profile.php">
                    <div class="profile-wrapper">
                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($userPic); ?>" class="profile-img" alt="Profile" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    </div>
                </a>
            </button>

                <div class="dropdown">
                    <button class="btn" type="button" data-bs-toggle="dropdown" data-bs-offset="0,15" =aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="../profile.php"><i class="fa-regular fa-user me-2"></i>View Profile</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="myFavourites.php"><i class="fa-regular fa-heart me-2"></i>Favourites</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="myBookings.php"><i class="fa-regular fa-calendar me-2"></i>Bookings</a>
                        </li>
                        <li class="logout">
                            <a class="dropdown-item" href="/Pampeers/app/controllers/auth/logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</header>

<main class="container-fluid mt-4 px-4">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title">Available Babysitters</div>
        <div class="arrow-controls">
            <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', -1)"> < </button>
            <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', 1)"> > </button>
        </div>
    </div>

    <?php if (!empty($sitters)): ?>
    <div class="carousel-wrapper" id="avail-carousel">
        <?php foreach ($sitters as $peer): ?>
        <div class="carousel-card">
            <div class="small-card" style="cursor: pointer;" onclick="window.location.href='viewSitterProfile.php?sitterID=<?= htmlspecialchars($peer['sitterID']) ?>'">
                <div class="card-img-container">
                    <button class="like-btn" data-id="<?= htmlspecialchars($peer['sitterID']) ?>" aria-label="Like" onclick="toggleFav(event, this)">
                        <i class="fa-<?= !empty($peer['isFav']) ? 'solid text-danger' : 'regular' ?> fa-heart"></i> 
                    </button>
                    <img src="/Pampeers/app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                </div>
                <h6><?= htmlspecialchars($peer['name'] ?? 'Unknown') ?></h6>
                <p class="city"><?= htmlspecialchars($peer['city'] ?? 'Location N/A') ?></p>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <p class="m-0 fw-bold">₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center text-muted mt-4">No available sitters found.</p>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-5 mb-2" id="nearby-header">
        <div class="section-title">Near You (<?= htmlspecialchars($userCity) ?>)</div>
        <div class="arrow-controls">
            <button class="arrow-btn" onclick="scrollCarousel('near-carousel', -1)"> < </button>
            <button class="arrow-btn" onclick="scrollCarousel('near-carousel', 1)"> > </button>
        </div>
    </div>

    <?php if (!empty($sittersNear)): ?>
    <div class="carousel-wrapper" id="near-carousel">
        <?php foreach ($sittersNear as $peer): ?>
        <div class="carousel-card">
            <div class="small-card" style="cursor: pointer;" onclick="window.location.href='viewSitterProfile.php?sitterID=<?= htmlspecialchars($peer['sitterID']) ?>'">
                <div class="card-img-container">
                    <button class="like-btn" data-id="<?= htmlspecialchars($peer['sitterID']) ?>" aria-label="Like" onclick="toggleFav(event, this)">
                        <i class="fa-<?= !empty($peer['isFav']) ? 'solid text-danger' : 'regular' ?> fa-heart"></i>
                    </button>
                    <img src="/Pampeers/app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" alt="Sitter">
                </div>
                <h6><?= htmlspecialchars($peer['name'] ?? 'Unknown') ?></h6>
                <p class="city"><?= htmlspecialchars($peer['city'] ?? 'Location N/A') ?></p>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <p class="m-0 fw-bold">₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center text-muted mt-2" id="near-empty">No sitters found in your city.</p>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function scrollCarousel(carouselId, direction) {

    const container = document.getElementById(carouselId);

    if (!container) return;

    const card = container.querySelector('.carousel-card');

    if (card) {

        const scrollAmount = card.offsetWidth + 20;

        container.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
}

/* ================= SEARCH ================= */

const searchInput = document.getElementById('search-input');
const searchButton = document.getElementById('search-button');

function performSearch() {

    const keyword = searchInput.value.trim();

    fetch(`/Pampeers/app/controllers/user/search.php?keyword=${encodeURIComponent(keyword)}`)

    .then(response => response.json())

    .then(data => {

        console.log("SEARCH RESULT:", data);

        const container = document.getElementById('avail-carousel');

        if (!container) return;

        container.innerHTML = '';

        /* TOGGLE NEARBY SECTION */
        const nearbyHeader = document.getElementById('nearby-header');
        const nearbyCarousel = document.getElementById('near-carousel');

        if (keyword === '') {

            if (nearbyHeader) nearbyHeader.style.display = 'flex';
            if (nearbyCarousel) nearbyCarousel.style.display = 'flex';

        } else {

            if (nearbyHeader) nearbyHeader.style.display = 'none';
            if (nearbyCarousel) nearbyCarousel.style.display = 'none';
        }

        /* NO RESULTS */
        if (!Array.isArray(data) || data.length === 0) {

            container.innerHTML = `
                <div class="w-100 text-center text-muted p-5">
                    No results found.
                </div>
            `;

            return;
        }

        /* DISPLAY RESULTS */
        data.forEach(sitter => {

            container.innerHTML += `
                <div class="carousel-card">

                    <div class="small-card"
                         style="cursor:pointer;"
                         onclick="window.location.href='viewSitterProfile.php?sitterID=${sitter.sitterID}'">

                        <div class="card-img-container">

                            <button class="like-btn"
                                    data-id="${sitter.sitterID}"
                                    onclick="toggleFav(event, this)">

                                <i class="fa-${parseInt(sitter.isFav) > 0 ? 'solid text-danger' : 'regular'} fa-heart"></i>

                            </button>

                            <img src="/Pampeers/app/uploads/profiles/${sitter.profilePic || 'default.jpg'}"
                                 alt="Sitter">

                        </div>

                        <h6>${sitter.firstName} ${sitter.lastName}</h6>

                        <p class="city">${sitter.cityMunicipality || 'Unknown Location'}</p>

                        <div class="d-flex justify-content-between align-items-center mt-2">

                            <p class="m-0 fw-bold">
                                ₱${sitter.hourlyRate}/hr
                            </p>

                        </div>

                    </div>

                </div>
            `;
        });
    })

    .catch(error => {

        console.error("SEARCH ERROR:", error);
    });
}

/* SEARCH BUTTON */
searchButton.addEventListener('click', function(e) {

    e.preventDefault();

    performSearch();
});

/* ENTER KEY SEARCH */
searchInput.addEventListener('keydown', function(e) {

    if (e.key === 'Enter') {

        e.preventDefault();

        performSearch();
    }
});

/* ================= FAVOURITES ================= */

function toggleFav(e, btn) {

    e.preventDefault();

    e.stopPropagation();

    const sitterId = btn.getAttribute('data-id');

    const icon = btn.querySelector('i');

    if (!sitterId) return;

    fetch('/Pampeers/app/controllers/user/toggleFavourite.php', {

        method: 'POST',

        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },

        body: `sitterId=${sitterId}`
    })

    .then(response => response.json())

    .then(data => {

        btn.classList.add('heart-pop');

        setTimeout(() => {

            btn.classList.remove('heart-pop');

        }, 300);

        if (data.status === 'added') {

            icon.classList.remove('fa-regular');

            icon.classList.add('fa-solid', 'text-danger');

        }

        else if (data.status === 'removed') {

            icon.classList.remove('fa-solid', 'text-danger');

            icon.classList.add('fa-regular');
        }
    })

    .catch(error => {

        console.error("Favourite Error:", error);
    });
}
</script>

</body>
</html>