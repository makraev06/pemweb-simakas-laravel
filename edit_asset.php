<?php
include 'includes/auth_check.php';
include 'config/database.php';

$assetId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_id = (int) $_SESSION['user_id'];

if ($assetId <= 0) {
    header('Location: assets.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM assets WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $assetId, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$asset = mysqli_fetch_assoc($result);

if (!$asset) {
    header('Location: assets.php');
    exit;
}

$pageTitle = 'Edit Asset | CashTrack';
$activePage = 'assets';
$searchPlaceholder = 'Cari Assets...';
$topbarTitle = 'Edit Asset';
$formError = $_SESSION['form_error'] ?? '';
unset($_SESSION['form_error']);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<?php include 'includes/head.php'; ?>

<body class="bg-surface text-on-surface antialiased">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <main class="ml-64 min-h-[calc(100vh-128px)] p-8 animate-fade-up">
        <div class="max-w-5xl mx-auto">
            <div class="mb-10">
                <nav class="flex items-center gap-2 text-xs font-bold tracking-widest text-slate-400 uppercase mb-3">
                    <a class="hover:text-emerald-600" href="dashboard.php">Buku Besar</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <a class="hover:text-emerald-600" href="assets.php">Aset</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-emerald-700">Edit Aset</span>
                </nav>
                <h2 class="text-4xl font-bold text-on-background tracking-tight">Edit Aset</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.04)] animate-scale-in">
                        <?php if (!empty($formError)): ?>
                            <div class="mb-6 rounded-lg bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container">
                                <?php echo htmlspecialchars($formError); ?>
                            </div>
                        <?php endif; ?>
                        <form action="process/asset_update.php" method="POST" class="space-y-8">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($asset['id']); ?>" />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Nama Aset
                                    </label>
                                    <input name="nama_aset" value="<?php echo htmlspecialchars($asset['nama_aset']); ?>"
                                        class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                        placeholder="e.g. Laptop Office, Emas, Tabungan BCA" type="text" required />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Nilai Asset
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                                        <input name="nilai" value="<?php echo htmlspecialchars($asset['nilai']); ?>"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 pl-8 pr-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                            placeholder="0.00" type="number" required />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Kategori
                                    </label>
                                    <div class="relative group">
                                        <select name="kategori"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 appearance-none focus:ring-2 focus:ring-emerald-500/10 transition-all font-medium"
                                            required>
                                            <option value="">Pilih kategori asset</option>
                                            <?php foreach (['Properti','Kendaraan','Emas','Tabungan','Elektronik','Peralatan','Investasi','Lainnya'] as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $asset['kategori'] === $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            expand_more
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Keterangan
                                    </label>
                                    <input name="deskripsi" value="<?php echo htmlspecialchars($asset['deskripsi']); ?>"
                                        class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                        placeholder="e.g. Asset kantor, investasi pribadi, kendaraan operasional..." type="text" required />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                    Tanggal Perolehan
                                </label>
                                <input name="tanggal_perolehan" value="<?php echo htmlspecialchars($asset['tanggal_perolehan']); ?>"
                                    class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                    type="date" required />
                            </div>

                            <div class="pt-6 flex items-center justify-end gap-4">
                                <a href="assets.php"
                                    class="px-8 py-3 rounded-lg text-emerald-700 font-bold hover:bg-surface-container-high transition-all">
                                    Batal
                                </a>
                                <button
                                    class="px-10 py-3 bg-gradient-to-br from-primary to-primary-container text-white font-bold rounded-lg shadow-lg hover:shadow-emerald-900/10 active:scale-[0.98] transition-all"
                                    type="submit">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="ml-64 p-6 flex justify-between items-center bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-4">
            <span class="font-['Inter'] text-xs text-slate-400">© 2026 CashTrack. All rights reserved.</span>
        </div>
        <div class="flex items-center gap-6">
            <a class="font-['Inter'] text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" href="#">Privacy Policy</a>
            <a class="font-['Inter'] text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" href="#">Terms</a>
            <span class="font-['Inter'] text-xs text-slate-400 px-3 py-1 bg-slate-200/50 dark:bg-slate-800 rounded-full">Version 2.4.1</span>
        </div>
    </footer>
</body>
</html>
