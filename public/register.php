<?php
session_start();

if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampeers - Create Account</title>

    <link rel="icon" type="image/x-icon" href="/Pampeers/app/uploads/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
<div class="signup-panel">

    <div class="text-center mb-4">
        <img src="/Pampeers/app/uploads/pampeerlogo.png" alt="Logo">
        <p class="small text-muted mb-0">Get started with us</p>
        <h1 class="brand-name">Pampeers</h1>
    </div>

    <form action="/Pampeers/app/controllers/auth/createUser.php"
          method="POST"
          enctype="multipart/form-data"
          class="row g-3">

        <!-- BASIC INFO -->
        <div class="col-md-6">
            <input type="text" name="firstName" class="form-control" placeholder="First Name" required>
        </div>

        <div class="col-md-6">
            <input type="text" name="lastName" class="form-control" placeholder="Last Name" required>
        </div>

        <div class="col-12">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="col-12">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>

        <div class="col-12">
            <input type="password" name="password" class="form-control" placeholder="Password" required minlength="8">
        </div>

        <div class="col-md-6">
            <input type="date" name="birthDate" class="form-control" required>
        </div>

        <div class="col-md-6">
            <select name="sex" class="form-select">
                <option value="">Sex</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>

        <!-- ADDRESS -->
        <div class="col-md-6">
            <input type="text" name="streetAddress" class="form-control" placeholder="Street Address">
        </div>

        <div class="col-md-6">
            <input type="text" name="barangay" class="form-control" placeholder="Barangay">
        </div>

        <div class="col-md-6">
            <input type="text" name="cityMunicipality" class="form-control" placeholder="City/Municipality">
        </div>

        <div class="col-md-6">
            <input type="text" name="province" class="form-control" placeholder="Province">
        </div>

        <div class="col-md-6">
            <input type="text" name="country" class="form-control" placeholder="Country">
        </div>

        <div class="col-md-6">
            <input type="text" name="zipCode" class="form-control" placeholder="ZIP Code">
        </div>

        <!-- CONTACT -->
        <div class="col-12">
            <input type="text" name="contactNumber" class="form-control" placeholder="Contact Number">
        </div>

        <!-- PROFILE -->
        <div class="col-12">
            <input type="file" name="profilePic" class="form-control">
        </div>

        <!-- SUBMIT -->
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-primary w-100">SIGN UP</button>
        </div>

    </form>

    <p class="text-center mt-3">
        Already have an account? <a href="guestDashboard.php">LOGIN</a>
    </p>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>