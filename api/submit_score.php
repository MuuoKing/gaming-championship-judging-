<?php
session_start();
require_once '../includes/dbConnection.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if judge is logged in
if (!isset($_SESSION['judge_logged_in']) || $_SESSION['judge_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit;
}

// Get and validate input
$participantId = $_POST['participant_id'] ?? null;
$judgeId = $_POST['judge_id'] ?? null;
$score = $_POST['score'] ?? null;

if (!$participantId || !$judgeId || !is_numeric($score) || $score < 0 || $score > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    // Check if a score already exists for this judge and participant
    $stmt = $pdo->prepare("
        SELECT score_id FROM scores 
        WHERE score_judge_id = ? AND score_participant_id = ?
    ");
    $stmt->execute([$judgeId, $participantId]);
    $existingScore = $stmt->fetch();

    if ($existingScore) {
        // Update existing score
        $stmt = $pdo->prepare("
            UPDATE scores 
            SET score_value = ?, score_updated_at = NOW() 
            WHERE score_judge_id = ? AND score_participant_id = ?
        ");
        $stmt->execute([$score, $judgeId, $participantId]);
    } else {
        // Insert new score
        $stmt = $pdo->prepare("
            INSERT INTO scores (score_participant_id, score_judge_id, score_value) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$participantId, $judgeId, $score]);
    }

    // Get updated total score for the participant
    $totalScore = getParticipantTotalScore($pdo, $participantId);

    echo json_encode([
        'success' => true, 
        'message' => 'Score submitted successfully',
        'totalScore' => $totalScore
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}