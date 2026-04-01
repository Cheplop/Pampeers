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

$recentUsersQuery = "SELECT firstName, lastName, email, role, dateCreated FROM users ORDER BY dateCreated DESC LIMIT 5";
$recentUsersResult = $conn->query($recentUsersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pampeers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .sidebar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); }
        .dashboard-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; font-size: 14px; }
        .table-container { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar">
                <div class="mb-4">
                    <h4 class="fw-bold">Pampeers Admin</h4>
                    <p><?= htmlspecialchars($admin['firstName'] ?? 'Admin') ?></p>
                </div>
                <a href="#dashboard">Dashboard</a>
                <a href="manageUsers.php">Manage Users</a>
                <a href="/pampeers/app/controllers/logout.php" class="mt-5">Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 p-4">
                <h2 class="mb-4">Dashboard</h2>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="stat-label">Total Users</div>
                            <div class="stat-number"><?= $totalUsers ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="stat-label">Guardians</div>
                            <div class="stat-number"><?= $totalGuardians ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="stat-label">Sitters</div>
                            <div class="stat-number"><?= $totalSitters ?></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="table-container">
                    <h5 class="mb-3">Recent Users</h5>
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
                                    <td><span class="badge bg-info"><?= htmlspecialchars($user['role']) ?></span></td>
                                    <td><?= htmlspecialchars($user['dateCreated'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
