<?php
session_start();
require "../../../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$walletId = $data['id'] ?? null;

if (!$walletId) {
    echo json_encode(['success' => false, 'error' => 'Valid ID required']);
    exit;
}

// Security: Ensure wallet belongs to user
try {
    $conn->beginTransaction();

    // Delete transactions first (Foreign key constraints usually require this, or CASCADE)
    $stmt1 = $conn->prepare("DELETE FROM transactions WHERE wallet_id = ? AND user_id = ?");
    $stmt1->execute([$walletId, $_SESSION['user_id']]);

    // Delete wallet
    $stmt2 = $conn->prepare("DELETE FROM wallets WHERE id = ? AND user_id = ?");
    $stmt2->execute([$walletId, $_SESSION['user_id']]);

    if ($stmt2->rowCount() > 0) {
        $conn->commit();
        echo json_encode(['success' => true]);
    } else {
        $conn->rollBack();
        echo json_encode(['success' => false, 'error' => 'Wallet not found']);
    }

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
