<?php
session_start();
require_once '../includes/dbConnection.php';

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'CSRF token validation failed';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Username and password are required';
        } else {
            // Check credentials 
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['admin_password'])) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['admin_username'];
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid credentials';
            }
        }
    }
}

$pageTitle = 'Admin Login - Gaming Championship';
$bodyClass = 'admin-login-page';
include '../includes/header.php';
?>



<div class="login-container">
    <div class="login-card">
        <div class="card-header">
            <h2 class="card-title">Admin Login</h2>
            <p class="card-description">Enter your credentials to access the admin panel</p>
        </div>
        <div class="card-content">
            <?php if ($error): ?>
                <div class="error-message">Confirm your credentials</div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="admin" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                
                <button type="submit" class="button purple-button">Login</button>
            </form>
        </div>
        <div class="card-footer">
            <a href="/" class="back-link">Back to Home</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
