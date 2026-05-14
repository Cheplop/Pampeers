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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sitter['firstName']) ?>'s Profile - Pampeers</title>
    <link rel="icon" href="/Pampeers/app/uploads/pampeerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/viewSitterProfile.css">
</head>
<body>

<div class="container-fluid py-5">
    <div class="row justify-content-center mt-5">  
        <div class="col-12 col-md-10 col-lg-6 d-flex flex-column align-items-center">
            
            <div class="col-12">  
                <a href="guardianDashboard.php" class="btn btn-light rounded-circle shadow-sm mb-4">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>

            <div class="card w-100 shadow-sm rounded-4 mb-4">
                <!-- Changed flex-row to flex-column flex-md-row to allow mobile stacking -->
                <div class="card-body d-flex flex-column flex-md-row align-items-center align-items-md-start px-3 py-4 px-md-5 py-md-5 g-2">
                    
                    <!-- Your original image (logic and design preserved) -->
                    <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($sitter['profilePic'] ?: 'default.jpg') ?>" class="mb-3 mb-md-0">

                    <div class="d-flex flex-column gap-3 ms-md-4 text-center text-md-start">
                        <div class="personal-info">
                            <!-- Typography preserved -->
                            <?= nl2br(htmlspecialchars($sitter['bio'] ?: 'This sitter has not written a bio yet.')) ?>
                            <h2 class="fw-bold mb-1"><?= htmlspecialchars($sitter['firstName'] . ' ' . $sitter['lastName']) ?></h2>
                            <p class="text-muted mb-3"><i class="fa-solid fa-location-arrow me-2"></i><?= htmlspecialchars($sitter['cityMunicipality']) ?></p>
                        </div>
                        
                        <div class="work-info justify-content-md-start">
                            <p><i class="fa-solid fa-user-clock me-2"></i><?= htmlspecialchars($sitter['experience']) ?> Years Experience</p>
                            <p><i class="fa-solid fa-money-bill-1 me-2"></i><?= number_format($sitter['hourlyRate'], 1) ?>/hr</p>
                        </div>

                        <div class="btns d-flex d-column justify-content-center align-content-between justify-content-md-start mt-2 gap-2">
                            <a href="bookSitter.php?sitterID=<?= $sitter['sitterID'] ?>" class="book-now btn rounded-pill">
                                Book Now
                            </a>    
                            <button id="favBtn" class="btn btn-<?= $isFavorited ? 'danger' : 'outline-danger' ?> rounded-pill px-4 py-2 fw-bold" onclick="toggleFavourite(<?= $sitter['sitterID'] ?>)">
                                <i class="fa-<?= $isFavorited ? 'solid' : 'regular' ?> fa-heart" id="favIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
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
            text.innerText = 'Favorite';
        }
    })
    .catch(err => console.error('Error:', err));
}
</script>

</body>
</html>