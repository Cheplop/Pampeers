<?php
require_once __DIR__ . '/../app/middleware/authCheck.php';


checkAuth(['sitter', 'guardian']);

if ($_SESSION['role'] === 'sitter') {
    require_once __DIR__ . '/../app/controllers/sitter/sitterFetchData.php';
} elseif ($_SESSION['role'] === 'guardian') {
    require_once __DIR__ . '/../app/controllers/guardian/guardianFetchData.php';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitter Dashboard - Pampeers</title>
    <link rel="icon" type="image/x-icon" href="/Pampeers/app/uploads/pampeerlogo.png">
    
    <link rel="stylesheet" href="css/sitterProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Ribeye&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<!-- ========== HEADER / NAVBAR ========== -->
<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">

        <!-- Brand Logo -->
        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <!-- Right Side: Profile + Menu -->
        <div class="right-side-p d-flex align-items-center gap-1">

            <!-- Profile Picture Link -->
            <button type="button" class="btn btn-link">
                <a href="../SitterProfile.php">
                    <?php $userPic = !empty($user['profilePic']) ? $user['profilePic'] : 'default.jpg'; ?>
                    <div class="profile-wrapper">
                        <img
                            src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($userPic); ?>"
                            class="profile-img-small"
                            alt="Profile"
                        >
                    </div>
                </a>
            </button>

            <!-- Hamburger Dropdown Menu -->
            <div class="dropdown">
                <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" type="button">Favourites</button></li>
                    <li><button class="dropdown-item" type="button">Messages</button></li>
                    <a class="dropdown-item" href="../sitterProfile.php">
                        Profile
                    </a>
                    <li>
                    <a class="dropdown-item" href="../../app/controllers/logout.php">
                        Logout
                    </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</header>

<div class="row g-3 mt-0 g-2 mb-3 p-2 mx-4">
        <div class="col-lg-7">
            <div class="card-profile p-4 d-flex align-items-center h-100">
                <?php if (!empty($user['profilePic'])): ?>
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($user['profilePic']) ?>" alt="Profile" class="profile-img mb-3">
                    <?php else: ?>
                        <div class="mb-3">
                            <div class="profile-img mx-auto d-flex align-items-center justify-content-center bg-light">
                                <span class="text-muted">No Photo</span>
                            </div>
                        </div>
                    <?php endif; ?>                
                    <div class="m-5">
                    <p class="mb-0"> <?= htmlspecialchars($user['bio']) ?> </p>
                    <h2 class="fw-bold mb-0"><?= htmlspecialchars($fullName) ?></h2>
                    <p class="text-muted small"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-profile d-flex flex-column justify-content-center p-4 h-100">
                <div class="info-item divider m-3">
                    <i class="bi bi-person-fill"></i><img src="/Pampeers/app/uploads/age.png" alt="Age">Age: <?= $age ?>
                </div>
                <div class="info-item divider m-3">
                    <i class="bi bi-pin-angle-fill"></i> <img src="/Pampeers/app/uploads/location.png" alt="Location"> Location: <?= htmlspecialchars($location) ?>
                </div>
                <div class="info-item divider m-3">
                    <i class="bi bi-briefcase-fill"></i> <img src="/Pampeers/app/uploads/exp.png" alt="Experience"> Experience: <?= htmlspecialchars($user['experience'] ?? '0') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="queue d-flex justify-content-between align-items-center mt-0 mb-3">
        <h5 class="fw-bold m-0">Work Queue</h5>
        <a href="#" class="view-all">View all ></a>
    </div>

    <div class="card-white p-4 shadow-sm mb-4 g-4">
        <div class="row">
            <div class="col-md-9">
                <h4 class="fw-bold">Mr. Jared Smith</h4>
                <p class="job-note">
                    Note: Hey, Charles. I just want to give you heads up that my kid has an allergy to peanuts, shrimps, almonds or any kind of nuts. Please take care of him and we will be back as soon as possible as we are finished with our errands.
                </p>
                <div class="job-details">
                    <div class="mb-1"><i class="bi bi-pin-map-fill"></i><img src="/Pampeers/app/uploads/locationgrey.png" alt="Location"> Xavier Estates, Cagayan De Oro City, Misamis Oriental</div>
                    <div><i class="bi bi-calendar3"></i> <img src="/Pampeers/app/uploads/date.png" alt="Date"> April 6, 2026   | <i class="bi bi-clock"></i>  <img src="/Pampeers/app/uploads/time.png" alt="time">  13:00 - 17:00 | 5 Hours</div>
                </div>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0 d-flex flex-column justify-content-between">
                <div class="price-tag">
                    <span class="currency">₱</span> 1,545.000
                </div>
                <a href="#" class="read-more">Read More</a>
            </div>
        </div>
    </div>

    <div class="card-white p-4 shadow-sm mb-5">
        <h6 class="fw-bold mb-3"><i class="bi bi-chat-square-quote-fill text-primary me-2"></i> Recent Reviews</h6>
        <p class="mb-1 fst-italic">"Charles, was very kind and gentle to my kids. He is loved and favorites. I would work with him again"</p>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">~ Jared</span>
            <a href="#" class="read-more">Read More</a>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
