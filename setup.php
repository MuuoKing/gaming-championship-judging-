<?php
/**
 * Database Schema Script for Judging System
 * 
 * This script creates the database and all necessary tables with proper naming conventions:
 * - All columns are prefixed with their table name
 * - Audit tables follow the same naming pattern
 * - The 'users' table has been renamed to 'admins' for clarity
 * - Includes sample data for initial setup
 */

// Database connection parameters
$host = 'localhost';
$dbname = 'judging_system';
$username = 'root';
$password = 'root';

try {
    // Create database if it doesn't exist
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "Database created successfully<br>";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create admins table (previously users)
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_username VARCHAR(50) NOT NULL UNIQUE,
        admin_password VARCHAR(255) NOT NULL,
        admin_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        admin_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_admin_username (admin_username)
    )");
    echo "Admins table created successfully<br>";
    
    // Admins audit table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins_audit (
        admin_audit_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_audit_operation_type ENUM('INSERT','UPDATE','DELETE') NOT NULL,
        admin_audit_admin_id INT NOT NULL,
        admin_audit_old_username VARCHAR(50),
        admin_audit_new_username VARCHAR(50),
        admin_audit_old_password VARCHAR(255),
        admin_audit_new_password VARCHAR(255),
        admin_audit_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        admin_audit_changed_by VARCHAR(50)
    )");
    echo "Admins audit table created successfully<br>";
    
    // Judges table
    $pdo->exec("CREATE TABLE IF NOT EXISTS judges (
        judge_id INT AUTO_INCREMENT PRIMARY KEY,
        judge_username VARCHAR(50) NOT NULL UNIQUE,
        judge_display_name VARCHAR(100) NOT NULL,
        judge_password_hash VARCHAR(255) NOT NULL,
        judge_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        judge_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_judge_username (judge_username),
        INDEX idx_judge_display_name (judge_display_name)
    )");
    echo "Judges table created successfully<br>";
    
    // Judges audit table
    $pdo->exec("CREATE TABLE IF NOT EXISTS judges_audit (
        judge_audit_id INT AUTO_INCREMENT PRIMARY KEY,
        judge_audit_operation_type ENUM('INSERT','UPDATE','DELETE') NOT NULL,
        judge_audit_judge_id INT NOT NULL,
        judge_audit_old_username VARCHAR(50),
        judge_audit_new_username VARCHAR(50),
        judge_audit_old_display_name VARCHAR(100),
        judge_audit_new_display_name VARCHAR(100),
        judge_audit_old_password_hash VARCHAR(255),
        judge_audit_new_password_hash VARCHAR(255),
        judge_audit_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        judge_audit_changed_by VARCHAR(50),
        INDEX idx_judge_audit_judge_id (judge_audit_judge_id)
    )");
    echo "Judges audit table created successfully<br>";
    
    // Participants table
    $pdo->exec("CREATE TABLE IF NOT EXISTS participants (
        participant_id INT AUTO_INCREMENT PRIMARY KEY,
        participant_name VARCHAR(100) NOT NULL,
        participant_category VARCHAR(50) NOT NULL,
        participant_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        participant_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_participant_name (participant_name),
        INDEX idx_participant_category (participant_category)
    )");
    echo "Participants table created successfully<br>";
    
    // Participants audit table
    $pdo->exec("CREATE TABLE IF NOT EXISTS participants_audit (
        participant_audit_id INT AUTO_INCREMENT PRIMARY KEY,
        participant_audit_operation_type ENUM('INSERT','UPDATE','DELETE') NOT NULL,
        participant_audit_participant_id INT NOT NULL,
        participant_audit_old_name VARCHAR(100),
        participant_audit_new_name VARCHAR(100),
        participant_audit_old_category VARCHAR(50),
        participant_audit_new_category VARCHAR(50),
        participant_audit_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        participant_audit_changed_by VARCHAR(50),
        INDEX idx_participant_audit_participant_id (participant_audit_participant_id)
    )");
    echo "Participants audit table created successfully<br>";
    
    // Scores table
    $pdo->exec("CREATE TABLE IF NOT EXISTS scores (
        score_id INT AUTO_INCREMENT PRIMARY KEY,
        score_participant_id INT NOT NULL,
        score_judge_id INT NOT NULL,
        score_value INT NOT NULL,
        score_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        score_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (score_participant_id) REFERENCES participants(participant_id),
        FOREIGN KEY (score_judge_id) REFERENCES judges(judge_id),
        INDEX idx_score_participant (score_participant_id),
        INDEX idx_score_judge (score_judge_id),
        INDEX idx_score_value (score_value),
        UNIQUE KEY unique_judge_participant (score_judge_id, score_participant_id)
    )");
    echo "Scores table created successfully<br>";
    
    // Scores audit table
    $pdo->exec("CREATE TABLE IF NOT EXISTS scores_audit (
        score_audit_id INT AUTO_INCREMENT PRIMARY KEY,
        score_audit_operation_type ENUM('INSERT','UPDATE','DELETE') NOT NULL,
        score_audit_score_id INT NOT NULL,
        score_audit_old_participant_id INT,
        score_audit_new_participant_id INT,
        score_audit_old_judge_id INT,
        score_audit_new_judge_id INT,
        score_audit_old_value INT,
        score_audit_new_value INT,
        score_audit_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        score_audit_changed_by VARCHAR(50),
        INDEX idx_score_audit_score_id (score_audit_score_id)
    )");
    echo "Scores audit table created successfully<br>";
    
    // Create triggers for admins table
    $pdo->exec("DROP TRIGGER IF EXISTS admins_after_insert");
    $pdo->exec("CREATE TRIGGER admins_after_insert AFTER INSERT ON admins FOR EACH ROW 
                INSERT INTO admins_audit (
                    admin_audit_operation_type, 
                    admin_audit_admin_id, 
                    admin_audit_new_username, 
                    admin_audit_new_password, 
                    admin_audit_changed_by
                ) VALUES (
                    'INSERT', 
                    NEW.admin_id, 
                    NEW.admin_username, 
                    NEW.admin_password, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS admins_after_update");
    $pdo->exec("CREATE TRIGGER admins_after_update AFTER UPDATE ON admins FOR EACH ROW 
                INSERT INTO admins_audit (
                    admin_audit_operation_type, 
                    admin_audit_admin_id, 
                    admin_audit_old_username, 
                    admin_audit_new_username, 
                    admin_audit_old_password, 
                    admin_audit_new_password, 
                    admin_audit_changed_by
                ) VALUES (
                    'UPDATE', 
                    NEW.admin_id, 
                    OLD.admin_username, 
                    NEW.admin_username, 
                    OLD.admin_password, 
                    NEW.admin_password, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS admins_after_delete");
    $pdo->exec("CREATE TRIGGER admins_after_delete AFTER DELETE ON admins FOR EACH ROW 
                INSERT INTO admins_audit (
                    admin_audit_operation_type, 
                    admin_audit_admin_id, 
                    admin_audit_old_username, 
                    admin_audit_old_password, 
                    admin_audit_changed_by
                ) VALUES (
                    'DELETE', 
                    OLD.admin_id, 
                    OLD.admin_username, 
                    OLD.admin_password, 
                    CURRENT_USER()
                )");
    echo "Admins triggers created successfully<br>";
    
    // Create triggers for judges table
    $pdo->exec("DROP TRIGGER IF EXISTS judges_after_insert");
    $pdo->exec("CREATE TRIGGER judges_after_insert AFTER INSERT ON judges FOR EACH ROW 
                INSERT INTO judges_audit (
                    judge_audit_operation_type, 
                    judge_audit_judge_id, 
                    judge_audit_new_username, 
                    judge_audit_new_display_name, 
                    judge_audit_new_password_hash, 
                    judge_audit_changed_by
                ) VALUES (
                    'INSERT', 
                    NEW.judge_id, 
                    NEW.judge_username, 
                    NEW.judge_display_name, 
                    NEW.judge_password_hash, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS judges_after_update");
    $pdo->exec("CREATE TRIGGER judges_after_update AFTER UPDATE ON judges FOR EACH ROW 
                INSERT INTO judges_audit (
                    judge_audit_operation_type, 
                    judge_audit_judge_id, 
                    judge_audit_old_username, 
                    judge_audit_new_username, 
                    judge_audit_old_display_name, 
                    judge_audit_new_display_name, 
                    judge_audit_old_password_hash, 
                    judge_audit_new_password_hash, 
                    judge_audit_changed_by
                ) VALUES (
                    'UPDATE', 
                    NEW.judge_id, 
                    OLD.judge_username, 
                    NEW.judge_username, 
                    OLD.judge_display_name, 
                    NEW.judge_display_name, 
                    OLD.judge_password_hash, 
                    NEW.judge_password_hash, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS judges_after_delete");
    $pdo->exec("CREATE TRIGGER judges_after_delete AFTER DELETE ON judges FOR EACH ROW 
                INSERT INTO judges_audit (
                    judge_audit_operation_type, 
                    judge_audit_judge_id, 
                    judge_audit_old_username, 
                    judge_audit_old_display_name, 
                    judge_audit_old_password_hash, 
                    judge_audit_changed_by
                ) VALUES (
                    'DELETE', 
                    OLD.judge_id, 
                    OLD.judge_username, 
                    OLD.judge_display_name, 
                    OLD.judge_password_hash, 
                    CURRENT_USER()
                )");
    echo "Judges triggers created successfully<br>";
    
    // Create triggers for participants table
    $pdo->exec("DROP TRIGGER IF EXISTS participants_after_insert");
    $pdo->exec("CREATE TRIGGER participants_after_insert AFTER INSERT ON participants FOR EACH ROW 
                INSERT INTO participants_audit (
                    participant_audit_operation_type, 
                    participant_audit_participant_id, 
                    participant_audit_new_name, 
                    participant_audit_new_category, 
                    participant_audit_changed_by
                ) VALUES (
                    'INSERT', 
                    NEW.participant_id, 
                    NEW.participant_name, 
                    NEW.participant_category, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS participants_after_update");
    $pdo->exec("CREATE TRIGGER participants_after_update AFTER UPDATE ON participants FOR EACH ROW 
                INSERT INTO participants_audit (
                    participant_audit_operation_type, 
                    participant_audit_participant_id, 
                    participant_audit_old_name, 
                    participant_audit_new_name, 
                    participant_audit_old_category, 
                    participant_audit_new_category, 
                    participant_audit_changed_by
                ) VALUES (
                    'UPDATE', 
                    NEW.participant_id, 
                    OLD.participant_name, 
                    NEW.participant_name, 
                    OLD.participant_category, 
                    NEW.participant_category, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS participants_after_delete");
    $pdo->exec("CREATE TRIGGER participants_after_delete AFTER DELETE ON participants FOR EACH ROW 
                INSERT INTO participants_audit (
                    participant_audit_operation_type, 
                    participant_audit_participant_id, 
                    participant_audit_old_name, 
                    participant_audit_old_category, 
                    participant_audit_changed_by
                ) VALUES (
                    'DELETE', 
                    OLD.participant_id, 
                    OLD.participant_name, 
                    OLD.participant_category, 
                    CURRENT_USER()
                )");
    echo "Participants triggers created successfully<br>";
    
    // Create triggers for scores table
    $pdo->exec("DROP TRIGGER IF EXISTS scores_after_insert");
    $pdo->exec("CREATE TRIGGER scores_after_insert AFTER INSERT ON scores FOR EACH ROW 
                INSERT INTO scores_audit (
                    score_audit_operation_type, 
                    score_audit_score_id, 
                    score_audit_new_participant_id, 
                    score_audit_new_judge_id, 
                    score_audit_new_value, 
                    score_audit_changed_by
                ) VALUES (
                    'INSERT', 
                    NEW.score_id, 
                    NEW.score_participant_id, 
                    NEW.score_judge_id, 
                    NEW.score_value, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS scores_after_update");
    $pdo->exec("CREATE TRIGGER scores_after_update AFTER UPDATE ON scores FOR EACH ROW 
                INSERT INTO scores_audit (
                    score_audit_operation_type, 
                    score_audit_score_id, 
                    score_audit_old_participant_id, 
                    score_audit_new_participant_id, 
                    score_audit_old_judge_id, 
                    score_audit_new_judge_id, 
                    score_audit_old_value, 
                    score_audit_new_value, 
                    score_audit_changed_by
                ) VALUES (
                    'UPDATE', 
                    NEW.score_id, 
                    OLD.score_participant_id, 
                    NEW.score_participant_id, 
                    OLD.score_judge_id, 
                    NEW.score_judge_id, 
                    OLD.score_value, 
                    NEW.score_value, 
                    CURRENT_USER()
                )");
    
    $pdo->exec("DROP TRIGGER IF EXISTS scores_after_delete");
    $pdo->exec("CREATE TRIGGER scores_after_delete AFTER DELETE ON scores FOR EACH ROW 
                INSERT INTO scores_audit (
                    score_audit_operation_type, 
                    score_audit_score_id, 
                    score_audit_old_participant_id, 
                    score_audit_old_judge_id, 
                    score_audit_old_value, 
                    score_audit_changed_by
                ) VALUES (
                    'DELETE', 
                    OLD.score_id, 
                    OLD.score_participant_id, 
                    OLD.score_judge_id, 
                    OLD.score_value, 
                    CURRENT_USER()
                )");
    echo "Scores triggers created successfully<br>";
    
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
        $stmt->execute(['judge3', 'Michael Jack', $judgePassword]);
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
        $stmt->execute(['Neon Ninjas', 'Racing']);
        echo "Sample participants created<br>";
    }
    
    // Insert sample scores if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM scores");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // Get all judges and participants
        $judges = $pdo->query("SELECT judge_id FROM judges")->fetchAll(PDO::FETCH_COLUMN);
        $participants = $pdo->query("SELECT participant_id FROM participants")->fetchAll(PDO::FETCH_COLUMN);
        
        // Insert scores for each judge-participant combination
        $stmt = $pdo->prepare("INSERT INTO scores (score_participant_id, score_judge_id, score_value) VALUES (?, ?, ?)");
        
        foreach ($judges as $judgeId) {
            foreach ($participants as $participantId) {
                // Generate random score between 70 and 100
                $score = rand(70, 100);
                $stmt->execute([$participantId, $judgeId, $score]);
            }
        }
        echo "Sample scores created for all judge-participant combinations<br>";
    }
    
    echo "<br>Setup completed successfully! <a href='index.php'>Go to homepage</a>";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>