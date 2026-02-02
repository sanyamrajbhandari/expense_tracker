<?php
session_start();
require "../../../config/db.php";
require_once "../../../includes/security.php";
verify_csrf_api();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = get_json_input();
$transactionId = $data['id'] ?? null;

if (!$transactionId) {
    echo json_encode(['success' => false, 'error' => 'Valid ID required']);
    exit;
}

// Start transaction
$conn->beginTransaction();

try {
    // 1. Fetch transaction details before deletion
    $fetchStmt = $conn->prepare("SELECT amount, type, wallet_id FROM transactions WHERE id = ? AND user_id = ? FOR UPDATE");
    $fetchStmt->execute([$transactionId, $_SESSION['user_id']]);
    $txn = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    if (!$txn) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'error' => 'Transaction not found']);
        exit;
    }

    // 2. Delete transaction
    $delStmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $delStmt->execute([$transactionId, $_SESSION['user_id']]);

    // 3. Adjust Wallet Balance
    // If it was an expense, add back to balance. If income, subtract.
    $adjustment = ($txn['type'] === 'expense') ? $txn['amount'] : -$txn['amount'];
    
    $updateWStmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ? AND user_id = ?");
    $updateWStmt->execute([$adjustment, $txn['wallet_id'], $_SESSION['user_id']]);

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
