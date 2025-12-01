<?php
// Database configuration for MySQL
// Update these values with your MySQL server details

define('DB_HOST', 'shopspshere-dbserver.database.windows.net');          // Your MySQL host (e.g., localhost or your Azure MySQL server)
define('DB_NAME', 'shopspheredb');            // Your database name
define('DB_USER', 'myadmin');              // Your MySQL username
define('DB_PASS', 'password123!');                  // Your MySQL password
define('DB_CHARSET', 'utf8mb4');

// Function to get MySQL connection
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed. Please try again later.");
    }
}

// Function to execute a prepared statement
function dbQuery($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        throw new Exception("Database query failed. Please try again later.");
    }
}
?>
