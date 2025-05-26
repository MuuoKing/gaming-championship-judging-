<?php
session_start();
require_once '../includes/dbConnection.php';
require_once '../includes/functions.php';

// Check if judge is logged in
isLoggedIn('judge');

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$successMessage = '';
$judgeId = $_SESSION['judge_id'];
$judgeName = $_SESSION['judge_display_name'];

// Get all participants with their scores
$stmt = $pdo->query("
    SELECT p.participant_id, 
           p.participant_name, 
           p.participant_category,
           COALESCE(AVG(s.score_value), 0) as total_points
    FROM participants p
    LEFT JOIN scores s ON p.participant_id = s.score_participant_id
    GROUP BY p.participant_id, p.participant_name, p.participant_category
    ORDER BY p.participant_id
");
$participants = $stmt->fetchAll();

// Handle score submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_score'])) {
    // This will be handled by the AJAX endpoint in api/submit_score.php
    // We keep this here for form fallback
    header("Location: portal.php");
    exit;
}

$pageTitle = 'Judge Portal - Gaming Championship';
$bodyClass = 'judge-portal-page';
include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Judge Portal</h1>
        <div class="judge-info">
            <span>Welcome, <?php echo h($judgeName); ?></span>
            <a href="logout.php" class="button outline-button">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-message"><?php echo h($successMessage); ?></div>
    <?php endif; ?>

    <div class="judge-portal-content">
        <div class="participants-list">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Participants</h2>
                    <p class="card-description">Select a participant to assign points</p>
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="participant-search" placeholder="Search participants...">
                    </div>
                </div>
                <div class="card-content">
                    <table class="data-table" id="participants-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Current Points</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($participants) > 0): ?>
                                <?php foreach ($participants as $participant): ?>
                                    <tr data-id="<?php echo h($participant['participant_id']); ?>" 
                                        data-name="<?php echo h($participant['participant_name']); ?>" 
                                        data-category="<?php echo h($participant['participant_category']); ?>" 
                                        data-points="<?php echo round($participant['total_points']); ?>">
                                        <td><?php echo h($participant['participant_id']); ?></td>
                                        <td><?php echo h($participant['participant_name']); ?></td>
                                        <td><?php echo h($participant['participant_category']); ?></td>
                                        <td><?php echo is_numeric($participant['total_points']) ? round($participant['total_points']) : 0; ?></td>
                                        <td>
                                            <button class="button small-button select-participant">Select</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">No participants found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="scoring-panel">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Assign Points</h2>
                    <p class="card-description" id="scoring-description">Select a participant first</p>
                </div>
                <div class="card-content">
                    <div id="no-participant-selected">
                        <div class="empty-state">
                            <i class="fas fa-trophy"></i>
                            <p>Select a participant from the list to assign points</p>
                        </div>
                    </div>

                    <div id="participant-scoring" style="display: none;">
                        <div class="participant-info">
                            <div class="info-row">
                                <span class="info-label">Team:</span>
                                <span class="info-value" id="selected-name"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Category:</span>
                                <span class="info-value" id="selected-category"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Current Points:</span>
                                <span class="info-value" id="selected-points"></span>
                            </div>
                        </div>

                        <form id="score-form" method="POST" action="api/submit_score.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="participant_id" id="participant-id">
                            <input type="hidden" name="judge_id" value="<?php echo $judgeId; ?>">
                            
                            <div class="form-group">
                                <label for="score">Assign Score (1-100)</label>
                                <div class="slider-container">
                                    <input type="range" id="score-slider" name="score" min="1" max="100" value="50">
                                    <div class="score-display" id="score-display">50</div>
                                </div>
                            </div>
                            
                            <button type="submit" class="button cyan-button">
                                <i class="fas fa-save"></i> Submit Score
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$scripts = ['/assets/js/judges.js'];
include '../includes/footer.php'; 
?>