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
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- CRITICAL FOR RESPONSIVENESS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Sitter - Pampeers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/bookSitter.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <!-- Adjusted col-md-6 to col-lg-5 for better desktop centering, kept md-6 for tablet -->
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="card p-4">
                    <h3 class="mb-1 fw-bold">Book <?= htmlspecialchars($sitter['firstName'] . ' ' . $sitter['lastName']) ?></h3>
                    <p class="text-muted mb-4">Rate: ₱<?= htmlspecialchars($sitter['hourlyRate']) ?>/hr</p>

                    <form action="/Pampeers/app/controllers/booking/create.php" method="POST">
                        <input type="hidden" name="sitterID" value="<?= htmlspecialchars($sitter['sitterID']) ?>">
                        
                        <!-- Start Date/Time Group -->
                        <div class="row mb-3">
                            <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                <label class="form-label fw-semibold small">Start Date</label>
                                <input type="date" name="bookingDate" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold small">Start Time</label>
                                <input type="time" name="startTime" class="form-control" required>
                            </div>
                        </div>

                        <!-- End Date/Time Group -->
                        <div class="row mb-3">
                            <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                <label class="form-label fw-semibold small">End Date</label>
                                <input type="date" name="endDate" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-semibold small">End Time</label>
                                <input type="time" name="endTime" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Notes for the Sitter (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Allergies, house rules, emergency contact..."></textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <!-- Preserved your .submit class and design -->
                            <button type="submit" class="submit rounded-pill py-2 fw-bold border-0">Submit Booking Request</button>
                            <a href="guardianDashboard.php" class="btn btn-link text-muted text-decoration-none text-center small">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>