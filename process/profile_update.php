<?php
session_start();
include '../config/database.php';
include '../includes/csrf.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function redirectProfile($type, $message)
{
    $_SESSION['profile_' . $type] = $message;
    header("Location: ../profile.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../profile.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? '')) {
    redirectProfile('error', 'Sesi form tidak valid. Silakan coba lagi.');
}

$user_id = (int) $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['password_confirmation'] ?? '';
$passwordHash = null;
$newPhotoPath = null;
$newPhotoAbsolutePath = null;

if ($name === '') {
    redirectProfile('error', 'Nama wajib diisi.');
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectProfile('error', 'Email wajib diisi dengan format yang valid.');
}

if ($password !== '') {
    if (strlen($password) < 6) {
        redirectProfile('error', 'Password baru minimal 6 karakter.');
    }

    if ($password !== $passwordConfirmation) {
        redirectProfile('error', 'Konfirmasi password tidak cocok.');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
}

try {
    $stmtCurrent = mysqli_prepare($conn, "SELECT profile_photo FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtCurrent, "i", $user_id);
    mysqli_stmt_execute($stmtCurrent);
    $currentUser = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCurrent));

    if (!$currentUser) {
        redirectProfile('error', 'User tidak ditemukan.');
    }

    $stmtEmail = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
    mysqli_stmt_bind_param($stmtEmail, "si", $email, $user_id);
    mysqli_stmt_execute($stmtEmail);
    $existingEmail = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEmail));

    if ($existingEmail) {
        redirectProfile('error', 'Email sudah digunakan oleh user lain.');
    }

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_photo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            redirectProfile('error', 'Gagal mengupload foto profil.');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            redirectProfile('error', 'Ukuran foto profil maksimal 2MB.');
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            redirectProfile('error', 'Format foto profil harus jpg, jpeg, png, atau webp.');
        }

        $mimeType = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
            if ($finfo) {
                finfo_close($finfo);
            }
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        }

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            redirectProfile('error', 'Tipe file foto profil tidak valid.');
        }

        $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'profile_' . $user_id . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $newPhotoAbsolutePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        $newPhotoPath = 'uploads/profile/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $newPhotoAbsolutePath)) {
            redirectProfile('error', 'Gagal menyimpan foto profil.');
        }
    }

    if ($passwordHash !== null && $newPhotoPath !== null) {
        $stmtUpdate = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, password = ?, profile_photo = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtUpdate, "ssssi", $name, $email, $passwordHash, $newPhotoPath, $user_id);
    } elseif ($passwordHash !== null) {
        $stmtUpdate = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtUpdate, "sssi", $name, $email, $passwordHash, $user_id);
    } elseif ($newPhotoPath !== null) {
        $stmtUpdate = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, profile_photo = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtUpdate, "sssi", $name, $email, $newPhotoPath, $user_id);
    } else {
        $stmtUpdate = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtUpdate, "ssi", $name, $email, $user_id);
    }

    mysqli_stmt_execute($stmtUpdate);

    $oldPhotoPath = $currentUser['profile_photo'] ?? '';
    if ($newPhotoPath !== null && !empty($oldPhotoPath) && strpos($oldPhotoPath, 'uploads/profile/') === 0) {
        $oldPhotoAbsolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $oldPhotoPath);
        if (is_file($oldPhotoAbsolutePath)) {
            unlink($oldPhotoAbsolutePath);
        }
    }

    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['profile_photo'] = $newPhotoPath ?? ($oldPhotoPath ?? '');

    redirectProfile('success', 'Profil berhasil diperbarui.');
} catch (Throwable $error) {
    if ($newPhotoAbsolutePath !== null && is_file($newPhotoAbsolutePath)) {
        unlink($newPhotoAbsolutePath);
    }

    redirectProfile('error', 'Gagal memperbarui profil. Pastikan migration database sudah dijalankan.');
}
