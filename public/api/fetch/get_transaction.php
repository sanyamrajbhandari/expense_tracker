<?php
session_start();
require "../../../config/db.php";
require_once "../../../includes/security.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID required']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$txn = $stmt->fetch(PDO::FETCH_ASSOC);

if ($txn) {
    $txn['title'] = e($txn['title']);
    $txn['category'] = e($txn['category']);
    $txn['type'] = e($txn['type']);
    echo json_encode(['success' => true, 'transaction' => $txn]);
} else {
    echo json_encode(['success' => false, 'error' => e('Not found')]);
}
