<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

requireAuth();

$userId = $_SESSION['user_id'];
$sitterId = $_GET['sitterID'] ?? null;

if (!$sitterId) {
    header("Location: guardianDashboard.php");
    exit();
}

// Fetch Sitter and User Details
$query = "SELECT s.*, u.firstName, u.lastName, u.profilePic, u.cityMunicipality, u.bio, u.sex, u.birthDate 
          FROM sitters s 
          JOIN users u ON s.userID = u.id 
          WHERE s.sitterID = ? AND s.verificationStatus = 'verified' AND u.isActive = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $sitterId);
$stmt->execute();
$sitter = $stmt->get_result()->fetch_assoc();

if (!$sitter) {
    echo "Sitter not found or unavailable.";
    exit();
}

// Calculate Age
$age = 'Unknown';
if (!empty($sitter['birthDate'])) {
    $birthDate = new DateTime($sitter['birthDate']);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
}

// Check if currently favorited by this guardian
$favCheck = $conn->prepare("SELECT id FROM favourites WHERE guardian_id = ? AND sitter_id = ?");
$favCheck->bind_param("ii", $userId, $sitterId);
$favCheck->execute();
$isFavorited = $favCheck->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($sitter['firstName']) ?>'s Profile - Pampeers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .profile-header { background: linear-gradient(135deg, #a8c0ff 0%, #3f2b96 100%); height: 150px; border-radius: 20px 20px 0 0; }
        .profile-img { width: 150px; height: 150px; object-fit: cover; border: 5px solid white; margin-top: -75px; }
    </style>
</head>
<body>

<div class="container py-5 max-w-75" style="max-width: 800px;">
    
    <a href="guardianDashboard.php" class="btn btn-light rounded-circle shadow-sm mb-4">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="profile-header"></div>
        <div class="card-body text-center px-5 pb-5">
            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($sitter['profilePic'] ?: 'default.jpg') ?>" class="rounded-circle profile-img shadow-sm mb-3">
            
            <h2 class="fw-bold mb-1"><?= htmlspecialchars($sitter['firstName'] . ' ' . $sitter['lastName']) ?></h2>
            <p class="text-muted mb-3"><i class="fa-solid fa-location-dot me-2"></i><?= htmlspecialchars($sitter['cityMunicipality']) ?></p>

            <div class="d-flex justify-content-center gap-3 mb-4">
                <div class="bg-light px-4 py-2 rounded-3 border">
                    <h5 class="fw-bold text-primary m-0">₱<?= number_format($sitter['hourlyRate'], 2) ?></h5>
                    <small class="text-muted">per hour</small>
                </div>
                <div class="bg-light px-4 py-2 rounded-3 border">
                    <h5 class="fw-bold m-0"><?= htmlspecialchars($sitter['experience']) ?> Years</h5>
                    <small class="text-muted">Experience</small>
                </div>
            </div>

            <p class="text-start mb-4" style="line-height: 1.8;">
                <?= nl2br(htmlspecialchars($sitter['bio'] ?: 'This sitter has not written a bio yet.')) ?>
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="bookSitter.php?sitterID=<?= $sitter['sitterID'] ?>" class="btn btn-primary rounded-pill px-5 py-2 fw-bold w-50">
                    Book Now
                </a>
                
                <button id="favBtn" class="btn btn-<?= $isFavorited ? 'danger' : 'outline-danger' ?> rounded-pill px-4 py-2 fw-bold" onclick="toggleFavourite(<?= $sitter['sitterID'] ?>)">
                    <i class="fa-<?= $isFavorited ? 'solid' : 'regular' ?> fa-heart me-2" id="favIcon"></i> 
                    <span id="favText"><?= $isFavorited ? 'Favorited' : 'Favourite' ?></span>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
function toggleFavourite(sitterId) {
    fetch('/Pampeers/app/controllers/user/toggleFavourite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `sitterId=${sitterId}`
    })
    .then(response => response.json())
    .then(data => {
        const btn = document.getElementById('favBtn');
        const icon = document.getElementById('favIcon');
        const text = document.getElementById('favText');

        if (data.status === 'added') {
            btn.classList.replace('btn-outline-danger', 'btn-danger');
            icon.classList.replace('fa-regular', 'fa-solid');
            text.innerText = 'Favorited';
        } else if (data.status === 'removed') {
            btn.classList.replace('btn-danger', 'btn-outline-danger');
            icon.classList.replace('fa-solid', 'fa-regular');
            text.innerText = 'Favourite';
        }
    })
    .catch(err => console.error('Error:', err));
}
</script>

</body>
</html>