<?php
/**
 * Database Connection with Environment Detection
 * Supports local MySQL and Railway MySQL deployment
 */

// Check if we're on Railway (environment variables are set)
if (isset($_ENV['MYSQL_URL']) || getenv('MYSQL_URL')) {
    // Parse MYSQL_URL for Railway
    $databaseUrl = $_ENV['MYSQL_URL'] ?? getenv('MYSQL_URL');
    $dbParts = parse_url($databaseUrl);
    
    $host = $dbParts['host'];
    $port = $dbParts['port'] ?? 3306;
    $dbname = ltrim($dbParts['path'], '/');
    $username = $dbParts['user'];
    $password = $dbParts['pass'];
    
} elseif (isset($_ENV['MYSQLHOST']) || getenv('MYSQLHOST')) {
    // Alternative Railway MySQL format
    $host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST');
    $port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? 3306;
    $dbname = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE');
    $username = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER');
    $password = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD');
    
} else {
    // Local development settings
    $host = 'localhost';
    $port = 3306;
    $dbname = 'judging_system';
    $username = 'root';
    $password = 'root';
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Auto-create tables and sample data for demo
if (file_exists(__DIR__ . '/setup_functions.php')) {
    require_once __DIR__ . '/setup_functions.php';
    createTablesIfNotExists($pdo);
}
?>