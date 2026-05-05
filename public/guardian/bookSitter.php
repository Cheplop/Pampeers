<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
requireAuth();

$sitterID = $_GET['sitterID'] ?? null;
if (!$sitterID) { header("Location: guardianDashboard.php"); exit(); }

// Fetch sitter details to show the name and rate
$stmt = $conn->prepare("SELECT s.*, u.firstName, u.lastName FROM sitters s JOIN users u ON s.userID = u.id WHERE s.sitterID = ?");
$stmt->bind_param("i", $sitterID);
$stmt->execute();
$sitter = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Sitter - Pampeers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <h3 class="mb-1">Book <?= htmlspecialchars($sitter['firstName'] . ' ' . $sitter['lastName']) ?></h3>
                    <p class="text-primary fw-bold">₱<?= htmlspecialchars($sitter['hourlyRate']) ?> / hour</p>
                    <hr>

                    <!-- This points to your existing create.php controller -->
                    <form action="../../app/controllers/booking/create.php" method="POST">
                        <input type="hidden" name="sitterID" value="<?= $sitterID ?>">

                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="bookingDate" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="startTime" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label">End Time</label>
                                <input type="time" name="endTime" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes for the Sitter (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Allergies, house rules..."></textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill py-2">Submit Booking Request</button>
                            <a href="public/guardianDashboard.php" class="btn btn-link text-muted">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>