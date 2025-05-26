<?php
// Database connection
$host = 'localhost';
$dbname = 'judging_system';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTables($pdo) {
    // Admin users table 
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_username VARCHAR(50) NOT NULL UNIQUE,
        admin_password VARCHAR(255) NOT NULL,
        admin_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Judges table 
    $pdo->exec("CREATE TABLE IF NOT EXISTS judges (
        judge_id INT AUTO_INCREMENT PRIMARY KEY,
        judge_username VARCHAR(50) NOT NULL UNIQUE,
        judge_display_name VARCHAR(100) NOT NULL,
        judge_password_hash VARCHAR(255) NOT NULL,
        judge_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Participants table 
    $pdo->exec("CREATE TABLE IF NOT EXISTS participants (
        participant_id INT AUTO_INCREMENT PRIMARY KEY,
        participant_name VARCHAR(100) NOT NULL,
        participant_category VARCHAR(50) NOT NULL,
        participant_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Scores table
    $pdo->exec("CREATE TABLE IF NOT EXISTS scores (
        score_id INT AUTO_INCREMENT PRIMARY KEY,
        score_participant_id INT NOT NULL,
        score_judge_id INT NOT NULL,
        score_value INT NOT NULL,
        score_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (score_participant_id) REFERENCES participants(participant_id),
        FOREIGN KEY (score_judge_id) REFERENCES judges(judge_id),
        UNIQUE KEY unique_judge_participant (score_judge_id, score_participant_id)
    )");

    // Insert default admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE admin_username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (admin_username, admin_password) VALUES ('admin', ?)");
        $stmt->execute([$hashedPassword]);
        echo "Default admin user created (username: admin, password: password)<br>";
    }

    // Insert sample judges if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM judges");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $judgePassword = password_hash('P%ssw2rd', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO judges (judge_username, judge_display_name, judge_password_hash) VALUES (?, ?, ?)");
        $stmt->execute(['judge1', 'Alex Kamau', $judgePassword]);
        $stmt->execute(['judge2', 'Sarah Bismack', $judgePassword]);
        echo "Sample judges created with password 'P%ssw2rd'<br>";
    }

    // Insert sample participants if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO participants (participant_name, participant_category) VALUES (?, ?)");
        $stmt->execute(['Team Alpha', 'FPS']);
        $stmt->execute(['Digital Dragons', 'MOBA']);
        $stmt->execute(['Pixel Pirates', 'Strategy']);
        $stmt->execute(['Neon Drivers', 'Racing']);
        echo "Sample participants created<br>";
    }
}

// Initialize tables
createTables($pdo);

//echo "Database setup completed successfully!";
?>