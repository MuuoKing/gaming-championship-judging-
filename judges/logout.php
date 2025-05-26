<?php
session_start();

// Clear judge session variables
unset($_SESSION['judge_logged_in']);
unset($_SESSION['judge_id']);
unset($_SESSION['judge_username']);
unset($_SESSION['judge_display_name']);

// Redirect to login page
header("Location: ../index.php");
exit;
