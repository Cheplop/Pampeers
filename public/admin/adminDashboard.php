<?php
require_once __DIR__ . '/../../app/middleware/authCheck.php';
require_once __DIR__ . '/../../app/config/db_connect.php';

checkAuth('admin');

$adminId = $_SESSION['user_id'];

$adminStmt = $conn->prepare("SELECT firstName, lastName, profilePic FROM users WHERE uID = ? LIMIT 1");
$adminStmt->bind_param("i", $adminId);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();
$admin = $adminResult->fetch_assoc();
$adminStmt->close();

$userCountQuery = "SELECT COUNT(*) AS total FROM users";
$userResult = $conn->query($userCountQuery);
$userData = $userResult->fetch_assoc();
$totalUsers = $userData['total'] ?? 0;

$guardianCountQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'guardian'";
$guardianResult = $conn->query($guardianCountQuery);
$guardianData = $guardianResult->fetch_assoc();
$totalGuardians = $guardianData['total'] ?? 0;

$sitterCountQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'sitter'";
$sitterResult = $conn->query($sitterCountQuery);
$sitterData = $sitterResult->fetch_assoc();
$totalSitters = $sitterData['total'] ?? 0;

$recentUsersQuery = "SELECT firstName, lastName, email, role, dateCreated FROM users ORDER BY dateCreated DESC LIMIT 10";
$recentUsersResult = $conn->query($recentUsersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Pampeers</title>
<link rel="icon" type="image/x-icon" href="/Pampeers/app/uploads/pampeerlogo.png">

<link rel="stylesheet" href="../css/adminDashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Ribeye&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<header class="sticky-top custom-header">
    <div class="nav-container d-flex flex-wrap align-items-center justify-content-between px-4">
        
        <!-- Brand -->
        <div class="d-flex justify-content-center align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <!-- Right Side -->
        <div class="d-flex align-items-center gap-3">
            <a href="../../app/controllers/logout.php" class="logout-btn">
                Logout
            </a>

            <?php $userPic = !empty($admin['profilePic']) ? $admin['profilePic'] : 'default.jpg'; ?>

            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($userPic); ?>" 
                 class="profile-img-p" 
                 alt="Profile Picture">
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="cards row">

        <div class="col-md-12 p-3 p-md-4">

            <p class="mb-4">
                Welcome Back, <b><?= htmlspecialchars($admin['firstName'] . ' ' . $admin['lastName']) ?>!</b>
            </p>

            <!-- Statistics Cards -->
            <div class="row mb-4">

                <div class="col-md-4 mb-3">
                    <div class="dashboard-card">
                        <div class="stat-label">Total Users</div>
                        <div class="stat-number"><?= $totalUsers ?></div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="dashboard-card">
                        <div class="stat-label">Guardians</div>
                        <div class="stat-number"><?= $totalGuardians ?></div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="dashboard-card">
                        <div class="stat-label">Sitters</div>
                        <div class="stat-number"><?= $totalSitters ?></div>
                    </div>
                </div>

            </div>

            <!-- Recent Users -->
            <div class="table-container">
                <h5 class="mb-3">Recent Users</h5>

                <!-- ✅ ONLY ADD THIS WRAPPER -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recentUsersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge custom-badge">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($user['dateCreated'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <!-- ✅ END -->

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>