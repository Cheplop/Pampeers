<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/config/config.php';
requireAuth();

$uID = $_SESSION['user_id'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favourites - Pampeers</title>
    
    <link rel="icon" type="image/png" href="/Pampeers/app/uploads/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="../css/myFavourites.css">
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
                            <a class="dropdown-item" href="/Pampeers/public/guardian/myBookings.php"><i class="fa-regular fa-calendar me-2"></i>Bookings</a>
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

<div class="container py-5">
    <div class="d-flex align-items-center mb-5">
        <a href="guardianDashboard.php" class="btn btn-light rounded-circle me-3 shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="label">My Favourite Peers</h2>
    </div>

    <?php if (empty($favSitters)): ?>
        <div class="empty-likes">
            <i class="fa-regular fa-heart fa-4x text-muted mb-3 opacity-25"></i>
            <h4 class="text-muted">No favourites yet</h4>
            <p class="text-muted mb-4">Start exploring to find the perfect peer for your needs.</p>
            <a href="guardianDashboard.php" class="btn btn-primary rounded-pill px-4">Browse Sitters</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="fav-container">
            <?php foreach ($favSitters as $peer): ?>
                <div class="col" id="sitter-card-<?= $peer['sitterID'] ?>">
                    <div class="card fav-card h-100 shadow-sm p-3 position-relative">
                        
                        <button class="btn-heart like-btn" data-id="<?= $peer['sitterID'] ?>">
                            <i class="fa-solid fa-heart text-danger"></i>
                        </button>

                        <div class="text-center mb-3">
                            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars(!empty($peer['profilePic']) ? $peer['profilePic'] : 'default.jpg'); ?>" 
                                 class="rounded-circle border" 
                                 style="width: 100px; height: 100px; object-fit: cover;" alt="Sitter">
                        </div>

                        <div class="card-body p-0 text-center">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($peer['firstName'] . ' ' . $peer['lastName']) ?></h6>
                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($peer['cityMunicipality']) ?>
                            </p>
                            
                            <div class="bg-light p-2 rounded-3 mb-3">
                                <span class="fw-bold text-primary">₱<?= number_format($peer['hourlyRate'], 2) ?>/hr</span>
                            </div>

                            <a href="bookSitter.php?sitterID=<?= $peer['sitterID'] ?>" class="btn btn-dark w-100 rounded-pill fw-bold">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Reuse the heart toggle logic from the dashboard
document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const sitterId = this.getAttribute('data-id');
        const card = document.getElementById(`sitter-card-${sitterId}`);
        
        fetch('/Pampeers/app/controllers/user/toggleFavourite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `sitterId=${sitterId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'removed') {
                // Smoothly remove the card since we are on the Favourites page
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    // If no cards left, reload to show the "No favorites" message
                    if (document.querySelectorAll('.fav-card').length === 0) {
                        location.reload();
                    }
                }, 300);
            }
        })
        .catch(err => console.error('Error:', err));
    });
});
</script>

</body>
</html>