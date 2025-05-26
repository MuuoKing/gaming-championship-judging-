<?php
session_start();
require_once '../includes/dbConnection.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get scoring method from request
    $scoreType = $_GET['type'] ?? 'accumulated';
    $validTypes = ['accumulated', 'average'];
    if (!in_array($scoreType, $validTypes)) {
        $scoreType = 'accumulated';
    }

    // Get all participants with their scores based on selected type
    if ($scoreType === 'accumulated') {
        $stmt = $pdo->query("
            SELECT p.participant_id, p.participant_name, p.participant_category,
                   COALESCE(SUM(s.score_value), 0) as total_points,
                   COUNT(s.score_value) as judge_count,
                   COALESCE(AVG(s.score_value), 0) as average_score
            FROM participants p
            LEFT JOIN scores s ON p.participant_id = s.score_participant_id
            GROUP BY p.participant_id, p.participant_name, p.participant_category
            ORDER BY total_points DESC, average_score DESC
        ");
    } else {
        $stmt = $pdo->query("
            SELECT p.participant_id, p.participant_name, p.participant_category,
                   COALESCE(AVG(s.score_value), 0) as total_points,
                   COUNT(s.score_value) as judge_count,
                   COALESCE(SUM(s.score_value), 0) as accumulated_score
            FROM participants p
            LEFT JOIN scores s ON p.participant_id = s.score_participant_id
            GROUP BY p.participant_id, p.participant_name, p.participant_category
            ORDER BY total_points DESC
        ");
    }
    $participants = $stmt->fetchAll();

    // Format the data and get judge scores for each participant
    $formattedParticipants = [];
    foreach ($participants as $participant) {
        $displayScore = $scoreType === 'accumulated' ? 
            $participant['total_points'] : 
            round($participant['total_points'], 1);

        // Get judge scores for this participant
        $judgeStmt = $pdo->prepare("
            SELECT j.judge_display_name, j.judge_username, s.score_value, s.score_created_at
            FROM scores s
            JOIN judges j ON s.score_judge_id = j.judge_id
            WHERE s.score_participant_id = ?
            ORDER BY s.score_created_at DESC
        ");
        $judgeStmt->execute([$participant['participant_id']]);
        $judgeScores = $judgeStmt->fetchAll();
            
        $formattedParticipants[] = [
            'id' => $participant['participant_id'],
            'name' => $participant['participant_name'],
            'category' => $participant['participant_category'],
            'totalPoints' => $displayScore,
            'judgeCount' => $participant['judge_count'],
            'averageScore' => $scoreType === 'accumulated' ? 
                round($participant['total_points'] / max(1, $participant['judge_count']), 1) : 
                round($participant['total_points'], 1),
            'accumulatedScore' => $scoreType === 'average' ? 
                $participant['accumulated_score'] : 
                $participant['total_points'],
            'judgeScores' => $judgeScores
        ];
    }

    echo json_encode([
        'success' => true,
        'participants' => $formattedParticipants,
        'scoreType' => $scoreType
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}