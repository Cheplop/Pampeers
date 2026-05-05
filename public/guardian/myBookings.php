<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];

// Fetch all bookings made by this Guardian
// We join with 'sitters' and then 'users' to get the SITTER'S name
$query = "
    SELECT 
        b.*, 
        u.firstName AS sitterFirstName, 
        u.lastName AS sitterLastName, 
        u.profilePic AS sitterPic,
        s.hourlyRate
    FROM bookings b
    JOIN sitters s ON b.sitterID = s.sitterID
    JOIN users u ON s.userID = u.id
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
</head>
<body class="bg-light">

<header class="sticky-top custom-header bg-white shadow-sm p-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <a href="guardianDashboard.php" class="text-decoration-none text-dark">
                <i class="fa-solid fa-arrow-left me-2"></i>
                <span class="fw-bold">My Bookings</span>
            </a>
        </div>
    </div>
</header>

<main class="container">
    <?php if (empty($myBookings)): ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-calendar-ghost fa-3x text-muted mb-3"></i>
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
                                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($b['sitterPic']) ?>" 
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
                                            echo $b['status'] === 'accepted' ? 'text-success' : ($b['status'] === 'pending' ? 'text-warning' : 'text-danger'); 
                                        ?>"><?= $b['status'] ?></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 p-3 d-flex flex-column justify-content-center border-start bg-white">
                                <?php if ($b['status'] === 'pending'): ?>
                                    <!-- Potential Cancel Button Logic -->
                                    <button class="btn btn-outline-danger btn-sm rounded-pill">Cancel Request</button>
                                <?php elseif ($b['status'] === 'completed'): ?>
                                    <button class="btn btn-primary btn-sm rounded-pill">Leave a Review</button>
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

</body>
</html>