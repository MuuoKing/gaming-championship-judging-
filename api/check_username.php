<?php
require_once '../includes/dbConnection.php';
header('Content-Type: application/json');

if (!isset($_GET['username'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Username parameter is required']);
    exit;
}

$username = $_GET['username'];
$stmt = $pdo->prepare("SELECT judge_id FROM judges WHERE judge_username = ?");
$stmt->execute([$username]);

echo json_encode(['exists' => $stmt->rowCount() > 0]);