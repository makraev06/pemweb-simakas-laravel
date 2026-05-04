<?php
session_start();
include '../config/database.php';

function redirectAssetError($message)
{
    $_SESSION['form_error'] = $message;
    header("Location: ../add_asset.php");
    exit;
}

function isValidAssetDate($date)
{
    $parsedDate = DateTime::createFromFormat('Y-m-d', $date);
    return $parsedDate && $parsedDate->format('Y-m-d') === $date;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../add_asset.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$allowedAssetCategories = [
    'Properti',
    'Kendaraan',
    'Emas',
    'Tabungan',
    'Elektronik',
    'Peralatan',
    'Investasi',
    'Lainnya',
];

$user_id = (int) $_SESSION['user_id'];
$nama_aset = trim($_POST['nama_aset'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$nilai = $_POST['nilai'] ?? '';
$deskripsi = trim($_POST['deskripsi'] ?? ($_POST['keterangan'] ?? ''));
$tanggal_perolehan = $_POST['tanggal_perolehan'] ?? ($_POST['tanggal'] ?? '');

if ($nama_aset === '') {
    redirectAssetError('Nama aset wajib diisi.');
}

if (!in_array($kategori, $allowedAssetCategories, true)) {
    redirectAssetError('Kategori aset tidak valid.');
}

if (!is_numeric($nilai) || (float) $nilai <= 0) {
    redirectAssetError('Nilai aset harus berupa angka lebih dari 0.');
}

if ($deskripsi === '') {
    redirectAssetError('Keterangan aset wajib diisi.');
}

if (!isValidAssetDate($tanggal_perolehan)) {
    redirectAssetError('Tanggal perolehan aset tidak valid.');
}

$nilai = (float) $nilai;

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO assets (user_id, nama_aset, kategori, nilai, deskripsi, tanggal_perolehan)
     VALUES (?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "issdss",
    $user_id,
    $nama_aset,
    $kategori,
    $nilai,
    $deskripsi,
    $tanggal_perolehan
);

mysqli_stmt_execute($stmt);

header("Location: ../assets.php");
exit;
