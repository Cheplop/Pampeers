<?php
// Set the timezone to Asia/Manila for the application
date_default_timezone_set('Asia/Manila');
// Start a session to handle user login data
session_start();

// Database connection details
$servername = "localhost"; // The server where the database is running
$username = "root"; // Username to connect to the database
$password = ""; // Password for the database user
$database = "pampeers"; // Name of the database

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check if the connection failed, and stop the script if it did
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>