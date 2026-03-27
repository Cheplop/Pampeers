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
// Fetch the columns including birthdate
$sql = "SELECT firstname, lastname, email, role, sex, street, city, country, birthdate FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    // Clean variables for HTML
    $full_name = $user['firstname'] . " " . $user['lastname'];
    $birthdate_raw = $user['birthdate'];
    
    // OPTIONAL: Calculate Age for a better UI
    $bday = new DateTime($birthdate_raw);
    $today = new DateTime('today');
    $age = $bday->diff($today)->y;
}