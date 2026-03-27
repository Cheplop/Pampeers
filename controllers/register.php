<?php
// FIX: Moves UP one level from 'controllers' to find 'config'
include '../config/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. DATA VALIDATION: Check if the required fields exist in the form
    if (isset($_POST['email'], $_POST['password'], $_POST['firstName'], $_POST['lastName'], $_POST['role'])) {
        
        // 2. SANITIZE CORE ACCOUNT INFO
        $email     = mysqli_real_escape_string($conn, $_POST['email']);
        $role      = mysqli_real_escape_string($conn, $_POST['role']);
        $sex       = mysqli_real_escape_string($conn, isset($_POST['sex']) ? $_POST['sex'] : ''); 
        
        // 2.5 SANITIZE BIRTHDATE
        $birthdate = mysqli_real_escape_string($conn, $_POST['birthdate']);

        // 3. SANITIZE NAME FIELDS
        $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
        $lastName  = mysqli_real_escape_string($conn, $_POST['lastName']);
        
        // 4. SANITIZE ADDRESS FIELDS (Handle blanks if partner forgot a field)
        $street    = mysqli_real_escape_string($conn, isset($_POST['street']) ? $_POST['street'] : '');
        $city      = mysqli_real_escape_string($conn, isset($_POST['city']) ? $_POST['city'] : '');
        $country   = mysqli_real_escape_string($conn, isset($_POST['country']) ? $_POST['country'] : '');
        
        // 5. HASH PASSWORD
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // 6. SQL EXECUTION
        $sql = "INSERT INTO users (firstName, lastName, email, password, role, sex, street, city, country, birthdate) 
            VALUES ('$firstName', '$lastName', '$email', '$password', '$role', '$sex', '$street', '$city', '$country', '$birthdate')";

        if (mysqli_query($conn, $sql)) {
            // Redirect to login page inside the 'app' folder
            header("Location: ../public/login.php?registration=success");
            exit();
        } else {
            // Debugging error for your rubric!
            die("Database Error: " . mysqli_error($conn));
        }
        
    } else {
        die("Error: Form fields are missing. Please ensure your partner added name='firstName', etc.");
    }
}
?>