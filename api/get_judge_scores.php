
<?php
session_start();
require_once '../includes/dbConnection.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    $participantId = $_GET['participant_id'] ?? null;
    
    if (!$participantId || !is_numeric($participantId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid participant ID']);
        exit;
    }

    // Get detailed judge scores for the participant
    $stmt = $pdo->prepare("
        SELECT j.judge_display_name, j.judge_username, s.score_value, s.score_created_at
        FROM scores s
        JOIN judges j ON s.score_judge_id = j.judge_id
        WHERE s.score_participant_id = ?
        ORDER BY s.score_created_at DESC
    ");
    $stmt->execute([$participantId]);
    $scores = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'scores' => $scores
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}