<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampeers - Login</title>
    <link rel="icon" type="image/x-icon" href="/Pampeers_copyRepo/uploads/profiles/pampeerlogo.png">

    <link href="https://fonts.googleapis.com/css2?family=Ribeye&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Pampeers_copyRepo/public/style.css">
</head>
<body>
    <div class="container-fluid main-container p-0">
        <div class="row g-0 h-100">

            <div class="col-12 col-md-7 left-side d-none d-md-flex flex-column justify-content-between">
                <div class="my-background"></div>

                <div class="hero-content p-2">
                    <div class="d-flex align-items-center gap-2 mb-0">
                        <img src="/Pampeers_copyRepo/uploads/pampeerlogo.png" alt="Pampeer Logo" class="logo">
                        <h1 class="mb-0">Pampeers</h1>
                    </div>
                    <p>Hire a babysitter right at your hand!</p>
                </div>

                <ul class="list-inline d-flex justify-content-end gap-5 mb-0">
                    <li class="list-inline-item">Trusted and Verified Sitters</li>
                    <li class="list-inline-item">Easy Booking Process</li>
                    <li class="list-inline-item">24/7 Customer Support</li>
                </ul>
            </div>

            <div class="col-12 col-md-5 right-side d-flex align-items-center justify-content-center">
                <div class="login-panel">

                    <div class="d-flex d-md-none justify-content-center gap-2 mb-3">
                        <img src="/Pampeers_copyRepo/uploads/profiles/pampeerlogo.png" alt="Pampeer Logo" class="logo">
                    </div>

                    <h4>LOGIN</h4>

                    <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
                        <div class="alert alert-success">Registration successful. You can now log in.</div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php
                                switch ($_GET['error']) {
                                    case 'invalid':
                                        echo 'Invalid email or password.';
                                        break;
                                    case 'unauthorized':
                                        echo 'Please log in first.';
                                        break;
                                    case 'role_not_found':
                                    case 'invalid_role':
                                        echo 'Invalid account role.';
                                        break;
                                    case 'email_exists':
                                        echo 'Email already exists.';
                                        break;
                                    default:
                                        echo 'Something went wrong.';
                                        break;
                                }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="/Pampeers_copyRepo/app/middleware/loginLogic.php" method="POST">
                        <div class="input-group mb-3">
                            <span class="input-group-text">@</span>
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control" id="floatingInputGroup1" placeholder="Email" required>
                                <label for="floatingInputGroup1">Email</label>
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">🔒︎</span>
                            <div class="form-floating">
                                <input type="password" name="password" class="form-control" id="floatingInputGroup2" placeholder="Password" required>
                                <label for="floatingInputGroup2">Password</label>
                            </div>
                        </div>

                        <small class="forgot mt-0">Forgot Password?</small>
                        <button type="submit" class="btn btn-primary w-100 mt-3">CONTINUE</button>
                    </form>

                    <p class="signup">
                        New User? <a href="/Pampeers_copyRepo/register">Sign up now</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</body>
</html>