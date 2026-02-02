<?php
session_start();
require "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

/* Fetch wallets */
$walletStmt = $conn->prepare(
    "SELECT id, name, balance 
     FROM wallets 
     WHERE user_id = ?"
);
$walletStmt->execute([$userId]);
$wallets = $walletStmt->fetchAll(PDO::FETCH_ASSOC);

/* Fetch transactions */
$txnStmt = $conn->prepare(
    "SELECT 
    t.id,
    t.title,
    t.amount,
    t.type,
    t.transaction_datetime,
    w.name AS wallet_name
FROM transactions t
JOIN wallets w ON t.wallet_id = w.id
WHERE t.user_id = ?
ORDER BY t.transaction_datetime DESC
"
);
$txnStmt->execute([$userId]);
$transactions = $txnStmt->fetchAll(PDO::FETCH_ASSOC);

/* Group transactions by date */
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
