<?php
// db.php
// Database connection file using MySQLi for MySQL database

$host = 'localhost';
$username = 'root';
$password = '1234';
$dbname = 'user_management';

// 1. Establish connection to MySQL server
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 2. Create database if it does not exist
$dbQuery = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($dbQuery)) {
    die("Database creation failed: " . $conn->error);
}

// Select the database
if (!$conn->select_db($dbname)) {
    die("Database selection failed: " . $conn->error);
}

// 3. Create users table if it does not exist
$createTableQuery = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NULL,
        gender VARCHAR(15) NULL,
        bio TEXT NULL,
        dob DATE NULL,
        address TEXT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (!$conn->query($createTableQuery)) {
    die("Table creation failed: " . $conn->error);
}
?>
