<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/config/config.php';
requireAuth();

$uID = $_SESSION['user_id'];

// Join tables to get sitter details and user names[cite: 2]
$query = "SELECT s.*, u.name, u.profilePic as img, u.cityMunicipality as city 
          FROM favourites f 
          JOIN sitters s ON f.sitterID = s.sitterID 
          JOIN users u ON s.uID = u.id 
          WHERE f.uID = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uID);
$stmt->execute();
$favSitters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampeers - My Favourites</title>
    <link rel="icon" type="image/png" href="/Pampeers/app/uploads/pampeerlogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="guardianDashboard.php" class="btn btn-outline-dark btn-sm rounded-circle">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="section-title m-0">My Favourites</h2>
    </div>

    <?php if (empty($favSitters)): ?>
        <div class="text-center py-5">
            <i class="fa-regular fa-heart fa-3x text-muted mb-3"></i>
            <p class="text-muted">You haven't added any sitters to your favourites yet.</p>
            <a href="guardianDashboard.php" class="btn btn-primary rounded-pill px-4">Browse Sitters</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($favSitters as $peer): ?>
                <div class="col">
                    <div class="small-card bg-white p-3 shadow-sm rounded-4">
                        <div class="card-img-container mb-2">
                            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($peer['img'] ?: 'default.jpg'); ?>" 
                                 class="img-fluid rounded-3" alt="Sitter">
                        </div>
                        <h6 class="mb-1"><?= htmlspecialchars($peer['name']) ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($peer['city']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary">₱<?= htmlspecialchars($peer['rate']) ?>/hr</span>
                            <a href="bookSitter.php?sitterID=<?= $peer['sitterID'] ?>" class="btn btn-sm btn-dark rounded-pill px-3">Book</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>