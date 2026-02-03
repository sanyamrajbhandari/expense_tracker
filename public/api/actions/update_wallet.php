<?php
session_start();
require "../../../config/db.php";
require_once "../../../includes/security.php";
verify_csrf_api();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => e('Unauthorized')]);
    exit;
}

$input = $_POST;
if (empty($input)) {
    $input = get_json_input();
}

$id = $input['id'] ?? null;
$name = trim($input['name'] ?? '');
$balance = $input['balance'] ?? null; 

if (!$id || $name === '') {
    echo json_encode(['success' => false, 'error' => e('Invalid input data')]);
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

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => e($e->getMessage())]);
}
