<?php
session_start();
require "../../../config/db.php";

header("Content-Type: application/json");

// 1. Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

// 2. Get POST data
$type     = $_POST['type'] ?? '';
$title    = trim($_POST['title'] ?? '');
$amount   = $_POST['amount'] ?? 0;
$category = $_POST['category'] ?? '';
$date     = $_POST['date'] ?? '';
$time     = $_POST['time'] ?? '';
$walletId = $_POST['wallet'] ?? '';

// 3. Basic validation
if ($title === '' || $amount <= 0 || !$walletId) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid input data"
    ]);
    exit;
}

// Combine date + time → DATETIME
$transactionDateTime = $date . ' ' . $time . ':00';

try {
    // 4. Start DB transaction
    $conn->beginTransaction();

    // 5. Insert transaction
    $insertSql = "
        INSERT INTO transactions 
        (user_id, wallet_id, type, title, category, amount, transaction_datetime)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insertSql);
    $stmt->execute([
        $userId,
        $walletId,
        $type,
        $title,
        $category,
        $amount,
        $transactionDateTime
    ]);

    // 6. Update wallet balance
    if ($type === 'expense') {
        $updateSql = "
            UPDATE wallets 
            SET balance = balance - ? 
            WHERE id = ? AND user_id = ?
        ";
    } else {
        $updateSql = "
            UPDATE wallets 
            SET balance = balance + ? 
            WHERE id = ? AND user_id = ?
        ";
    }

    $stmt = $conn->prepare($updateSql);
    $stmt->execute([$amount, $walletId, $userId]);

    // 7. Commit DB changes
    $conn->commit();

    // 8. Success response
    echo json_encode([
        "success" => true,
        "message" => "Transaction added successfully"
    ]);

} catch (Exception $e) {
    // Rollback if anything fails
    $conn->rollBack();

    echo json_encode([
        "success" => false,
        "message" => "Failed to add transaction"
    ]);
}
