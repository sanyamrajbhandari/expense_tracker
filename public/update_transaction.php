<?php
session_start();
require "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Support both JSON and FormData
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents("php://input"), true);
}

$id = $input['id'] ?? null;
$title = $input['title'] ?? null;
$amount = $input['amount'] ?? null;
$category = $input['category'] ?? null;
// $date = $input['date'] ?? null; // Optional update
// $wallet_id = $input['wallet_id'] ?? null; // Optional update

if (!$id || !$title || !$amount || !$category) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Update transaction
$sql = "UPDATE transactions SET title = ?, amount = ?, category = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$title, $amount, $category, $id, $_SESSION['user_id']]);

if ($stmt->rowCount() >= 0) { // >= 0 because it might be successful even if no rows changed (same values)
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Update failed']);
}
