<?php
session_start();
require_once '../includes/dbConnection.php';

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$showChangePasswordModal = false;
$defaultPassword = 'P%ssw2rd'; // Change this to your actual default password

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['change_password'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'CSRF token validation failed';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Username and password are required';
        } else {
            // Check if judge exists using correct column names
            $stmt = $pdo->prepare("SELECT judge_id, judge_username, judge_display_name, judge_password_hash FROM judges WHERE judge_username = ?");
            $stmt->execute([$username]);
            $judge = $stmt->fetch();

            if ($judge) {
                // Check if the password matches the default password or the hashed password
                if ($password === $defaultPassword || password_verify($password, $judge['judge_password_hash'])) {
                    if ($password === $defaultPassword) {
                        // Show change password modal
                        $showChangePasswordModal = true;
                        $_SESSION['temp_judge_id'] = $judge['judge_id'];
                        $_SESSION['temp_judge_username'] = $judge['judge_username'];
                        $_SESSION['temp_judge_display_name'] = $judge['judge_display_name'];
                    } else {
                        // Login successful
                        $_SESSION['judge_logged_in'] = true;
                        $_SESSION['judge_id'] = $judge['judge_id'];
                        $_SESSION['judge_username'] = $judge['judge_username'];
                        $_SESSION['judge_display_name'] = $judge['judge_display_name'];
                        
                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);
                        
                        header("Location: ../judges/dashboard.php");
                        exit;
                    }
                } else {
                    $error = 'Invalid password';
                }
            } else {
                $error = 'Invalid judge username';
            }
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Both password fields are required';
        $showChangePasswordModal = true;
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
        $showChangePasswordModal = true;
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters long';
        $showChangePasswordModal = true;
    } elseif ($newPassword === $defaultPassword) {
        $error = 'New password cannot be the default password';
        $showChangePasswordModal = true;
    } else {
        // Update password in database with new hash using correct column names
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE judges SET judge_password_hash = ? WHERE judge_id = ?");
        $stmt->execute([$hashedPassword, $_SESSION['temp_judge_id']]);
        
        // Complete login
        $_SESSION['judge_logged_in'] = true;
        $_SESSION['judge_id'] = $_SESSION['temp_judge_id'];
        $_SESSION['judge_username'] = $_SESSION['temp_judge_username'];
        $_SESSION['judge_display_name'] = $_SESSION['temp_judge_display_name'];
        
        // Clean up temp session
        unset($_SESSION['temp_judge_id']);
        unset($_SESSION['temp_judge_username']);
        unset($_SESSION['temp_judge_display_name']);
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        header("Location: ../judges/dashboard.php");
        exit;
    }
}

$pageTitle = 'Judge Login - Gaming Championship';
$bodyClass = 'judge-login-page';
include '../includes/header.php';
?>

<div class="login-container">
    <div class="login-card judge-card">
        <div class="card-header">
            <h2 class="card-title">Judge Portal</h2>
            <p class="card-description">Enter your judge credentials to access the scoring system</p>
        </div>
        <div class="card-content">
            <?php if ($error && !$showChangePasswordModal): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="username">Judge Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="judge1" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon password-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="button cyan-button">
                    <i class="fas fa-gamepad"></i> Enter Judging Portal
                </button>
            </form>
        </div>
        <div class="card-footer">
            <a href="/" class="back-link">Back to Home</a>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<?php if ($showChangePasswordModal): ?>
<div class="modal-overlay active" id="passwordModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Default Password</h3>
            <p>For security reasons, please change your default password</p>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </div>
        <div class="modal-body">
            <form method="POST" action="login.php" id="changePasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="change_password" value="1">
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-with-icon password-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                        <button type="button" class="toggle-password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon password-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        <button type="button" class="toggle-password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="button cyan-button">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.password-field {
    position: relative;
}
.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(131, 78, 145, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    display: none;
}
.modal-overlay.active {
    display: flex;
}
.modal-content {
    background: white;
    padding: 20px;
    border-radius: 5px;
    width: 100%;
    max-width: 400px;
}
.modal-actions {
    margin-top: 20px;
    text-align: right;
}
</style>

<?php 
$scripts = ['/assets/js/judges.js'];
include '../includes/footer.php'; 
?>