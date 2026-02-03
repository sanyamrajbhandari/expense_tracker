<?php
session_start();
require "../../../config/db.php";
require_once "../../../includes/security.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

// fetching wallets
$walletStmt = $conn->prepare(
    "SELECT id, name, balance 
     FROM wallets 
     WHERE user_id = ?"
);
$walletStmt->execute([$userId]);
$wallets = $walletStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($wallets as &$w) {
    $w['name'] = e($w['name']); //using e() to use htmlspecialchars for xss prevention
}
unset($w);

// fetching transactions
$txnStmt = $conn->prepare(
    "SELECT 
    t.id,
    t.title,
    t.amount,
    t.type,
    t.category,
    t.transaction_datetime,
    w.name AS wallet_name
FROM transactions t
JOIN wallets w ON t.wallet_id = w.id
WHERE t.user_id = ?
ORDER BY t.transaction_datetime DESC
LIMIT 15
"
);
$txnStmt->execute([$userId]);
$transactions = $txnStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($transactions as &$txn) {
    $txn['title'] = e($txn['title']);
    $txn['wallet_name'] = e($txn['wallet_name']);
    $txn['category'] = e($txn['category']);
    $txn['type'] = e($txn['type']);
}
unset($txn);

//the grouped transactions by date
$grouped = [];

foreach ($transactions as $txn) {
    $dateKey = date('Y-m-d', strtotime($txn['transaction_datetime']));
    $grouped[$dateKey][] = $txn;
}

echo json_encode([
    'success' => true,
    'wallets' => $wallets,
    'transactions' => $grouped
]);
