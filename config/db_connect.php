<?php
// Database Configuration
$servername = "localhost";
$username = "root";     // Default XAMPP user
$password = "";         // Default XAMPP password is empty
$dbname = "pampeers";   // The name of the database you created

// Create connection using MySQLi (Object-Oriented style)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // If it fails, stop the script and show the error
    // This helps with the Debugging (30%) part of your rubric!
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to utf8mb4 (matches your database collation)
$conn->set_charset("utf8mb4");
?>