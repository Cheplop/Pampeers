<?php
// ==============================
// BASIC APP CONFIG
// ==============================

// Set timezone
date_default_timezone_set('Asia/Manila');

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================
// DEV MODE (TURN OFF BEFORE PROD)
// ==============================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==============================
// DATABASE CONNECTION
// ==============================
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "pampeers2";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>