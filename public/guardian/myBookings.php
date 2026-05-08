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
        r.reviewID -- ADDED THIS
    FROM bookings b
    JOIN sitters s ON b.sitterID = s.sitterID
    JOIN users u ON s.userID = u.id
    LEFT JOIN reviews r ON b.bookingID = r.bookingID -- ADDED THIS
    WHERE b.userID = ?
    ORDER BY b.createdAt DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$myBookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - Pampeers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .star-rating { font-size: 1.5rem; color: #ddd; cursor: pointer; }
        .star-rating .fa-star.active { color: #ffc107; }
    </style>
</head>
<body class="bg-light">

<header class="sticky-top custom-header bg-white shadow-sm p-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="guardianDashboard.php" class="text-decoration-none text-dark">
            <i class="fa-solid fa-arrow-left me-2"></i>
            <span class="fw-bold">My Bookings</span>
        </a>
    </div>
</header>

<main class="container">
    <?php if (empty($myBookings)): ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3"></i>
            <p class="text-muted">You haven't made any bookings yet.</p>
            <a href="guardianDashboard.php" class="btn btn-primary rounded-pill">Find a Sitter</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($myBookings as $b): ?>
                <div class="col-12 mb-3">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-2 bg-light d-flex align-items-center justify-content-center p-3">
                                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($b['sitterPic'] ?: 'default.jpg') ?>" 
                                     class="rounded-circle shadow-sm" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                            <div class="col-md-7 p-3">
                                <h5 class="fw-bold mb-1">Sitter: <?= htmlspecialchars($b['sitterFirstName'] . ' ' . $b['sitterLastName']) ?></h5>
                                <p class="text-muted small mb-2">
                                    <i class="fa-regular fa-calendar me-1"></i> <?= date('M d, Y', strtotime($b['bookingDate'])) ?> 
                                    <i class="fa-regular fa-clock ms-3 me-1"></i> <?= date('h:i A', strtotime($b['startTime'])) ?> - <?= date('h:i A', strtotime($b['endTime'])) ?>
                                </p>
                                <div class="d-flex gap-3 mt-2">
                                    <span class="small text-muted">Total: <strong>₱<?= number_format($b['totalAmount'], 2) ?></strong></span>
                                    <span class="small text-muted">Status: 
                                        <strong class="text-capitalize <?php 
                                            echo in_array($b['status'], ['accepted', 'completed']) ? 'text-success' : ($b['status'] === 'pending' ? 'text-warning' : 'text-danger'); 
                                        ?>"><?= $b['status'] ?></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 p-3 d-flex flex-column justify-content-center border-start bg-white text-center">
                                <?php if ($b['status'] === 'pending'): ?>
                                    <button class="btn btn-outline-danger btn-sm rounded-pill">Cancel Request</button>
                                <?php elseif ($b['status'] === 'completed'): ?>
                                    
                                    <?php if (empty($b['reviewID'])): ?>
                                        <button class="btn btn-primary btn-sm rounded-pill" 
                                                onclick="openReviewModal(<?= $b['bookingID'] ?>, '<?= htmlspecialchars($b['sitterFirstName'] . ' ' . $b['sitterLastName']) ?>')">
                                            Leave a Review
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill" disabled>
                                            Reviewed <i class="fa-solid fa-check"></i>
                                        </button>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <button class="btn btn-light btn-sm rounded-pill" disabled>No Actions</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Rate <span id="modalSitterName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="star-rating mb-3" id="starRating">
                    <i class="fa-solid fa-star" data-value="1"></i>
                    <i class="fa-solid fa-star" data-value="2"></i>
                    <i class="fa-solid fa-star" data-value="3"></i>
                    <i class="fa-solid fa-star" data-value="4"></i>
                    <i class="fa-solid fa-star" data-value="5"></i>
                </div>
                <textarea id="reviewComment" class="form-control rounded-3" rows="3" placeholder="How was your experience?"></textarea>
                <input type="hidden" id="modalBookingID">
                <input type="hidden" id="selectedRating" value="0">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="submitReview()">Post Review</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    
    function openReviewModal(bookingID, sitterName) {
        document.getElementById('modalBookingID').value = bookingID;
        document.getElementById('modalSitterName').innerText = sitterName;
        // Reset modal
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
            document.querySelectorAll('#starRating i').forEach(s => {
                s.classList.toggle('active', s.getAttribute('data-value') <= val);
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
</script>

</body>
</html>