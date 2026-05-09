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
$stmt = $conn->prepare("SELECT firstName, lastName, profilePic FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

/* ================= STATS ================= */
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE deletedAt IS NULL")->fetch_assoc()['total'] ?? 0;
$totalGuardians = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'guardian' AND deletedAt IS NULL")->fetch_assoc()['total'] ?? 0;
$totalSitters = $conn->query("SELECT COUNT(*) AS total FROM sitters WHERE verificationStatus = 'verified'")->fetch_assoc()['total'] ?? 0;

/* ================= RECENT USERS ================= */
// ADDED: displayRole logic using IF and LEFT JOIN
$recentUsersResult = $conn->query("
    SELECT u.id, u.firstName, u.lastName, u.emailAddress, u.role, u.createdAt AS dateJoined, u.isActive, s.verificationStatus,
           IF(s.sitterID IS NOT NULL, 'Guardian / Sitter', 'Guardian') AS displayRole
    FROM users u
    LEFT JOIN sitters s ON u.id = s.userID
    WHERE u.role != 'admin' AND u.deletedAt IS NULL
    ORDER BY u.createdAt DESC LIMIT 10
");

/* ================= PENDING SITTERS ================= */
$pendingSitters = $conn->query("
    SELECT s.sitterID, u.id as userID, u.firstName, u.lastName, u.emailAddress, s.verificationStatus
    FROM sitters s
    JOIN users u ON s.userID = u.id
    WHERE s.verificationStatus = 'pending' AND u.deletedAt IS NULL
    ORDER BY s.createdAt DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampeers - Admin Dashboard</title>
    
    <link rel="icon" type="image/png" href="/Pampeers/app/uploads/pampeerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="bg-light">

<header class="sticky-top custom-header bg-white shadow-sm">
    <div class="nav-container d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="logo" class="logo-img">
            <p class="brand m-0">Pampeers <span class="badge bg-danger rounded-pill ms-2" style="font-family:'Poppins', sans-serif; font-size: 0.7rem; vertical-align: middle;">ADMIN</span></p>
        </div>

        <div class="right-side-p d-flex align-items-center gap-2">
            <div class="profile-wrapper">
                <img src="/Pampeers/app/uploads/profiles/<?= htmlspecialchars($admin['profilePic'] ?? 'default.jpg'); ?>" class="profile-img border" alt="Profile">
            </div>
            <div class="dropdown">
                <button class="btn border-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars fs-5"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li><h6 class="dropdown-header fw-bold"><?= htmlspecialchars($admin['firstName'] . ' ' . $admin['lastName']) ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../../app/controllers/auth/logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<main class="container-fluid mt-4 px-4 pb-5">
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> Action completed successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> An error occurred processing your request.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-bold text-uppercase small">Total Users</p>
                        <h2 class="fw-bold m-0 text-dark"><?= $totalUsers ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-users fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-bold text-uppercase small">Guardians</p>
                        <h2 class="fw-bold m-0 text-dark"><?= $totalGuardians ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                        <i class="fa-solid fa-shield-halved fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-bold text-uppercase small">Verified Sitters</p>
                        <h2 class="fw-bold m-0 text-dark"><?= $totalSitters ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                        <i class="fa-solid fa-baby-carriage fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-title h5 fw-bold mb-3 text-dark"><i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>Pending Approvals</div>
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-5 border-start border-warning border-4">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="text-muted small text-uppercase">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pendingSitters->num_rows > 0): ?>
                        <?php while ($sitter = $pendingSitters->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($sitter['firstName'] . ' ' . $sitter['lastName']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($sitter['emailAddress'] ?? 'No email') ?></td>
                                <td><span class="badge bg-warning text-dark px-3 rounded-pill">Pending</span></td>
                                <td class="text-end">
                                    <a href="/Pampeers/app/controllers/admin/verifySitter.php?id=<?= $sitter['sitterID'] ?>&action=approve" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm me-1">Approve</a>
                                    <a href="/Pampeers/app/controllers/admin/verifySitter.php?id=<?= $sitter['sitterID'] ?>&action=reject" class="btn btn-outline-danger btn-sm rounded-pill px-3">Reject</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No pending sitter applications.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-title h5 fw-bold mb-3 text-dark"><i class="fa-solid fa-users-gear text-primary me-2"></i>Manage Users</div>
    <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-primary border-4">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="text-muted small text-uppercase">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentUsersResult->num_rows > 0): ?>
                        <?php while ($u = $recentUsersResult->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($u['emailAddress'] ?? 'No email') ?></td>
                             
                                <td>
                                    <span class="badge <?= strpos($u['displayRole'], 'Sitter') !== false ? 'bg-primary' : 'bg-secondary' ?> bg-opacity-10 text-dark px-2 rounded">
                                        <?= htmlspecialchars($u['displayRole']) ?>
                                    </span>
                                    
                                    <?php if (strpos($u['displayRole'], 'Sitter') !== false && !empty($u['verificationStatus'])): ?>
                                        <div class="mt-1">
                                            <?php 
                                                $vStatus = $u['verificationStatus'];
                                                $badgeClass = 'bg-secondary';
                                                
                                                if ($vStatus === 'verified') $badgeClass = 'bg-success';
                                                elseif ($vStatus === 'pending') $badgeClass = 'bg-warning text-dark';
                                                elseif ($vStatus === 'rejected') $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?> px-2 rounded-pill" style="font-size: 0.65rem;">
                                                <i class="fa-solid <?= $vStatus === 'verified' ? 'fa-check' : ($vStatus === 'pending' ? 'fa-clock' : 'fa-xmark') ?> me-1"></i>
                                                <?= ucfirst(htmlspecialchars($vStatus)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="text-muted small"><?= date('M d, Y', strtotime($u['dateJoined'])) ?></td>
                                <td>
                                    <?php if ($u['isActive']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-circle fa-2xs me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-circle fa-2xs me-1"></i>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($u['isActive']): ?>
                                        <form action="/Pampeers/app/controllers/admin/deactivateUser.php" method="POST" class="d-inline">
                                            <input type="hidden" name="userID" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm" onclick="return confirm('Deactivate this user?');">Deactivate</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="/Pampeers/app/controllers/admin/reactivateUser.php" method="POST" class="d-inline">
                                            <input type="hidden" name="userID" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm" onclick="return confirm('Reactivate this user?');">Reactivate</button>
                                        </form>
                                    <?php endif; ?>

                                    <form action="/Pampeers/app/controllers/admin/deleteUser.php" method="POST" class="d-inline ms-1">
                                        <input type="hidden" name="userID" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('WARNING: Permanently delete this user?');">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>