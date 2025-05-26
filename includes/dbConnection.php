<?php
/**
 * Database Connection with Environment Detection
 * Supports both local MySQL and Render MySQL deployment
 */

// Check if we're on Render (environment variables are set)
if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL')) {
    // Parse DATABASE_URL for Render MySQL
    $databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
    $dbParts = parse_url($databaseUrl);
    
    $host = $dbParts['host'];
    $port = $dbParts['port'] ?? 3306;
    $dbname = ltrim($dbParts['path'], '/');
    $username = $dbParts['user'];
    $password = $dbParts['pass'];
    
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