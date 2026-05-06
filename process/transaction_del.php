<?php
session_start();
include '../config/database.php';
include '../includes/csrf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function redirectTransactionDelete($message = '')
{
    if ($message !== '') {
        $_SESSION['form_error'] = $message;
    }

    header("Location: ../transaction.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTransactionDelete();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? '')) {
    redirectTransactionDelete('Sesi form tidak valid. Silakan coba lagi.');
}

$user_id = (int) $_SESSION['user_id'];
$transactionId = (int) ($_POST['id'] ?? 0);

if ($transactionId <= 0) {
    redirectTransactionDelete('Transaksi tidak valid.');
}

$transactionStarted = false;

try {
    mysqli_begin_transaction($conn);
    $transactionStarted = true;

    $stmtTransaction = mysqli_prepare(
        $conn,
        "SELECT id, jenis, jumlah, account_id
         FROM transactions
         WHERE id = ? AND user_id = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmtTransaction, "ii", $transactionId, $user_id);
    mysqli_stmt_execute($stmtTransaction);
    $transaction = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTransaction));

    if (!$transaction) {
        throw new RuntimeException('invalid_transaction');
    }

    if (!empty($transaction['account_id'])) {
        $accountId = (int) $transaction['account_id'];
        $amount = (float) $transaction['jumlah'];
        $balanceChange = $transaction['jenis'] === 'income' ? -$amount : $amount;

        $stmtAccount = mysqli_prepare(
            $conn,
            "UPDATE accounts
             SET balance = balance + ?
             WHERE account_id = ? AND user_id = ?"
        );
        mysqli_stmt_bind_param($stmtAccount, "dii", $balanceChange, $accountId, $user_id);
        mysqli_stmt_execute($stmtAccount);
    }

    $stmtAsset = mysqli_prepare(
        $conn,
        "SELECT id FROM assets WHERE transaction_id = ? AND user_id = ? FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmtAsset, "ii", $transactionId, $user_id);
    mysqli_stmt_execute($stmtAsset);
    $assetResult = mysqli_stmt_get_result($stmtAsset);
    $asset = mysqli_fetch_assoc($assetResult);

    if ($asset) {
        $stmtDeleteAsset = mysqli_prepare(
            $conn,
            "DELETE FROM assets WHERE id = ? AND user_id = ?"
        );
        mysqli_stmt_bind_param($stmtDeleteAsset, "ii", $asset['id'], $user_id);
        mysqli_stmt_execute($stmtDeleteAsset);
    }

    $stmtDelete = mysqli_prepare($conn, "DELETE FROM transactions WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmtDelete, "ii", $transactionId, $user_id);
    mysqli_stmt_execute($stmtDelete);

    mysqli_commit($conn);
    header("Location: ../transaction.php");
    exit;
} catch (Throwable $error) {
    if ($transactionStarted) {
        mysqli_rollback($conn);
    }

    if ($error->getMessage() === 'invalid_transaction') {
        redirectTransactionDelete('Transaksi tidak ditemukan.');
    }

    redirectTransactionDelete('Gagal menghapus transaksi.');
}
