<?php
// Database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost'); // <-- Replace with your AWS RDS endpoint or server IP
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'dairy');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

try {
    // Set PDO options
    $pdoOptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci" // Unicode support
    ];

    // Create PDO instance
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, $pdoOptions);

} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    exit('A database error occurred. Please try again later.');
}
?>
