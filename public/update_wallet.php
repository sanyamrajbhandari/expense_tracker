<?php
session_start();
require "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents("php://input"), true);
}

$id = $input['id'] ?? null;
$name = $input['name'] ?? null;
// Balance updates usually happen via transactions, but editing name is common. 
// If user wants to manually adjust balance, we can allow it.
$balance = $input['balance'] ?? null; 

if (!$id || !$name) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$sql = "UPDATE wallets SET name = ?";
$params = [$name];

if ($balance !== null) {
    $sql .= ", balance = ?";
    $params[] = $balance;
}

$sql .= " WHERE id = ? AND user_id = ?";
$params[] = $id;
$params[] = $_SESSION['user_id'];

$stmt = $conn->prepare($sql);
$stmt->execute($params);

echo json_encode(['success' => true]);
