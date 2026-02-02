<?php
session_start();
require "../../../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$userId = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$balance = floatval($_POST['balance'] ?? 0);

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'Wallet name is required']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO wallets (user_id, name, balance, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userId, $name, $balance]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
