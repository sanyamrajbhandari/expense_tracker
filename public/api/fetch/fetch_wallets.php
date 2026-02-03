<?php
session_start();
require "../../../config/db.php";
require_once "../../../includes/security.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$walletId = $_GET['id'] ?? null;

// Fetching all wallets for the user
try {
    $walletsStmt = $conn->prepare("SELECT id, name, balance, created_at FROM wallets WHERE user_id = ?");
    $walletsStmt->execute([$userId]);
    $wallets = $walletsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Pre-escape wallet names
    foreach ($wallets as &$w) {
        $w['name'] = e($w['name']); //using e() from security.php for htmlspecialchars
    }
    unset($w);

    //Determine selected wallet (passed ID or first one)
    $selectedWallet = null;
    
    // Default to strict numeric comparison if ID is provided
    if ($walletId !== null && is_numeric($walletId)) {
        foreach ($wallets as $w) {
            if ($w['id'] == $walletId) {
                $selectedWallet = $w;
                break;
            }
        }
    } 
    
    if (!$selectedWallet && $walletId === null && !empty($wallets)) {
        $selectedWallet = $wallets[0];
    }

    //Fetching transactions for selected wallet (if any)
    $transactions = [];
    if ($selectedWallet) {
        $txnStmt = $conn->prepare("
            SELECT title, amount, type, transaction_datetime 
            FROM transactions 
            WHERE user_id = ? AND wallet_id = ? 
            ORDER BY transaction_datetime DESC 
            LIMIT 50
        ");
        $txnStmt->execute([$userId, $selectedWallet['id']]);
        $transactions = $txnStmt->fetchAll(PDO::FETCH_ASSOC);
        
        //Pre-escaping transaction titles
        foreach ($transactions as &$txn) {
            $txn['title'] = e($txn['title']);
            $txn['type'] = e($txn['type']); // Though enum-like, good practice
        }
        unset($txn);
    }

    echo json_encode([
        'success' => true,
        'wallets' => $wallets,
        'selected_wallet' => $selectedWallet,
        'transactions' => $transactions
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => e($e->getMessage())]);
}
