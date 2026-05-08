<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/config/config.php';

requireAuth();

/* ================= USER DATA ================= */
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

/* ================= FETCH BOOKINGS ================= */
require_once __DIR__ . '/../../app/controllers/booking/fetchBookings.php';

/* ================= SITTER DATA ================= */
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchAvail.php';
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchNear.php';

$sitters = $sitters ?? [];
$sittersNear = $sittersNear ?? [];
$userCity = $user['cityMunicipality'] ?? 'Cagayan de Oro';
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

        <div class="right-side-p d-flex align-items-center gap-1">
            <a href="../profile.php">
                <div class="profile-wrapper">
                    <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic'] ?? 'default.jpg'); ?>" class="profile-img" alt="Profile">
                </div>
            </a>
            <div class="dropdown">
                <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="myBookings.php">My Bookings</a></li>
                    <li><a class="dropdown-item" href="favourites.php">Favourites</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../../app/controllers/auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<main class="container-fluid mt-4 px-4">
    <?php if (isset($_GET['booking']) && $_GET['booking'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <strong>Booking Sent!</strong> Your request is now pending sitter approval.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-5">
        <div class="section-title h5 fw-bold mb-3 text-dark">My Recent Bookings</div>
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <?php if (empty($bookings)): ?>
                <div class="text-muted py-2">You haven't made any bookings yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Sitter</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                <tr>
                                    <td>
                                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($booking['profilePic'] ?? 'default.jpg') ?>" class="rounded-circle me-2" style="width:35px; height:35px; object-fit:cover;">
                                        <span class="fw-semibold"><?= htmlspecialchars($booking['displayName'] ?? 'Unknown') ?></span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($booking['bookingDate'])) ?></td>
                                    <td>
                                        <span class="badge rounded-pill <?= $booking['status'] === 'accepted' ? 'bg-success' : ($booking['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                            <?= ucfirst($booking['status'] ?? 'Pending') ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-primary">₱<?= number_format($booking['totalAmount'] ?? 0, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title">Available Babysitters</div>
        <div class="arrow-controls">
            <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', -1)"> < </button>
            <button class="arrow-btn" onclick="scrollCarousel('avail-carousel', 1)"> > </button>
        </div>
    </div>

    <div class="carousel-wrapper" id="avail-carousel">
        <?php foreach ($sitters as $peer): ?>
        <div class="carousel-card">
            <div class="small-card">
                <div class="card-img-container">
                    <button class="like-btn" data-id="<?= htmlspecialchars($peer['sitterID'] ?? '') ?>"><i class="fa-regular fa-heart"></i></button>
                    <img src="../../app/uploads/profiles/<?= htmlspecialchars($peer['img'] ?? 'default.jpg'); ?>" alt="Sitter">
                </div>
                <h6><?= htmlspecialchars($peer['name'] ?? 'Unknown Sitter') ?></h6>
                <p class="city"><?= htmlspecialchars($peer['city'] ?? 'Cagayan de Oro') ?></p>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <p class="m-0">₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>
                    <a href="bookSitter.php?sitterID=<?= htmlspecialchars($peer['sitterID'] ?? '') ?>" class="btn btn-sm btn-primary rounded-pill px-3">Book</a>   
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function scrollCarousel(carouselId, direction) {
    const container = document.getElementById(carouselId);
    container.scrollBy({ left: direction * 220, behavior: 'smooth' });
}

// 1. AJAX SEARCH LOGIC
document.querySelector('.search-btn').addEventListener('click', function(e) {
    e.preventDefault();
    
    // Get values from the search bar
    const where = document.getElementById('input-where').value.trim();
    const when = document.getElementById('input-when').value; 
    const who = document.getElementById('input-who').value.trim();
    
    // Build the query string dynamically
    const params = new URLSearchParams({
        location: where,
        date: when,
        keyword: who
    });

    // Make the request to your backend search endpoint
    fetch(`/Pampeers/app/controllers/user/search.php?${params.toString()}`)
        .then(response => response.json())
        .then(res => {
            const container = document.getElementById('avail-carousel');
            container.innerHTML = ''; // Clear current sitters

            if (res.success && res.data.length > 0) {
                // Loop through results and build new cards
                res.data.forEach(sitter => {
                    const profilePic = sitter.profilePic ? sitter.profilePic : 'default.jpg';
                    const heartClass = sitter.isFavourite ? 'fa-solid text-danger' : 'fa-regular';
                    
                    container.innerHTML += `
                        <div class="carousel-card">
                            <div class="small-card">
                                <div class="card-img-container">
                                    <button class="like-btn" data-id="${sitter.sitterID}">
                                        <i class="${heartClass} fa-heart"></i>
                                    </button>
                                    <img src="../../app/uploads/profiles/${profilePic}" alt="Sitter">
                                </div>
                                <h6>${sitter.firstName} ${sitter.lastName}</h6>
                                <p class="city">${sitter.cityMunicipality}</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <p class="m-0">₱${sitter.hourlyRate}/hr</p>
                                    <a href="bookSitter.php?sitterID=${sitter.sitterID}" class="btn btn-sm btn-primary rounded-pill px-3">Book</a>   
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = `<div class="text-muted p-5 text-center w-100">No available sitters found matching your criteria.</div>`;
            }
        })
        .catch(err => console.error('Search error:', err));
});

// 2. EVENT DELEGATION FOR LIKE BUTTONS
document.getElementById('avail-carousel').addEventListener('click', function(e) {
    const btn = e.target.closest('.like-btn');
    if (!btn) return; // If they didn't click a like button, do nothing
    
    e.preventDefault();
    const sitterId = btn.getAttribute('data-id');
    const icon = btn.querySelector('i');
    if(!sitterId) return;

    fetch('/Pampeers/app/controllers/user/toggleFavourite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `sitterId=${sitterId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'added') {
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid', 'text-danger'); 
        } else if (data.status === 'removed') {
            icon.classList.remove('fa-solid', 'text-danger');
            icon.classList.add('fa-regular'); 
        } else {
            console.error("Error toggling favourite:", data.message);
        }
    })
    .catch(err => console.error('Fetch error:', err));
});
</script>
</body>
</html>