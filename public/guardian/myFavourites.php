<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/config/config.php';
requireAuth();

// Use one consistent variable for the user ID
$uID = $_SESSION['user_id'];

/* ================= FETCH FAVOURITE SITTERS ================= */
// profilePic and cityMunicipality come from the 'users' table (u)
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
$stmt->close();

/* ================= FETCH LOGGED-IN USER PHOTO ================= */
$stmt = $conn->prepare("SELECT profilePic FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $uID); // Fixed: was $userId
$stmt->execute();
$userResult = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fallback to default if no image exists
$profilePic = (!empty($userResult['profilePic'])) ? $userResult['profilePic'] : 'default.jpg';
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
        <div class="d-none d-lg-flex align-items-center gap-2">
            <a href="/Pampeers/public/guardian/guardianDashboard.php">
                <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img" alt="Pampeers Logo" >
            </a>
            <p class="brand m-0"><a href="/Pampeers/public/guardian/guardianDashboard.php">Pampeers</a></p>
        </div>

        <div class="right-side-p d-flex align-items-center justify-content-end gap-3 ms-auto">
            <div class="nav-btn d-flex align-items-center gap-2">
                <a href="../profile.php" class="text-decoration-none">
                    <div class="profile-wrapper">
                        <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic); ?>" class="profile-img" alt="Profile">
                    </div>
                </a>

                <div class="dropdown">
                    <button class="btn-dropdown border-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../profile.php"><i class="fa-regular fa-user me-2"></i>View Profile</a></li>
                        <li><a class="dropdown-item" href="myFavourites.php"><i class="fa-regular fa-heart me-2"></i>Favourites</a></li>
                        <li><a class="dropdown-item" href="/Pampeers/public/guardian/myBookings.php"><i class="fa-regular fa-calendar me-2"></i>Bookings</a></li>
                        <li class="logout"><a class="dropdown-item" href="/Pampeers/app/controllers/auth/logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a></li>
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
        <h2 class="label">FAVOURITE PEERS</h2>
    </div>

    <?php if (!empty($favSitters)): ?>
        <div class="carousel-wrapper" id="avail-carousel">
            <?php foreach ($favSitters as $peer): ?>
            <div class="carousel-card fav-card" id="sitter-card-<?= htmlspecialchars($peer['sitterID']) ?>">
                <div class="small-card" style="cursor: pointer;" onclick="window.location.href='viewSitterProfile.php?sitterID=<?= htmlspecialchars($peer['sitterID']) ?>'">
                    <div class="card-img-container">
                        <button class="like-btn" data-id="<?= htmlspecialchars($peer['sitterID']) ?>" aria-label="Unlike" onclick="event.stopPropagation();">
                            <i class="fa-solid text-danger fa-heart"></i> 
                        </button>
                        <img src="/Pampeers/app/uploads/profiles/<?= !empty($peer['profilePic']) ? htmlspecialchars($peer['profilePic']) : 'default.jpg'; ?>" alt="Sitter">
                    </div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($peer['firstName'] . ' ' . $peer['lastName']) ?></h6>
                    <p>
                        <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($peer['cityMunicipality']) ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <p class="m-0 fw-bold">₱<?= number_format($peer['hourlyRate'], 2) ?>/hr</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state text-center py-5">
            <p class="big text-muted mt-4">No favourite sitters found</p>
            <p class="small text-muted">Start exploring to find the perfect peer for your needs.</p>
            <a href="guardianDashboard.php" class="btn-browse btn rounded-pill px-4 border-1">Browse Sitters</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
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
            // Assuming your controller returns 'removed' when a favorite is toggled off
            if (data.status === 'removed' || data.success) {
                if (card) {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    
                    setTimeout(() => {
                        card.remove();
                        // If no cards are left, reload to show the "empty state" message
                        if (document.querySelectorAll('.fav-card').length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            }
        })
        .catch(err => console.error('Error:', err));
    });
});
</script>

</body>
</html>