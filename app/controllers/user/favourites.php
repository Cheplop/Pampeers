<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/config/config.php';
requireAuth();

$uID = $_SESSION['user_id'];

// FIXED: Corrected column names (userID instead of uID, firstName/lastName instead of name)
$query = "SELECT s.*, u.firstName, u.lastName, u.profilePic as img, u.cityMunicipality as city 
          FROM favourites f 
          JOIN sitters s ON f.sitter_id = s.sitterID 
          JOIN users u ON s.userID = u.id 
          WHERE f.guardian_id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uID);
$stmt->execute();
$favSitters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pampeers - My Favourites</title>
    <link rel="icon" type="image/png" href="/Pampeers/app/uploads/pampeerlogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">My Favourite Sitters</h3>
    <?php if (empty($favSitters)): ?>
        <div class="text-center py-5">
            <p class="text-muted">No favourites yet.</p>
            <a href="guardianDashboard.php" class="btn btn-primary rounded-pill px-4">Browse Sitters</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($favSitters as $peer): ?>
                <div class="col">
                    <div class="small-card bg-white p-3 shadow-sm rounded-4">
                        <div class="card-img-container mb-2">
                            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($peer['img'] ?: 'default.jpg'); ?>" 
                                 class="img-fluid rounded-3" style="height:150px; width:100%; object-fit:cover;">
                        </div>
                        <h6 class="mb-1"><?= htmlspecialchars($peer['firstName'] . ' ' . $peer['lastName']) ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($peer['city']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary">₱<?= htmlspecialchars($peer['hourlyRate']) ?>/hr</span>
                            <a href="bookSitter.php?sitterID=<?= $peer['sitterID'] ?>" class="btn btn-sm btn-dark rounded-pill px-3">Book</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>