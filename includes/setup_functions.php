<?php
/**
 * Setup functions for creating demo data (MySQL)
 */

function createTablesIfNotExists($pdo) {
    try {
        // Check if tables exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
        if ($stmt->rowCount() > 0) {
            return; // Tables already exist
        }

        // Create all tables
        createMySQLTables($pdo);
        
        // Insert demo data
        insertDemoData($pdo);

    } catch (PDOException $e) {
        error_log("Table creation failed: " . $e->getMessage());
    }
}

function createMySQLTables($pdo) {
    // Create admins table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_username VARCHAR(50) NOT NULL UNIQUE,
        admin_password VARCHAR(255) NOT NULL,
        admin_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        admin_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_admin_username (admin_username)
    )");

    // Create judges table
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

    // Create participants table
    $pdo->exec("CREATE TABLE IF NOT EXISTS participants (
        participant_id INT AUTO_INCREMENT PRIMARY KEY,
        participant_name VARCHAR(100) NOT NULL,
        participant_category VARCHAR(50) NOT NULL,
        participant_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        participant_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_participant_name (participant_name),
        INDEX idx_participant_category (participant_category)
    )");

    // Create scores table
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
}

function insertDemoData($pdo) {
    try {
        // Insert default admin
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE admin_username = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (admin_username, admin_password) VALUES (?, ?)");
            $stmt->execute(['admin', $hashedPassword]);
        }

        // Insert demo judges
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM judges");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $judgePassword = password_hash('P%ssw2rd', PASSWORD_DEFAULT);
            $judges = [
                ['judge1', 'Alex Kamau'],
                ['judge2', 'Sarah Bismack'],
                ['judge3', 'Michael Chen'],
                ['judge4', 'Emma Rodriguez'],
                ['judge5', 'David Kim']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO judges (judge_username, judge_display_name, judge_password_hash) VALUES (?, ?, ?)");
            foreach ($judges as $judge) {
                $stmt->execute([$judge[0], $judge[1], $judgePassword]);
            }
        }

        // Insert demo participants
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $participants = [
                ['Team Alpha', 'FPS'],
                ['Digital Dragons', 'MOBA'],
                ['Pixel Pirates', 'Strategy'],
                ['Neon Ninjas', 'Racing'],
                ['Cyber Wolves', 'Battle Royale'],
                ['Storm Riders', 'FPS'],
                ['Phoenix Squad', 'MOBA'],
                ['Shadow Hunters', 'Strategy'],
                ['Lightning Bolts', 'Racing'],
                ['Fire Eagles', 'Battle Royale']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO participants (participant_name, participant_category) VALUES (?, ?)");
            foreach ($participants as $participant) {
                $stmt->execute([$participant[0], $participant[1]]);
            }
        }

        // Insert demo scores
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM scores");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $judges = $pdo->query("SELECT judge_id FROM judges")->fetchAll(PDO::FETCH_COLUMN);
            $participants = $pdo->query("SELECT participant_id FROM participants")->fetchAll(PDO::FETCH_COLUMN);
            
            $stmt = $pdo->prepare("INSERT INTO scores (score_participant_id, score_judge_id, score_value) VALUES (?, ?, ?)");
            
            foreach ($participants as $participantId) {
                // Each participant gets scores from 2-5 random judges
                $judgeSubset = array_rand(array_flip($judges), rand(2, min(5, count($judges))));
                if (!is_array($judgeSubset)) $judgeSubset = [$judgeSubset];
                
                foreach ($judgeSubset as $judgeId) {
                    $score = rand(65, 98); // Random score between 65-98
                    $stmt->execute([$participantId, $judgeId, $score]);
                }
            }
        }

    } catch (PDOException $e) {
        error_log("Demo data insertion failed: " . $e->getMessage());
    }
}
?>