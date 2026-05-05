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
                    <input type="text" id="input-where" placeholder="City or area" />
                </div>
                <div class="divider"></div>
                <div class="field-group">
                    <label for="input-when">When</label>
                    <input type="date" id="input-when" />
                </div>
                <div class="divider"></div>
                <div class="field-group">
                    <label for="input-who">Who</label>
                    <input type="text" id="input-who" placeholder="e.g. newborn" />
                </div>
            </div>
            <button class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>

        <div class="right-side-p d-flex align-items-center gap-1">
            <a href="../guardianProfile.php">
                <div class="profile-wrapper">
                    <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic'] ?? 'default.jpg'); ?>" class="profile-img" alt="Profile">
                </div>
            </a>
            <!-- Hamburger Dropdown Menu -->
            <div class="dropdown">
                <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../guardianProfile.php">Profile</a></li>
                    <!-- Added My Bookings Link -->
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
    <!-- Success/Error Alerts -->
    <?php if (isset($_GET['booking']) && $_GET['booking'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <strong>Booking Sent!</strong> Your request is now pending sitter approval.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Section: My Recent Bookings -->
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

    <!-- Section: Available Babysitters -->
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
                    <!-- Make sure 'sitterID' matches the column name in your database result -->
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

document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const sitterId = this.getAttribute('data-id');
        const icon = this.querySelector('i');
        if(!sitterId) return;

        fetch('../../app/controllers/user/toggleFavourite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `sitterID=${sitterId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                icon.classList.toggle('fa-regular');
                icon.classList.toggle('fa-solid');
            }
        });
    });
});
</script>
</body>
</html>