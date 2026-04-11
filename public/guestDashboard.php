<?php
session_start();

// Fetch available sitters
require_once __DIR__ . '/../app/controllers/sitter/sitterFetchAvail.php';

// Fetch sitters near the guardian
$userCity = isset($user['city']) ? $user['city'] : 'Cagayan De Oro';
require_once __DIR__ . '/../app/controllers/sitter/sitterFetchNear.php';

// prevent undefined errors
$sitters = $sitters ?? [];
$sittersNear = $sittersNear ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pampeers - Guardian Dashboard</title>
<link rel="icon" type="image/x-icon" href="../app/uploads/pampeerlogo.png">


<link href="https://fonts.googleapis.com/css2?family=Ribeye&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/guestDashboard.css">
</head>

<body>

<header class="sticky-top custom-header">
    <!-- ✅ only added flex-wrap -->
    <div class="left-side nav-container d-flex flex-wrap align-items-center justify-content-between px-2">

        <!-- Brand -->
        <div class="d-flex justify-content-center align-items-center gap-2">
            <img src="../app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <!-- Search Bar -->
        <!-- ✅ no layout change, just allow wrap -->
        <div class="search-bar d-flex align-items-center gap-3 flex-wrap">
            <span>Where</span>
            <div class="divider"></div>
            <span>When</span>
            <div class="divider"></div>
            <span>Who</span>
            <button class="search-btn">🔍</button>
        </div>

        <!-- Right Side -->
        <div class="right-side-p d-flex align-items-center gap-3 mt-2 mt-md-0">

            <a href="../public/register.php" class="signup-btn">
                Sign up
            </a>

            <button type="button" class="login-btn" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                Login
            </button>
        </div>
    </div>
</header>

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="col-12 col-md-12 d-flex align-items-center justify-content-center">
            <div class="login-panel">

                <h4>LOGIN</h4>

                <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
                    <div class="alert alert-success">Registration successful. You can now log in.</div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php
                            switch ($_GET['error']) {
                                case 'invalid':
                                    echo 'Invalid email or password.';
                                    break;
                                case 'unauthorized':
                                    echo 'Please log in first.';
                                    break;
                                case 'role_not_found':
                                case 'invalid_role':
                                    echo 'Invalid account role.';
                                    break;
                                case 'email_exists':
                                    echo 'Email already exists.';
                                    break;
                                default:
                                    echo 'Something went wrong.';
                                    break;
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="/pampeers/app/middleware/loginLogic.php" method="POST">
                    <div class="input-group mb-3">
                        <span class="input-group-text">@</span>
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" id="floatingInputGroup1" placeholder="Email" required>
                             <label for="floatingInputGroup1">Email</label>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">🔒︎</span>
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" id="floatingInputGroup2" placeholder="Password" required>
                            <label for="floatingInputGroup2">Password</label>
                        </div>
                    </div>

                    <small class="forgot mt-0">Forgot Password?</small>
                    <button type="submit" class="btn btn-primary w-100 mt-3">LOG IN</button>
                </form>

                <p class="signup mb-0">
                    New User? <a href="../public/register.php">Sign up now</a>
                </p>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- AVAILABLE SITTERS -->
<div class="container-fluid mt-4">
    <div class="section-title text-start">Available Babysitters</div>

    <?php if (!empty($sitters)): ?>
    <div class="carousel-wrapper">
        <?php foreach ($sitters as $peer): ?>
        <div class="carousel-card">
            <div class="small-card">

                <img src="/Pampeers/app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" 
                     alt="Sitter Profile Picture">

                <h6><?= htmlspecialchars($peer['name'] ?? '') ?></h6>

                <p class="city">
                    <?= htmlspecialchars($peer['city'] ?? '') ?>
                </p>
               
                <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center mt-4">No available sitters found.</p>
    <?php endif; ?>

</div>

<!-- NEAR YOU -->
<div class="container-fluid mt-4">
    <div class="section-title text-start">Peers in Cagayan De Oro City</div>

    <?php if (!empty($sittersNear)): ?>
    <div class="carousel-wrapper">
        <?php foreach ($sittersNear as $peer): ?>
        <div class="carousel-card">
            <div class="small-card">

                <img src="/Pampeers/app/uploads/profiles/<?= !empty($peer['img']) ? htmlspecialchars($peer['img']) : 'default.jpg'; ?>" 
                     alt="Sitter Profile Picture">

                <h6><?= htmlspecialchars($peer['name'] ?? '') ?></h6>

                <p class="city">
                    <?= htmlspecialchars($peer['city'] ?? '') ?>
                </p>
               
                <p>₱<?= htmlspecialchars($peer['rate'] ?? '0') ?>/hr</p>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="text-center mt-4">No available sitters found.</p>
    <?php endif; ?>

</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>