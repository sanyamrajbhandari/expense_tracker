<?php
session_start();
require "../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

// Get selected month (YYYY-MM), fallback to current
$month = $_GET['month'] ?? date('Y-m');

// 1️⃣ Fetch available months
$monthsStmt = $conn->prepare("
    SELECT DISTINCT DATE_FORMAT(transaction_datetime, '%Y-%m') AS month
    FROM transactions
    WHERE user_id = ?
    ORDER BY month DESC
");
$monthsStmt->execute([$userId]);
$months = $monthsStmt->fetchAll(PDO::FETCH_COLUMN);

// 2️⃣ Fetch transactions for selected month
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

// 3️⃣ Group by DATE (like your dashboard)
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
