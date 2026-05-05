<?php
session_start();
include '../config/database.php';
include '../includes/csrf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function redirectAccountDelete($type, $message)
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
    redirectAccountDelete('error', 'Sesi form tidak valid. Silakan coba lagi.');
}

$user_id = (int) $_SESSION['user_id'];
$accountId = (int) ($_POST['account_id'] ?? 0);

if ($accountId <= 0) {
    redirectAccountDelete('error', 'Sumber dana tidak valid.');
}

try {
    $stmt = mysqli_prepare($conn, "DELETE FROM accounts WHERE account_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $accountId, $user_id);
    mysqli_stmt_execute($stmt);

    redirectAccountDelete('success', 'Sumber dana berhasil dihapus.');
} catch (Throwable $error) {
    redirectAccountDelete('error', 'Gagal menghapus sumber dana. Pastikan foreign key transactions.account_id memakai ON DELETE SET NULL.');
}
