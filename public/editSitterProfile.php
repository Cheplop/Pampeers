<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/helpers/sitter.php';

requireAuth();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /Pampeers/public/login.php");
    exit();
}

/* BLOCK NON-SITTER */
if (!isSitter($conn, $userId)) {
    header("Location: /Pampeers/public/profile.php?error=not_sitter");
    exit();
}

/* GET SITTER DATA */
$sitter = getSitter($conn, $userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Sitter Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">

    <h3>Edit Sitter Profile</h3>

    <form method="POST" action="/Pampeers/app/controllers/sitter/updateSitter.php">

        <div class="mb-3">
            <label>Bio</label>
            <textarea name="bio" class="form-control"><?= htmlspecialchars($sitter['bio'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label>Hourly Rate</label>
            <input type="number" name="hourlyRate" class="form-control"
                   value="<?= htmlspecialchars($sitter['hourlyRate'] ?? 0) ?>">
        </div>

        <div class="mb-3">
            <label>Experience</label>
            <input type="number" name="experience" class="form-control"
                   value="<?= htmlspecialchars($sitter['experience'] ?? 0) ?>">
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="/Pampeers/public/profile.php" class="btn btn-secondary">Back</a>

    </form>

</div>

</body>
</html>