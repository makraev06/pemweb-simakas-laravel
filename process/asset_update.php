<?php
session_start();
include '../config/database.php';

function redirectAssetError($message, $id)
{
    $_SESSION['form_error'] = $message;
    header("Location: ../edit_asset.php?id=" . (int) $id);
    exit;
}

function isValidAssetDate($date)
{
    $parsedDate = DateTime::createFromFormat('Y-m-d', $date);
    return $parsedDate && $parsedDate->format('Y-m-d') === $date;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../assets.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
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
$asset_id = (int) ($_POST['id'] ?? 0);
$nama_aset = trim($_POST['nama_aset'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$nilai = $_POST['nilai'] ?? '';
$deskripsi = trim($_POST['deskripsi'] ?? '');
$tanggal_perolehan = $_POST['tanggal_perolehan'] ?? '';

if ($asset_id <= 0) {
    header('Location: ../assets.php');
    exit;
}

if ($nama_aset === '') {
    redirectAssetError('Nama aset wajib diisi.', $asset_id);
}

if (!in_array($kategori, $allowedAssetCategories, true)) {
    redirectAssetError('Kategori aset tidak valid.', $asset_id);
}

if (!is_numeric($nilai) || (float) $nilai <= 0) {
    redirectAssetError('Nilai aset harus berupa angka lebih dari 0.', $asset_id);
}

if ($deskripsi === '') {
    redirectAssetError('Keterangan aset wajib diisi.', $asset_id);
}

if (!isValidAssetDate($tanggal_perolehan)) {
    redirectAssetError('Tanggal perolehan aset tidak valid.', $asset_id);
}

$nilai = (float) $nilai;

$stmt = mysqli_prepare(
    $conn,
    "UPDATE assets SET nama_aset = ?, kategori = ?, nilai = ?, deskripsi = ?, tanggal_perolehan = ? WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    'ssdssii',
    $nama_aset,
    $kategori,
    $nilai,
    $deskripsi,
    $tanggal_perolehan,
    $asset_id,
    $user_id
);

mysqli_stmt_execute($stmt);

header('Location: ../assets.php');
exit;
