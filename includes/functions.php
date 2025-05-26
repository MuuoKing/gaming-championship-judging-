<?php
// CSRF token validation
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die("CSRF token validation failed");
    }
    return true;
}

// Get participant total score
function getParticipantTotalScore($pdo, $participantId) {
    $stmt = $pdo->prepare("
        SELECT AVG(score_value) as avg_score 
        FROM scores 
        WHERE score_participant_id = ?
    ");
    $stmt->execute([$participantId]);
    $result = $stmt->fetch();
    return round($result['avg_score'] ?? 0);
}

// Update all participant scores
function updateAllParticipantScores($pdo) {
    $stmt = $pdo->query("SELECT participant_id FROM participants");
    $participants = $stmt->fetchAll();
    
    foreach ($participants as $participant) {
        $score = getParticipantTotalScore($pdo, $participant['participant_id']);
        
    }
}

// Check if user is logged in
function isLoggedIn($role) {
    if (!isset($_SESSION[$role . '_logged_in']) || $_SESSION[$role . '_logged_in'] !== true) {
        header("Location: index.php");
        exit;
    }
    return true;
}

// Sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}