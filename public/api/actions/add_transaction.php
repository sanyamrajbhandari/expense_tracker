<?php
session_start();
require "../../../config/db.php";
require_once "../../../includes/security.php";
verify_csrf_api();

header("Content-Type: application/json");

//Checking login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => e("User not logged in")
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

//Getting POST data
$type     = $_POST['type'] ?? '';
$title    = trim($_POST['title'] ?? '');
$amount   = $_POST['amount'] ?? 0;
$category = $_POST['category'] ?? '';
$date     = $_POST['date'] ?? '';
$time     = $_POST['time'] ?? '';
$walletId = $_POST['wallet'] ?? '';

//Form validation
if ($title === '') {
    echo json_encode([
        "success" => false,
        "message" => e("Please fill the title")
    ]);
    exit;
}

if ($amount <= 0) {
    echo json_encode([
        "success" => false,
        "message" => e("Amount should be greater than 0")
    ]);
    exit;
}


if (!$walletId) {
    echo json_encode([
        "success" => false,
        "message" => e("Please select a wallet")
    ]);
    exit;
}


// Combining date and time to form DATETIME
$transactionDateTime = $date . ' ' . $time . ':00';

try {
    //Starting DB transaction for multiple queries
    $conn->beginTransaction();

    //Insert transaction query
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

    //Update transaction query
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

    //Committing the DB changes
    $conn->commit();

    //Success response
    echo json_encode([
        "success" => true,
        "message" => e("Transaction added successfully")
    ]);

} catch (Exception $e) {
    // Rollback if anything fails
    $conn->rollBack();

    echo json_encode([
        "success" => false,
        "message" => e("Failed to add transaction")
    ]);
}
