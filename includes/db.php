<?php
// Database Configuration
$host = 'localhost'; // Change if using a different host
$dbname = 'procurement_system'; // Change to your actual database name
$user = 'root'; // Change to your database user
$pass = ''; // Change to your database password

// Create connection using MySQLi
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}

// Set charset to utf8mb4 for better security
$conn->set_charset("utf8mb4");
?>
