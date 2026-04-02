<?php
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchAvail.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Available Sitters</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/guardianDashboard.css">

</head>

<body>

<!-- SEARCH BAR -->
<div class="search-bar">
    <span>Where</span>
    <span>|</span>
    <span>When</span>
    <span>|</span>
    <span>Who</span>
</div>

<!-- AVAILABLE SITTERS -->
<div class="container mt-4">
    <div class="section-title text-start">AVAILABLE SITTERS</div>
    <div class="row">
        <?php foreach ($sitters as $peer): ?>
        <div class="col-sm-4 col-md-4 col-lg-2 mb-3 ml-4">
            <div class="small-card">
                <img src="../../app/uploads/profiles/<?= $peer['img'] ?>">

                <h6><?= $peer['name'] ?></h6>

                <p><?= $peer['city'] ?></p>
                <p>₱<?= $peer['rate'] ?>/hr</p>
                <p><?= $peer['bio'] ?></p>

                <button class="btn btn-outline-dark">GET IN TOUCH</button>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>