<?php
session_start();
require "../../../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$walletId = $_GET['id'] ?? null;

// 1. Fetch all wallets for the user
try {
    $walletsStmt = $conn->prepare("SELECT id, name, balance, created_at FROM wallets WHERE user_id = ?");
    $walletsStmt->execute([$userId]);
    $wallets = $walletsStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Determine selected wallet (passed ID or first one)
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
    
    // If no specific logic matched or no ID passed, distinct logic:
    // User requested ID but it wasn't found -> selectedWallet remains null (client handles this)
    // User didn't request ID -> Default to first
    if (!$selectedWallet && $walletId === null && !empty($wallets)) {
        $selectedWallet = $wallets[0];
    }

    // 3. Fetch transactions for selected wallet (if any)
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
    }

    echo json_encode([
        'success' => true,
        'wallets' => $wallets,
        'selected_wallet' => $selectedWallet,
        'transactions' => $transactions
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
