<?php
session_start();
require_once 'includes/dbConnection.php';
require_once 'includes/functions.php';

// Always use average scoring (no toggle)
$scoreType = 'average';

// Get all participants with their scores (average mode)
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
$participants = $stmt->fetchAll();

// Function to get detailed judge scores for a participant
function getJudgeScores($pdo, $participantId) {
    $stmt = $pdo->prepare("
        SELECT j.judge_display_name, j.judge_username, s.score_value, s.score_created_at
        FROM scores s
        JOIN judges j ON s.score_judge_id = j.judge_id
        WHERE s.score_participant_id = ?
        ORDER BY s.score_created_at DESC
    ");
    $stmt->execute([$participantId]);
    return $stmt->fetchAll();
}

$pageTitle = 'Live Scoreboard - Gaming Championship';
$bodyClass = 'scoreboard-page';
include 'includes/header.php';
?>

<div class="scoreboard-container">
    <div class="scoreboard-header">
        <div class="title-container">
            <i class="fas fa-trophy"></i>
            <h1 class="scoreboard-title">LIVE SCOREBOARD</h1>
            <i class="fas fa-trophy"></i>
        </div>
        <p class="scoreboard-description">
            Watch the competition unfold in real-time..
        </p>
        
        <a href="/" class="button outline-button">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>

    <div class="scoreboard-content">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Tournament Rankings</h2>
                <p class="card-description">
                    Participants ranked by average scores from all judges
                </p>
            </div>
            <div class="card-content">
                <div class="rankings" id="rankings-container">
                    <?php if (count($participants) > 0): ?>
                        <?php foreach ($participants as $index => $participant): ?>
                            <?php 
                                $medalClass = '';
                                if ($index === 0) $medalClass = 'gold';
                                else if ($index === 1) $medalClass = 'silver';
                                else if ($index === 2) $medalClass = 'bronze';
                                
                                $scoreClass = '';
                                $displayScore = round($participant['total_points'], 1);
                                $progressWidth = min(100, $displayScore);
                                
                                if ($displayScore >= 90) $scoreClass = 'score-excellent';
                                else if ($displayScore >= 80) $scoreClass = 'score-good';
                                else if ($displayScore >= 70) $scoreClass = 'score-average';
                                else $scoreClass = 'score-below-average';

                                // Get detailed judge scores for this participant
                                $judgeScores = getJudgeScores($pdo, $participant['participant_id']);
                            ?>
                            <div class="ranking-item <?php echo $medalClass; ?>">
                                <div class="ranking-content">
                                    <div class="medal">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div class="participant-info">
                                        <div class="participant-header">
                                            <div class="participant-details">
                                                <h3 class="participant-name"><?php echo h($participant['participant_name']); ?></h3>
                                                <p class="participant-category"><?php echo h($participant['participant_category']); ?></p>
                                            </div>
                                            <div class="score-container">
                                                <div class="score <?php echo $scoreClass; ?>"><?php echo $displayScore; ?></div>
                                                <div class="score-info">
                                                    <span class="judge-count"><?php echo $participant['judge_count']; ?> judge<?php echo $participant['judge_count'] !== 1 ? 's' : ''; ?></span>
                                                    <span class="total-score">Total: <?php echo $participant['accumulated_score']; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Judge Scores Breakdown -->
                                        <?php if (count($judgeScores) > 0): ?>
                                            <div class="judge-scores-breakdown">
                                                <h4 class="breakdown-title">
                                                    <i class="fas fa-gavel"></i> Judge Scores
                                                </h4>
                                                <div class="judge-scores-grid">
                                                    <?php foreach ($judgeScores as $judgeScore): ?>
                                                        <?php
                                                            $judgeScoreClass = '';
                                                            if ($judgeScore['score_value'] >= 90) $judgeScoreClass = 'judge-score-excellent';
                                                            else if ($judgeScore['score_value'] >= 80) $judgeScoreClass = 'judge-score-good';
                                                            else if ($judgeScore['score_value'] >= 70) $judgeScoreClass = 'judge-score-average';
                                                            else $judgeScoreClass = 'judge-score-below-average';
                                                        ?>
                                                        <div class="judge-score-item">
                                                            <div class="judge-info">
                                                                <span class="judge-name"><?php echo h($judgeScore['judge_display_name']); ?></span>
                                                                <span class="judge-username">@<?php echo h($judgeScore['judge_username']); ?></span>
                                                            </div>
                                                            <div class="judge-score <?php echo $judgeScoreClass; ?>">
                                                                <?php echo $judgeScore['score_value']; ?>
                                                            </div>
                                                            <div class="score-timestamp">
                                                                <?php echo date('M j, g:i A', strtotime($judgeScore['score_created_at'])); ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="no-scores">
                                                <i class="fas fa-clock"></i>
                                                <span>No scores submitted yet</span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="progress-bar">
                                            <div class="progress" style="width: <?php echo $progressWidth; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">No participants found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$scripts = ['/assets/js/scoreboard.js'];
include 'includes/footer.php'; 
?>