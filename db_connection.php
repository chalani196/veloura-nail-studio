<?php
// Database Configuration Settings
$host = "127.0.0.1";
$user = "root";       // Default WAMP username
$password = "";       // Default WAMP password
$database = "veloura_nail_studio";
$port = 3307;         // WAMP MariaDB Port

// Create Database Connection
$conn = new mysqli($host, $user, $password, $database, $port);

// Check Connection Error
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set UTF-8 Character Set
$conn->set_charset("utf8mb4");
?>