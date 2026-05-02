<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

requireAuth();
requireRole('admin');

$adminId = $_SESSION['user_id'];

/* ================= ADMIN INFO ================= */
$stmt = $conn->prepare("
    SELECT firstName, lastName, profilePic
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

/* ================= STATS (FIXED) ================= */

/* total users */
$totalUsers = $conn->query("
    SELECT COUNT(*) AS total FROM users
")->fetch_assoc()['total'] ?? 0;

/* guardians */
$totalGuardians = $conn->query("
    SELECT COUNT(*) AS total 
    FROM users 
    WHERE role = 'guardian'
")->fetch_assoc()['total'] ?? 0;

/* sitters (IMPORTANT FIX → USE SITTERS TABLE) */
$totalSitters = $conn->query("
    SELECT COUNT(*) AS total 
    FROM sitters
    WHERE verificationStatus = 'verified'
")->fetch_assoc()['total'] ?? 0;

/* ================= RECENT USERS ================= */
$recentUsersResult = $conn->query("
    SELECT firstName, lastName, emailAddress, role, dateCreated
    FROM users
    ORDER BY dateCreated DESC
    LIMIT 10
");

/* ================= PENDING SITTERS ================= */
$pendingSitters = $conn->query("
    SELECT s.sitterID, s.verificationStatus,
           u.id, u.firstName, u.lastName, u.emailAddress
    FROM sitters s
    INNER JOIN users u ON u.id = s.userID
    WHERE s.verificationStatus = 'pending'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Pampeers</title>

<link rel="icon" href="/Pampeers/app/uploads/pampeerlogo.png">
<link rel="stylesheet" href="/Pampeers/public/css/adminDashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<!-- HEADER -->
<header class="sticky-top custom-header">
    <div class="nav-container d-flex justify-content-between align-items-center px-4">

        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" class="logo-img">
            <p class="brand m-0">Pampeers</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="/Pampeers/app/controllers/auth/logout.php" class="logout-btn">Logout</a>

            <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($admin['profilePic'] ?? 'default.jpg') ?>"
                 class="profile-img-p">
        </div>

    </div>
</header>

<div class="container-fluid p-4">

    <h5>
        Welcome Back, 
        <b><?= htmlspecialchars(($admin['firstName'] ?? '') . ' ' . ($admin['lastName'] ?? '')) ?></b>
    </h5>

    <!-- STATS -->
    <div class="row mt-4">

        <div class="col-md-4">
            <div class="dashboard-card">
                <div>Total Users</div>
                <h3><?= $totalUsers ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card">
                <div>Guardians</div>
                <h3><?= $totalGuardians ?></h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dashboard-card">
                <div>Sitters (Verified)</div>
                <h3><?= $totalSitters ?></h3>
            </div>
        </div>

    </div>

    <!-- RECENT USERS -->
    <div class="table-container mt-4">
        <h5>Recent Users</h5>

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
                            <td><?= htmlspecialchars($user['emailAddress']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['dateCreated']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>

    <!-- PENDING SITTERS -->
    <div class="table-container mt-4">
        <h5>Pending Sitters</h5>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($sitter = $pendingSitters->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($sitter['firstName'] . ' ' . $sitter['lastName']) ?></td>
                            <td><?= htmlspecialchars($sitter['emailAddress']) ?></td>
                            <td><?= htmlspecialchars($sitter['verificationStatus']) ?></td>
                            <td>
                                <a href="/Pampeers/app/controllers/admin/verifySitter.php?id=<?= $sitter['sitterID'] ?>&action=approve"
                                   class="btn btn-success btn-sm">Approve</a>

                                <a href="/Pampeers/app/controllers/admin/verifySitter.php?id=<?= $sitter['sitterID'] ?>&action=reject"
                                   class="btn btn-danger btn-sm">Reject</a>

                                <a href="/Pampeers/app/controllers/admin/deactivateUser.php?id=<?= $sitter['id'] ?>"
                                   class="btn btn-warning btn-sm">Deactivate</a>

                                <a href="/Pampeers/app/controllers/admin/deleteUser.php?id=<?= $sitter['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

</body>
</html>