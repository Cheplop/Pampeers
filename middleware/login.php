<?php
session_start();

// FIX: Moves UP one level from 'app' to find the 'config' folder
include '../config/db_connect.php'; 

// 1. Pre-login Check: If already logged in, skip the login page
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. DATA VALIDATION: Ensure email and password were sent from the form
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        // 3. SQL EXECUTION: Fetch user data including firstname for personalized UI
        $sql = "SELECT id, password, role, firstname FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if ($result && $user = mysqli_fetch_assoc($result)) {
            
            // 4. SECURITY CHECK: Verify the hashed password
            if (password_verify($password, $user['password'])) {
                
                // Store essential data in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['firstname'] = $user['firstname']; 
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>