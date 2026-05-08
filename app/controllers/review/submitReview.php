<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $bookingId = $_POST['bookingID'] ?? null;
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if (!$bookingId || $rating < 1 || $rating > 5) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid rating (1-5).']);
        exit();
    }

    // 1. Verify the booking belongs to this user and is 'completed'
    // Matches your SQL: bookings table (bookingID, userID, status, sitterID)
    $stmt = $conn->prepare("SELECT sitterID FROM bookings WHERE bookingID = ? AND userID = ? AND status = 'completed'");
    $stmt->bind_param("ii", $bookingId, $userId);
    $stmt->execute();
    $sitterData = $stmt->get_result()->fetch_assoc();

    if (!$sitterData) {
        echo json_encode(['status' => 'error', 'message' => 'You can only review completed bookings.']);
        exit();
    }

    $sitterId = $sitterData['sitterID'];

    // 2. Check if a review already exists for this booking to prevent duplicates
    $checkReview = $conn->prepare("SELECT reviewID FROM reviews WHERE bookingID = ?");
    $checkReview->bind_param("i", $bookingId);
    $checkReview->execute();
    if ($checkReview->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already reviewed this booking.']);
        exit();
    }

    // 3. Insert the Review
    // Matches your SQL: reviews table (uuid, bookingID, userID, sitterID, rating, comment)
    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4)); // Generate a standard UUID string
    $insert = $conn->prepare("INSERT INTO reviews (uuid, bookingID, userID, sitterID, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("siiiis", $uuid, $bookingId, $userId, $sitterId, $rating, $comment);
    
    if ($insert->execute()) {
        // 4. Update the Sitter's ratingAverage
        // Matches your SQL: sitters table (ratingAverage, sitterID)
        $updateRating = $conn->prepare("
            UPDATE sitters 
            SET ratingAverage = (SELECT AVG(rating) FROM reviews WHERE sitterID = ?) 
            WHERE sitterID = ?
        ");
        $updateRating->bind_param("ii", $sitterId, $sitterId);
        $updateRating->execute();

        echo json_encode(['status' => 'success', 'message' => 'Review submitted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
}