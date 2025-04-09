<?php
$host = "localhost";
$dbname = "capstone";
$username = "root";
$password = "";

// Set the correct timezone
date_default_timezone_set('Asia/Manila');

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
