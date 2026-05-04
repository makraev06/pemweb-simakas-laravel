<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$filter_jenis = $_GET['jenis'] ?? '';
$filter_start = $_GET['start_date'] ?? '';
$filter_end = $_GET['end_date'] ?? '';

if (!in_array($filter_jenis, ['', 'income', 'expense'], true)) {
    $filter_jenis = '';
}

function isValidExportDate($date)
{
    $parsedDate = DateTime::createFromFormat('Y-m-d', $date);
    return $parsedDate && $parsedDate->format('Y-m-d') === $date;
}

if (!empty($filter_start) && !isValidExportDate($filter_start)) {
    $filter_start = '';
}

if (!empty($filter_end) && !isValidExportDate($filter_end)) {
    $filter_end = '';
}

// nama file otomatis
$filename = "laporan_transaksi_" . date('Y-m-d') . ".csv";

// header download
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$filename");

$output = fopen('php://output', 'w');

// header kolom
fputcsv($output, ['Tanggal', 'Jenis', 'Kategori', 'Jumlah', 'Keterangan']);

$conditions = ["user_id = ?"];
$types = "i";
$params = [$user_id];

// filter jenis
if (!empty($filter_jenis)) {
    $conditions[] = "jenis = ?";
    $types .= "s";
    $params[] = $filter_jenis;
}

// filter tanggal
if (!empty($filter_start)) {
    $conditions[] = "tanggal >= ?";
    $types .= "s";
    $params[] = $filter_start;
}

if (!empty($filter_end)) {
    $conditions[] = "tanggal <= ?";
    $types .= "s";
    $params[] = $filter_end;
}

$where_sql = implode(" AND ", $conditions);
$sql = "SELECT * FROM transactions WHERE $where_sql ORDER BY tanggal DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

// isi data
while ($row = mysqli_fetch_assoc($query)) {
    fputcsv($output, [
        $row['tanggal'],
        ucfirst($row['jenis']),
        $row['category'] ?? 'Lainnya',
        number_format($row['jumlah'], 0, ',', '.'),
        $row['keterangan']
    ]);
}

fclose($output);
exit;
