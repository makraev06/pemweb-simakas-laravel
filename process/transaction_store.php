<?php
session_start();
include '../config/database.php';
include '../includes/csrf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function redirectWithError($message)
{
    $_SESSION['form_error'] = $message;
    header("Location: ../add_transaction.php");
    exit;
}

function isValidDate($date)
{
    $parsedDate = DateTime::createFromFormat('Y-m-d', $date);
    return $parsedDate && $parsedDate->format('Y-m-d') === $date;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../add_transaction.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? '')) {
    redirectWithError('Sesi form tidak valid. Silakan coba lagi.');
}

$allowedJenis = ['income', 'expense'];
$allowedCategoriesByJenis = [
    'income' => ['Gaji', 'Bonus', 'Penjualan', 'Investasi', 'Hadiah', 'Lainnya'],
    'expense' => ['Makanan', 'Transportasi', 'Tagihan', 'Belanja', 'Investasi', 'Pembelian Aset', 'Lainnya'],
];
$allowedAssetTypes = ['Kendaraan', 'Elektronik', 'Properti', 'Peralatan', 'Investasi', 'Lainnya'];

$user_id = (int) $_SESSION['user_id'];
$jenis = $_POST['jenis'] ?? '';
$category = trim($_POST['category'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$tanggal = $_POST['tanggal'] ?? '';
$account_id = (int) ($_POST['account_id'] ?? 0);

if (!in_array($jenis, $allowedJenis, true)) {
    redirectWithError('Jenis transaksi tidak valid.');
}

if (!in_array($category, $allowedCategoriesByJenis[$jenis], true)) {
    redirectWithError('Kategori transaksi tidak valid.');
}

if ($keterangan === '') {
    redirectWithError('Keterangan transaksi wajib diisi.');
}

if (!isValidDate($tanggal)) {
    redirectWithError('Tanggal transaksi tidak valid.');
}

if ($account_id <= 0) {
    redirectWithError('Sumber dana wajib dipilih.');
}

$isAssetPurchase = $category === 'Pembelian Aset';
$assetName = null;
$assetType = null;
$assetValue = null;

if ($isAssetPurchase) {
    if ($jenis !== 'expense') {
        redirectWithError('Kategori Pembelian Aset hanya bisa digunakan untuk transaksi Dana Keluar.');
    }

    $assetName = trim($_POST['asset_name'] ?? '');
    $assetType = trim($_POST['asset_type'] ?? '');
    $assetValue = $_POST['asset_value'] ?? '';

    if ($assetName === '') {
        redirectWithError('Nama aset wajib diisi untuk kategori Pembelian Aset.');
    }

    if (!in_array($assetType, $allowedAssetTypes, true)) {
        redirectWithError('Jenis aset tidak valid.');
    }

    if (!is_numeric($assetValue) || (float) $assetValue <= 0) {
        redirectWithError('Nilai aset harus berupa angka lebih dari 0.');
    }

    $assetValue = (float) $assetValue;
    $jumlah = $assetValue;
} else {
    $jumlah = $_POST['jumlah'] ?? '';

    if (!is_numeric($jumlah) || (float) $jumlah <= 0) {
        redirectWithError('Jumlah transaksi harus berupa angka lebih dari 0.');
    }

    $jumlah = (float) $jumlah;
}

$transactionStarted = false;

try {
    mysqli_begin_transaction($conn);
    $transactionStarted = true;

    $stmtAccount = mysqli_prepare(
        $conn,
        "SELECT account_id, balance
         FROM accounts
         WHERE account_id = ? AND user_id = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmtAccount, "ii", $account_id, $user_id);
    mysqli_stmt_execute($stmtAccount);
    $account = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAccount));

    if (!$account) {
        throw new RuntimeException('invalid_account');
    }

    $currentBalance = (float) $account['balance'];

    if ($jenis === 'expense' && $currentBalance < $jumlah) {
        throw new RuntimeException('insufficient_balance');
    }

    $stmtTransaction = mysqli_prepare(
        $conn,
        "INSERT INTO transactions (user_id, jenis, jumlah, category, keterangan, tanggal, account_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmtTransaction, "isdsssi", $user_id, $jenis, $jumlah, $category, $keterangan, $tanggal, $account_id);
    mysqli_stmt_execute($stmtTransaction);

    $transactionId = mysqli_insert_id($conn);

    $balanceChange = $jenis === 'income' ? $jumlah : -$jumlah;
    $stmtBalance = mysqli_prepare(
        $conn,
        "UPDATE accounts
         SET balance = balance + ?
         WHERE account_id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param($stmtBalance, "dii", $balanceChange, $account_id, $user_id);
    mysqli_stmt_execute($stmtBalance);

    if ($isAssetPurchase) {
        $assetDescription = "Dibuat otomatis dari transaksi: " . $keterangan;

        $stmtAsset = mysqli_prepare(
            $conn,
            "INSERT INTO assets (user_id, nama_aset, kategori, nilai, deskripsi, tanggal_perolehan, transaction_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmtAsset,
            "issdssi",
            $user_id,
            $assetName,
            $assetType,
            $assetValue,
            $assetDescription,
            $tanggal,
            $transactionId
        );
        mysqli_stmt_execute($stmtAsset);
    }

    mysqli_commit($conn);

    header("Location: ../transaction.php");
    exit;
} catch (Throwable $error) {
    if ($transactionStarted) {
        mysqli_rollback($conn);
    }

    if ($error->getMessage() === 'insufficient_balance') {
        redirectWithError('Saldo sumber dana tidak mencukupi.');
    }

    if ($error->getMessage() === 'invalid_account') {
        redirectWithError('Sumber dana tidak valid.');
    }

    redirectWithError('Gagal menyimpan transaksi. Pastikan migration database sudah dijalankan.');
}
