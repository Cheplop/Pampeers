<?php

require_once __DIR__ . '/../../app/middleware/authCheck.php';
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchAvail.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitter Dashboard - Pampeers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .navbar-brand { font-weight: bold; color: #667eea !important; }
        .profile-section { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .profile-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
        .info-item { margin-bottom: 15px; }
        .info-label { font-weight: bold; color: #667eea; }
        .action-buttons { margin-top: 20px; }
        .stats-card { background: white; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 15px; }
        .stat-value { font-size: 28px; font-weight: bold; color: #667eea; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Pampeers</a>
            <div class="ms-auto">
                <span class="me-3">Welcome, <?= htmlspecialchars($user['firstName'] ?? '') ?>!</span>
                <a class="btn btn-outline-danger btn-sm" href="/pampeers/app/controllers/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="profile-section">
            <div class="row">
                <div class="col-md-3 text-center">
                    <?php if (!empty($user['profilePic'])): ?>
                        <img src="/pampeers/uploads/profiles/<?= htmlspecialchars($user['profilePic']) ?>" alt="Profile" class="profile-img mb-3">
                    <?php else: ?>
                        <div class="mb-3">
                            <div class="profile-img mx-auto d-flex align-items-center justify-content-center bg-light">
                                <span class="text-muted">No Photo</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-9">
                    <h2><?= htmlspecialchars($fullName) ?></h2>

                    <div class="info-item">
                        <span class="info-label">Email:</span> <?= htmlspecialchars($user['email']) ?>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Age:</span> <?= $age ?> years
                    </div>

                    <div class="info-item">
                        <span class="info-label">Location:</span> <?= htmlspecialchars($location) ?>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Contact:</span> <?= htmlspecialchars($user['contactNumber'] ?? 'N/A') ?>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Availability:</span> 
                        <span class="badge bg-<?= $availability === 'Available' ? 'success' : 'warning' ?>">
                            <?= $availability ?>
                        </span>
                    </div>

                    <div class="action-buttons">
                        <a href="../../app/controllers/sitter/updateProfile.php" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sitter Stats -->
        <div class="row">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stat-value"><?= htmlspecialchars($user['hourlyRate'] ?? '0.00') ?></div>
                    <div>Hourly Rate (₱)</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stat-value"><?= htmlspecialchars($user['experience'] ?? '0') ?></div>
                    <div>Years Experience</div>
                </div>
            </div>
        </div>

        <?php if (!empty($user['bio'])): ?>
            <div class="profile-section">
                <h5>About Me</h5>
                <p><?= htmlspecialchars($user['bio']) ?></p>
            </div>
        <?php endif; ?>

        <div class="alert alert-info">
            <strong>Welcome to Pampeers Sitter Dashboard!</strong> Here you can view and manage your profile.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
