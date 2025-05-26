<?php
session_start();
// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Championship - Judging System</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="home-page">
    <div class="container">
        <div class="hero-section">
            <div class="animate-pulse">
                <i class="fas fa-trophy trophy-icon"></i>
            </div>
            <h1 class="title">GAMING CHAMPIONSHIP</h1>
            <p class="subtitle">The ultimate gaming competition judging system</p>

            <div class="card-grid">
                <div class="card admin-card">
                    <div class="card-header">
                        <h2 class="card-title">Admin Panel</h2>
                        <p class="card-description">Manage judges and participants</p>
                    </div>
                    <div class="card-content">
                        <i class="fas fa-users icon"></i>
                        <a href="../admin/login.php" class="button purple-button">Admin Login</a>
                    </div>
                </div>

                <div class="card judge-card">
                    <div class="card-header">
                        <h2 class="card-title">Judge Portal</h2>
                        <p class="card-description">Score participants and review entries</p>
                    </div>
                    <div class="card-content">
                        <i class="fas fa-gamepad icon"></i>
                        <a href="../judges/login.php" class="button cyan-button">Judge Login</a>
                    </div>
                </div>

                <div class="card scoreboard-card">
                    <div class="card-header">
                        <h2 class="card-title">Scoreboard</h2>
                        <p class="card-description">View live competition results</p>
                    </div>
                    <div class="card-content">
                        <i class="fas fa-trophy icon"></i>
                        <a href="scoreboard.php" class="button yellow-button">View Scoreboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
