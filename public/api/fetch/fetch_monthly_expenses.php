<?php
session_start();
require "../../../config/db.php";
require_once "../../../includes/security.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

//Getting selected month (YYYY-MM) or fallingback to current
$month = $_GET['month'] ?? date('Y-m');

//Fetching available months
$monthsStmt = $conn->prepare("
    SELECT DISTINCT DATE_FORMAT(transaction_datetime, '%Y-%m') AS month
    FROM transactions
    WHERE user_id = ?
    ORDER BY month DESC
");
$monthsStmt->execute([$userId]);
$months = $monthsStmt->fetchAll(PDO::FETCH_COLUMN);

//Fetching transactions for selected month
$txnStmt = $conn->prepare("
    SELECT 
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
      AND DATE_FORMAT(t.transaction_datetime, '%Y-%m') = ?
    ORDER BY t.transaction_datetime DESC
");
$txnStmt->execute([$userId, $month]);
$transactions = $txnStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($transactions as &$txn) {
    $txn['title'] = e($txn['title']);
    $txn['wallet_name'] = e($txn['wallet_name']);
    $txn['category'] = e($txn['category']);
    $txn['type'] = e($txn['type']);
}
unset($txn);

//Grouping by DATE
$grouped = [];
foreach ($transactions as $txn) {
    $dateKey = date('Y-m-d', strtotime($txn['transaction_datetime']));
    $grouped[$dateKey][] = $txn;
}

echo json_encode([
    'success' => true,
    'months' => $months,
    'transactions' => $grouped
]);
