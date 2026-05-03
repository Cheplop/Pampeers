<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/sitter.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/guestDashboard.php");
    exit();
}

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

/* ================= SITTER DATA ================= */
$sitterData = getSitter($conn, $userId);
$isSitter = !empty($sitterData);

$verificationStatus = $sitterData['verificationStatus'] ?? null;

/* store sitter_id safely */
if ($isSitter) {
    $_SESSION['sitter_id'] = $sitterData['sitterID'];
}

/* ================= USER INFO ================= */
$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));

if ($fullName === '') {
    $fullName = $user['username'] ?? 'User';
}

$email = $user['emailAddress'] ?? '';
$location = $user['cityMunicipality'] ?? 'Cagayan de Oro';
$bio = $sitterData['bio'] ?? '';
$profilePic = $user['profilePic'] ?? 'default.jpg';

$role = $user['role'] ?? 'guardian';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Pampeers</title>

    <link rel="icon" href="/Pampeers/app/uploads/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/Pampeers/public/css/dashboard.css">
</head>

<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">

        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <div class="right-side-p d-flex align-items-center gap-3">

            <!-- GUARDIAN DASHBOARD ALWAYS -->
            <a href="/Pampeers/public/guardian/guardianDashboard.php" class="signup-btn">
                Guardian Dashboard
            </a>

            <?php if ($isSitter): ?>

                <?php if ($verificationStatus === 'verified'): ?>
                    <a href="/Pampeers/public/sitter/sitterDashboard.php" class="signup-btn">
                        Sitter Dashboard
                    </a>

                <?php elseif ($verificationStatus === 'pending'): ?>
                    <span class="btn btn-secondary btn-sm disabled">
                        Sitter Pending
                    </span>

                <?php else: ?>
                    <span class="btn btn-danger btn-sm disabled">
                        Not Verified
                    </span>
                <?php endif; ?>

            <?php endif; ?>

            <a href="/Pampeers/app/controllers/auth/logout.php" class="login-btn">
                Logout
            </a>

        </div>

    </div>
</header>

<main class="container-fluid mt-4 px-4">

    <div class="section-title">Profile</div>

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="small-card p-4">

                <div class="d-flex align-items-center gap-4">

                    <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($profilePic) ?>"
                         style="width:120px;height:120px;border-radius:50%;object-fit:cover;">

                    <div>
                        <h4><?= htmlspecialchars($fullName) ?></h4>
                        <p class="text-muted m-0"><?= htmlspecialchars($email) ?></p>
                        <p class="text-muted m-0"><?= htmlspecialchars($location) ?></p>
                    </div>

                </div>

                <hr>

                <p><?= $bio ?: 'No bio yet.' ?></p>

                <div class="d-flex gap-2">

                    <a href="/Pampeers/public/editProfile.php"
                       class="btn btn-primary btn-sm">
                        Edit Profile
                    </a>

                    <?php if ($isSitter): ?>

                        <!-- IMPORTANT FIXED PATH -->
                        <a href="/Pampeers/public/editSitterProfile.php"
                           class="btn btn-warning btn-sm">
                            Edit Sitter Profile
                        </a>

                        <?php if ($verificationStatus === 'pending'): ?>
                            <span class="btn btn-secondary btn-sm disabled">
                                Pending Verification
                            </span>
                        <?php endif; ?>

                    <?php else: ?>

                        <a href="/Pampeers/app/controllers/user/becomeSitter.php"
                           class="btn btn-success btn-sm">
                            Become a Sitter
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</main>

</body>
</html>