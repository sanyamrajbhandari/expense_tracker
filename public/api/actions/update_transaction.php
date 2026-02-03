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

//To Support both JSON and FormData
$input = $_POST;
if (empty($input)) {
    $input = get_json_input();
}

$id = $input['id'] ?? null;
$title = trim($input['title'] ?? '');
$amount = $input['amount'] ?? null;
$category = $input['category'] ?? null;
$wallet_id = $input['wallet_id'] ?? null;

if (!$id || $title === '' || $amount <= 0 || !$category || !$wallet_id) {
    echo json_encode(['success' => false, 'error' => e('Invalid input data')]);
    exit;
}

// Starting transaction
$conn->beginTransaction();

try {
    //Fetching old transaction details
    $oldStmt = $conn->prepare("SELECT amount, type, wallet_id FROM transactions WHERE id = ? AND user_id = ? FOR UPDATE");
    $oldStmt->execute([$id, $_SESSION['user_id']]);
    $oldTxn = $oldStmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldTxn) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'error' => e('Transaction not found')]);
        exit;
    }

    //Reverting old balance effect on old wallet
    $revertAdjustment = ($oldTxn['type'] === 'expense') ? $oldTxn['amount'] : -$oldTxn['amount'];
    $revertStmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ? AND user_id = ?");
    $revertStmt->execute([$revertAdjustment, $oldTxn['wallet_id'], $_SESSION['user_id']]);

    //Updating the transaction
    $updateSql = "UPDATE transactions SET title = ?, amount = ?, category = ?, wallet_id = ? WHERE id = ? AND user_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute([$title, $amount, $category, $wallet_id, $id, $_SESSION['user_id']]);

    //Applying new balance effect on new wallet
    $newAdjustment = ($oldTxn['type'] === 'expense') ? -$amount : $amount;
    $applyStmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ? AND user_id = ?");
    $applyStmt->execute([$newAdjustment, $wallet_id, $_SESSION['user_id']]);

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => e('Update failed: ' . $e->getMessage())]);
}
