<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];

// Fetch all bookings made by this Guardian
$query = "
    SELECT 
        b.*, 
        u.firstName AS sitterFirstName, 
        u.lastName AS sitterLastName, 
        u.profilePic AS sitterPic,
        s.hourlyRate,
        r.reviewID
    FROM bookings b
    JOIN sitters s ON b.sitterID = s.sitterID
    JOIN users u ON s.userID = u.id
    LEFT JOIN reviews r ON b.bookingID = r.bookingID
    WHERE b.userID = ?
    ORDER BY b.createdAt DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$myBookings = $result->fetch_all(MYSQLI_ASSOC);

// FIXED: Removed the 'as img' and 'as city' aliases so the array keys match what your HTML expects
$query = "SELECT s.*, u.firstName, u.lastName, u.profilePic, u.cityMunicipality 
          FROM favourites f 
          JOIN sitters s ON f.sitter_id = s.sitterID 
          JOIN users u ON s.userID = u.id 
          WHERE f.guardian_id = ? 
          AND s.isAvailable = 1 
          AND u.isActive = 1 
          AND s.verificationStatus = 'verified'";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uID);
$stmt->execute();
$favSitters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ================= FETCH USER PHOTO ONLY ================= */
$stmt = $conn->prepare("SELECT profilePic FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fallback to default if no image exists in database
$profilePic = (!empty($user['profilePic'])) ? $user['profilePic'] : 'default.jpg';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - Pampeers</title>
    <link rel="icon" type="image/png" href="/Pampeers/app/uploads/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/myBookings.css">

</head>
<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">

        <!-- Changed d-none d-md-flex to d-none d-lg-flex so it disappears on tablets/phones -->
        <!-- If you want it to disappear only on very small phones, use d-none d-sm-flex -->
        <div class="d-none d-lg-flex align-items-center gap-2">
            <a href="/Pampeers/public/guardian/guardianDashboard.php">
                <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img" alt="Pampeers Logo" >
            </a>
            <p class="brand m-0"><a href="/Pampeers/public/guardian/guardianDashboard.php">Pampeers</a></p>
        </div>

        <!-- Added ms-auto to ensure this stays on the right when the logo is gone -->
        <div class="right-side-p d-flex align-items-center justify-content-end gap-3 ms-auto">
        
            
            <div class="nav-btn d-flex align-items-center gap-2">
                <a href="../profile.php" class="text-decoration-none">
                    <div class="profile-wrapper">
                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic); ?>" class="profile-img" alt="Profile">
                    </div>
                </a>

                <div class="dropdown">
                    <button class="btn-dropdown border-1" type="button" data-bs-toggle="dropdown" data-bs-offset="0,15" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <!-- Added dropdown-menu-end to keep the menu within screen bounds on mobile -->
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
                            <a class="dropdown-item" href="../../app/controllers/auth/logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</header>

<div class="container py-5">
    <div class="d-flex justify-content-start align-items-center mb-4">
        <a href="guardianDashboard.php" class="btn btn-light rounded-circle me-3 shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="label">My Bookings</h1>
    </div>

    <?php if (empty($myBookings)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <h5 class="text-muted">You haven't made any bookings yet.</h5>
            <a href="guardianDashboard.php" class="btn btn-primary rounded-pill px-4 mt-3">Find a Sitter</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($myBookings as $b): 
                
                // Format the new datetime columns
                $start = new DateTime($b['startDateTime']);
                $end = new DateTime($b['endDateTime']);
                
                $dateDisplay = $start->format('M j, Y');
                $timeDisplay = $start->format('g:i A') . ' - ' . $end->format('g:i A');
                
                // Duration and total fallback
                $hours = $b['hoursRequested'] ?? round(($end->getTimestamp() - $start->getTimestamp()) / 3600, 2);
                $total = $b['totalAmount'] ?? ($hours * $b['hourlyRate']);
            ?>
                <div class="col">
                    <div class="booking-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($b['sitterPic'] ?: 'default.jpg') ?>" class="sitter-img" alt="Sitter">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($b['sitterFirstName'] . ' ' . $b['sitterLastName']) ?></h6>
                                    <small class="text-muted">₱<?= number_format($b['hourlyRate'], 2) ?>/hr</small>
                                </div>
                            </div>
                            <span class="status-badge status-<?= htmlspecialchars($b['status']) ?>">
                                <?= ucfirst(htmlspecialchars($b['status'])) ?>
                            </span>
                        </div>

                        <div class="details mb-3 flex-grow-1">
                            <p class="mb-1"><i class="fa-regular fa-calendar me-2"></i> <strong>Date:</strong> <?= $dateDisplay ?></p>
                            <p class="mb-1"><i class="fa-regular fa-clock me-2"></i> <strong>Time:</strong> <?= $timeDisplay ?></p>
                            <p class="mb-1"><i class="fa-solid fa-hourglass-half me-2"></i> <strong>Duration:</strong> <?= $hours ?> hrs</p>
                            <?php if (!empty($b['notes'])): ?>
                                <p class="mb-0 mt-2 small text-muted"><strong>Notes:</strong> <?= htmlspecialchars($b['notes']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="border-top pt-3 mt-auto d-flex justify-content-between align-items-center">
                            <h5 class="m-0 fw-bold text-success">₱<?= number_format($total, 2) ?></h5>
                            
                            <div>
                                <?php if ($b['status'] === 'pending' || $b['status'] === 'accepted'): ?>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold me-2" 
                                            onclick="cancelBooking(<?= $b['bookingID'] ?>)">
                                        Cancel
                                    </button>
                                <?php endif; ?>

                                <?php if ($b['status'] === 'completed' && empty($b['reviewID'])): ?>
                                    <button class="btn btn-sm btn-warning rounded-pill px-3 fw-bold text-dark" 
                                            onclick="openReviewModal(<?= $b['bookingID'] ?>)">
                                        Leave a Review
                                    </button>
                                <?php elseif ($b['status'] === 'completed' && !empty($b['reviewID'])): ?>
                                    <span class="badge bg-light text-dark border"><i class="fa-solid fa-check text-success me-1"></i> Reviewed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Rate your experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <p class="text-muted small">How was your babysitter?</p>
                <div class="star-rating mb-3" id="starRating">
                    <i class="fa-solid fa-star" data-value="1"></i>
                    <i class="fa-solid fa-star" data-value="2"></i>
                    <i class="fa-solid fa-star" data-value="3"></i>
                    <i class="fa-solid fa-star" data-value="4"></i>
                    <i class="fa-solid fa-star" data-value="5"></i>
                </div>
                <input type="hidden" id="selectedRating" value="0">
                <input type="hidden" id="modalBookingID" value="">
                <textarea class="form-control rounded-3 bg-light border-0" id="reviewComment" rows="3" placeholder="Write a brief review (optional)..."></textarea>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" onclick="submitReview()">Submit Review</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));

    function openReviewModal(bookingID) {
        document.getElementById('modalBookingID').value = bookingID;
        document.querySelectorAll('#starRating i').forEach(s => s.classList.remove('active'));
        document.getElementById('selectedRating').value = 0;
        document.getElementById('reviewComment').value = '';
        reviewModal.show();
    }

    // Star Selection Logic
    document.querySelectorAll('#starRating i').forEach(star => {
        star.addEventListener('click', function() {
            const val = this.getAttribute('data-value');
            document.getElementById('selectedRating').value = val;
            
            const stars = Array.from(document.querySelectorAll('#starRating i'));
            stars.forEach((s, index) => {
                if (index < val) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });

    function submitReview() {
        const bookingID = document.getElementById('modalBookingID').value;
        const rating = document.getElementById('selectedRating').value;
        const comment = document.getElementById('reviewComment').value;

        if (rating == 0) return alert("Please select a star rating!");

        fetch('../../app/controllers/review/submitReview.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `bookingID=${bookingID}&rating=${rating}&comment=${encodeURIComponent(comment)}`
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') location.reload();
        });
    }

    // NEW: Cancellation Logic
    function cancelBooking(bookingID) {
        if(confirm("Are you sure you want to cancel this booking?")) {
            // Adjust this URL if your cancel controller is named differently!
            window.location.href = `../../app/controllers/booking/cancelBooking.php?bookingID=${bookingID}`;
        }
    }
</script>

</body>
</html>