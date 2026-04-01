<?php
session_start();

// If user is already logged in, log them out to register a new account
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    // Restart session for the register page
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampeers - Create Account</title>
    <link rel="icon" type="image/x-icon" href="/pampeers/app/uploads/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./register.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="signup-panel">
            <div class="text-center mb-4">
                <img src="/pampeers/app/uploads/pampeerlogo.png" alt="Pampeer Logo">
                <p class="small text-muted mb-0">Get started with us</p>
                <h1 class="brand-name">Pampeers</h1>
            </div>

            <form action="/pampeers/app/controllers/createUser.php" method="POST" enctype="multipart/form-data" class="row g-3">

                <div class="col-md-12">
                    <label class="form-label">First Name</label>
                    <input type="text" name="firstName" class="form-control" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lastName" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                    <div class="form-text">Must be at least 8 characters</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sex</label>
                    <select class="form-select" name="sex">
                        <option value="">Choose...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role" required>
                        <option value="">Choose...</option>
                        <option value="guardian">Guardian</option>
                        <option value="sitter">Sitter</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Address</label>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="country" class="form-control" placeholder="Country">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="city" class="form-control" placeholder="City">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="street" class="form-control" placeholder="Street">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contactNumber" class="form-control" placeholder="09xxxxxxxxx">
                </div>

                <div class="col-12" id="sitterFields" style="display: none;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Hourly Rate</label>
                            <input type="number" step="0.01" min="0" name="hourlyRate" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Experience (years)</label>
                            <input type="number" min="0" name="experience" class="form-control" placeholder="0">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="Tell us about yourself"></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="profilePic" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100">SIGN UP</button>
                </div>
            </form>

            <p class="login-link mt-3 text-center">
                Already have an account? <a href="./login.php">LOGIN</a>
            </p>
        </div>
    </div>

    <script>
        const roleSelect = document.querySelector('select[name="role"]');
        const sitterFields = document.getElementById('sitterFields');

        function toggleSitterFields() {
            if (roleSelect.value === 'sitter') {
                sitterFields.style.display = 'block';
            } else {
                sitterFields.style.display = 'none';
            }
        }

        roleSelect.addEventListener('change', toggleSitterFields);
        toggleSitterFields();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>