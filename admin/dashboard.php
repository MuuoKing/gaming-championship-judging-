<?php
session_start();
require_once '../includes/dbConnection.php';
require_once '../includes/functions.php';

// Check if admin is logged in
isLoggedIn('admin');

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$successMessage = '';
$error = '';

// Handle clear all scores request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_scores'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'CSRF token validation failed';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM scores");
            $stmt->execute();
            $successMessage = 'All scores have been cleared successfully!';
        } catch (PDOException $e) {
            $error = 'Error clearing scores: ' . $e->getMessage();
        }
    }
}

// Handle add judge form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_judge'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'CSRF token validation failed';
    } else {
        $username = trim($_POST['username'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        $password = trim($_POST['password'] ?? 'P%ssw2rd');

        if (empty($username) || empty($displayName)) {
            $error = 'Username and display name are required';
        } else {
            try {
                $checkStmt = $pdo->prepare("SELECT judge_id FROM judges WHERE judge_username = ?");
                $checkStmt->execute([$username]);
                
                if ($checkStmt->rowCount() > 0) {
                    $error = 'Username "'.htmlspecialchars($username).'" already exists. Please choose a different one.';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO judges (judge_username, judge_display_name, judge_password_hash) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $displayName, $passwordHash]);
                    $successMessage = 'Judge added successfully with default password (P%ssw2rd)!';
                    
                    $_POST['username'] = '';
                    $_POST['display_name'] = '';
                }
            } catch (PDOException $e) {
                $error = 'Error adding judge: ' . $e->getMessage();
            }
        }
    }
}

// Handle add participant form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_participant'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'CSRF token validation failed';
    } else {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');

        if (empty($name) || empty($category)) {
            $error = 'All fields are required';
        } else {
            try {
                $checkStmt = $pdo->prepare("SELECT participant_id FROM participants WHERE participant_name = ? AND participant_category = ?");
                $checkStmt->execute([$name, $category]);
                
                if ($checkStmt->rowCount() > 0) {
                    $error = 'Participant "'.htmlspecialchars($name).'" already exists in category "'.htmlspecialchars($category).'"';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO participants (participant_name, participant_category) VALUES (?, ?)");
                    $stmt->execute([$name, $category]);
                    $successMessage = 'Participant added successfully!';
                    
                    $_POST['name'] = '';
                    $_POST['category'] = '';
                }
            } catch (PDOException $e) {
                $error = 'Error adding participant: ' . $e->getMessage();
            }
        }
    }
}

// Get all judges
$stmt = $pdo->query("SELECT * FROM judges ORDER BY judge_id");
$judges = $stmt->fetchAll();

// Get all participants with their scores
$stmt = $pdo->query("
    SELECT p.*, 
           COALESCE(AVG(s.score_value), 0) as total_points
    FROM participants p
    LEFT JOIN scores s ON p.participant_id = s.score_participant_id
    GROUP BY p.participant_id
    ORDER BY p.participant_id
");
$participants = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard - Gaming Championship';
$bodyClass = 'admin-dashboard-page';
include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Admin Dashboard</h1>
        <a href="logout.php" class="button outline-button">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-message"><?php echo h($successMessage); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo h($error); ?></div>
    <?php endif; ?>

    <div class="tabs">
        <div class="tab-header">
            <button class="tab-button active" data-tab="judges">
                <i class="fas fa-users"></i> Manage Judges
            </button>
            <button class="tab-button" data-tab="participants">
                <i class="fas fa-trophy"></i> Manage Participants
            </button>
        </div>

        <div class="tab-content active" id="judges-tab">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Add New Judge</h2>
                    <p class="card-description">Create credentials for a new judge to access the judging portal</p>
                </div>
                <div class="card-content">
                    <form method="POST" action="dashboard.php" id="add-judge-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="add_judge" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? h($_POST['username']) : ''; ?>" 
                                       placeholder="judge_username" required>
                                <div id="username-availability" class="availability-message"></div>
                            </div>
                            <div class="form-group">
                                <label for="display_name">Display Name</label>
                                <input type="text" id="display_name" name="display_name" 
                                       value="<?php echo isset($_POST['display_name']) ? h($_POST['display_name']) : ''; ?>" 
                                       placeholder="Judge Full Name" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" 
                                       value="P%ssw2rd" 
                                       placeholder="Judge Password" required>
                                <small class="form-text">Default password: P%ssw2rd</small>
                            </div>
                        </div>
                        
                        <button type="submit" class="button purple-button">
                            <i class="fas fa-plus"></i> Add Judge
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Current Judges</h2>
                    <p class="card-description">List of all judges with access to the judging portal</p>
                </div>
                <div class="card-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Display Name</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($judges) > 0): ?>
                                <?php foreach ($judges as $judge): ?>
                                    <tr>
                                        <td><?php echo h($judge['judge_id']); ?></td>
                                        <td><?php echo h($judge['judge_username']); ?></td>
                                        <td><?php echo h($judge['judge_display_name']); ?></td>
                                        <td><?php echo h($judge['judge_created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="no-data">No judges found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-content" id="participants-tab">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Add New Participant</h2>
                    <p class="card-description">Register a new participant for the competition</p>
                </div>
                <div class="card-content">
                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="add_participant" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Team/Participant Name</label>
                                <input type="text" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? h($_POST['name']) : ''; ?>" 
                                       placeholder="Team Name" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Game Category</label>
                                <input type="text" id="category" name="category" 
                                       value="<?php echo isset($_POST['category']) ? h($_POST['category']) : ''; ?>" 
                                       placeholder="FPS, MOBA, etc." required>
                            </div>
                        </div>
                        
                        <button type="submit" class="button purple-button">
                            <i class="fas fa-plus"></i> Add Participant
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-header-row">
                        <div>
                            <h2 class="card-title">Current Participants</h2>
                            <p class="card-description">List of all registered participants</p>
                        </div>
                        <button type="button" id="clearScoresBtn" class="button red-button">
                            <i class="fas fa-trash-alt"></i> Clear All Scores
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Total Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($participants) > 0): ?>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td><?php echo h($participant['participant_id']); ?></td>
                                        <td><?php echo h($participant['participant_name']); ?></td>
                                        <td><?php echo h($participant['participant_category']); ?></td>
                                        <td><?php echo round($participant['total_points']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="no-data">No participants found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Scores Confirmation Modal -->
    <div id="clearScoresModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Clear All Scores</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear ALL scores? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="button outline-button close-modal">Cancel</button>
                <button type="button" id="confirmClearScores" class="button red-button">Clear All Scores</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for clearing scores -->
    <form method="POST" action="dashboard.php" class="clear-scores-form" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="clear_scores" value="1">
    </form>
</div>

<?php 
$scripts = ['/assets/js/admin.js'];
include '../includes/footer.php'; 
?>