<?php
date_default_timezone_set('Asia/Manila');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pampeers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $conn = null; // Set to null instead of dying
}

$conn->set_charset("utf8mb4");
?>