<?php
session_start();
include '../config/database.php';
include '../includes/csrf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function redirectAccountUpdate($type, $message)
{
    $_SESSION['account_' . $type] = $message;
    header("Location: ../profile.php#accounts");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../profile.php#accounts");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? '')) {
    redirectAccountUpdate('error', 'Sesi form tidak valid. Silakan coba lagi.');
}

$allowedTypes = ['bank', 'ewallet', 'cash'];
$user_id = (int) $_SESSION['user_id'];
$accountId = (int) ($_POST['account_id'] ?? 0);
$accountName = trim($_POST['account_name'] ?? '');
$accountType = trim($_POST['account_type'] ?? '');
$balance = $_POST['balance'] ?? '';

if ($accountId <= 0) {
    redirectAccountUpdate('error', 'Sumber dana tidak valid.');
}

if ($accountName === '') {
    redirectAccountUpdate('error', 'Nama sumber dana wajib diisi.');
}

if (strlen($accountName) > 100) {
    redirectAccountUpdate('error', 'Nama sumber dana maksimal 100 karakter.');
}

if (!in_array($accountType, $allowedTypes, true)) {
    redirectAccountUpdate('error', 'Kategori sumber dana tidak valid.');
}

if (!is_numeric($balance) || (float) $balance < 0) {
    redirectAccountUpdate('error', 'Saldo harus berupa angka minimal 0.');
}

$balance = (float) $balance;

try {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE accounts
         SET account_name = ?, account_type = ?, balance = ?
         WHERE account_id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssdii", $accountName, $accountType, $balance, $accountId, $user_id);
    mysqli_stmt_execute($stmt);

    redirectAccountUpdate('success', 'Sumber dana berhasil diperbarui.');
} catch (Throwable $error) {
    redirectAccountUpdate('error', 'Gagal memperbarui sumber dana.');
}
