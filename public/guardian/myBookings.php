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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - Pampeers</title>
    <link rel="icon" type="image/png" href="/Pampeers/app/uploads/pampeerlogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #FDF9F1; font-family: 'Poppins', sans-serif; }
        .booking-card { background: #fff; border-radius: 15px; border: none; transition: transform 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .booking-card:hover { transform: translateY(-5px); }
        .sitter-img { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; }
        .status-badge { font-weight: 600; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-accepted { background-color: #d4edda; color: #155724; }
        .status-completed { background-color: #cce5ff; color: #004085; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .star-rating i { color: #ddd; cursor: pointer; font-size: 1.5rem; transition: color 0.2s; }
        .star-rating i.active, .star-rating i:hover, .star-rating i:hover ~ i { color: #f5b301; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">My Bookings</h2>
        <a href="guardianDashboard.php" class="btn btn-outline-dark rounded-pill px-4">Back to Dashboard</a>
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

                        <div class="mb-3 flex-grow-1">
                            <p class="mb-1"><i class="fa-regular fa-calendar text-primary me-2"></i> <strong>Date:</strong> <?= $dateDisplay ?></p>
                            <p class="mb-1"><i class="fa-regular fa-clock text-primary me-2"></i> <strong>Time:</strong> <?= $timeDisplay ?></p>
                            <p class="mb-1"><i class="fa-solid fa-hourglass-half text-primary me-2"></i> <strong>Duration:</strong> <?= $hours ?> hrs</p>
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