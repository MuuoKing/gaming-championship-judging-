<?php
session_start();
require_once '../includes/dbConnection.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    $since = $_GET['since'] ?? 0;
    $currentTime = time();
    
    // Check for new scores since the last check
    $stmt = $pdo->prepare("
        SELECT s.*, j.judge_display_name, j.judge_username, p.participant_name
        FROM scores s
        JOIN judges j ON s.score_judge_id = j.judge_id
        JOIN participants p ON s.score_participant_id = p.participant_id
        WHERE UNIX_TIMESTAMP(s.score_created_at) > ?
        ORDER BY s.score_created_at DESC
    ");
    $stmt->execute([$since]);
    $newScores = $stmt->fetchAll();
    
    $hasUpdates = count($newScores) > 0;
    
    echo json_encode([
        'success' => true,
        'hasUpdates' => $hasUpdates,
        'newScores' => $newScores,
        'timestamp' => $currentTime
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}