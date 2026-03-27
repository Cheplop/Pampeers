<?php
session_start();

// FIX: Moves UP one level from 'app' to find 'config'
include '../config/db_connect.php';

// 1. SECURITY CHECK: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. PREVENT CACHING (Security)
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 

// 3. DATA FETCHING: Get the specific user's info
$user_id = $_SESSION['user_id'];

// Fetch the new columns (firstname, lastname, and address info)
$sql = "SELECT firstname, lastname, email, role, sex, street, city, country FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    // Create clean variables for your partner to use in the HTML
    $full_name = $user['firstname'] . " " . $user['lastname'];
    $full_address = $user['street'] . ", " . $user['city'] . ", " . $user['country'];
    $user_role = ucfirst($user['role']); // Capitalizes 'parent' or 'sitter'
} else {
    // If user record is missing or deleted, force logout
    header("Location: logout.php");
    exit();
}
?>