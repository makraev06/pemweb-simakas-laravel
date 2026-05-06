<?php
session_start();
include '../config/database.php';

function redirectAssetDelete($message = '')
{
    if ($message !== '') {
        $_SESSION['form_error'] = $message;
    }

    header('Location: ../assets.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectAssetDelete();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$asset_id = (int) ($_POST['id'] ?? 0);

if ($asset_id <= 0) {
    redirectAssetDelete('Aset tidak valid.');
}

$stmt = mysqli_prepare(
    $conn,
    'DELETE FROM assets WHERE id = ? AND user_id = ?'
);
mysqli_stmt_bind_param($stmt, 'ii', $asset_id, $user_id);
mysqli_stmt_execute($stmt);

header('Location: ../assets.php');
exit;
