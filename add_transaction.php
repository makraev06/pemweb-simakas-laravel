<?php
include 'includes/auth_check.php';
include 'config/database.php';

$user_id = $_SESSION['user_id'];

$accounts = mysqli_query($conn, "
    SELECT account_id, account_name, account_type
    FROM accounts
    WHERE user_id = $user_id
    ORDER BY account_name ASC
");

$pageTitle = 'Add Transaction | CashTrack';
$activePage = 'transaction';
$searchPlaceholder = 'Search transactions...';
$hideSearch = true;
$formError = $_SESSION['form_error'] ?? '';
unset($_SESSION['form_error']);
?>

<!DOCTYPE html>
<html class="light" lang="en">
<?php include 'includes/head.php'; ?>

<body class="bg-surface text-on-surface antialiased">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <!-- Main Content Area -->
    <main class="ml-64 min-h-[calc(100vh-128px)] p-8">
        <div class="max-w-5xl mx-auto">
            <!-- Breadcrumbs & Heading -->
            <div class="mb-10">
                <nav class="flex items-center gap-2 text-xs font-bold tracking-widest text-slate-400 uppercase mb-3">
                    <a class="hover:text-emerald-600" href="dashboard.php">Buku Besar</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <a class="hover:text-emerald-600" href="transaction.php">Transaksi</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-emerald-700">Tambah Entri</span>
                </nav>
                <h2 class="text-4xl font-bold text-on-background tracking-tight">Tambah Transaksi</h2>
            </div>

            <!-- Asymmetric Layout: Form + Info Panel -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <!-- Left: Primary Form -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-[0px_12px_32px_rgba(25,28,30,0.04)]">
                        <?php if (!empty($formError)): ?>
                            <div
                                class="mb-6 rounded-lg bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container">
                                <?php echo htmlspecialchars($formError); ?>
                            </div>
                        <?php endif; ?>
                        <form action="process/transaction_store.php" method="POST" class="space-y-8">

                            <!-- Transaction Type & Amount Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label
                                        class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Jenis Transaksi
                                    </label>
                                    <div class="relative group">
                                        <select id="jenis" name="jenis"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 appearance-none focus:ring-2 focus:ring-emerald-500/10 transition-all font-medium"
                                            required>
                                            <option value="">Pilih jenis transaksi</option>
                                            <option value="income">Dana Masuk</option>
                                            <option value="expense">Dana Keluar</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="jumlahField" class="space-y-2">
                                    <label
                                        class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Jumlah
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                                        <input id="jumlah" name="jumlah"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 pl-8 pr-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                            placeholder="0.00" type="number" min="0" step="0.01" required />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label
                                        class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Kategori
                                    </label>
                                    <div class="relative group">
                                        <select id="category" name="category"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 appearance-none focus:ring-2 focus:ring-emerald-500/10 transition-all font-medium"
                                            required>
                                            <option value="">Pilih jenis transaksi dulu</option>
                                        </select>
                                        <span
                                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            expand_more
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Sumber Akun
                                    </label>
                                    <div class="relative group">
                                        <select name="account_id"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 pr-12 appearance-none focus:ring-2 focus:ring-emerald-500/10 transition-all font-medium"
                                            required>
                                            <option value="">Pilih sumber akun</option>

                                            <?php while ($account = mysqli_fetch_assoc($accounts)): ?>
                                                <option value="<?php echo $account['account_id']; ?>">
                                                    <?php echo htmlspecialchars($account['account_name']); ?>
                                                    <?php if (!empty($account['account_type'])): ?>
                                                        - <?php echo htmlspecialchars(ucfirst($account['account_type'])); ?>
                                                    <?php endif; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>

                                        <span
                                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            expand_more
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div id="assetFields"
                                class="hidden space-y-6 rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-5">
                                <div>
                                    <p class="text-[0.6875rem] font-bold text-emerald-700 uppercase tracking-widest">
                                        Detail Aset
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label
                                            class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                            Nama Aset
                                        </label>
                                        <input id="asset_name" name="asset_name"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                            placeholder="e.g. Laptop kantor, motor operasional" type="text" />
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                            Jenis Aset
                                        </label>
                                        <div class="relative group">
                                            <select id="asset_type" name="asset_type"
                                                class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 appearance-none focus:ring-2 focus:ring-emerald-500/10 transition-all font-medium">
                                                <option value="">Pilih jenis aset</option>
                                                <option value="Kendaraan">Kendaraan</option>
                                                <option value="Elektronik">Elektronik</option>
                                                <option value="Properti">Properti</option>
                                                <option value="Peralatan">Peralatan</option>
                                                <option value="Investasi">Investasi</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                            <span
                                                class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                expand_more
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                        Nilai Aset
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                                        <input id="asset_value" name="asset_value"
                                            class="w-full bg-surface-container-low border-none rounded-lg py-3 pl-8 pr-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                            placeholder="0.00" type="number" min="0" step="0.01" />
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <label
                                    class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                    Keterangan
                                </label>
                                <input name="keterangan"
                                    class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                    placeholder="e.g. Pembayaran vendor, gaji, pemasukan penjualan..." type="text"
                                    required />
                            </div>

                            <!-- Additional Notes -->
                            <div class="space-y-2">
                                <label
                                    class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                    Catatan Tambahan
                                </label>
                                <textarea name="catatan"
                                    class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-emerald-500/10 transition-all resize-none"
                                    placeholder="Riwayat audit lengkap..." rows="4"></textarea>
                            </div>

                            <!-- Tanggal -->
                            <div class="space-y-2">
                                <label
                                    class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-widest">
                                    Tanggal
                                </label>
                                <input name="tanggal" value="<?= date('Y-m-d') ?>"
                                    class="w-full bg-surface-container-low border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-emerald-500/10 transition-all"
                                    type="date" required />
                            </div>

                            <!-- Actions -->
                            <div class="pt-6 flex items-center justify-end gap-4">
                                <a href="transaction.php"
                                    class="px-8 py-3 rounded-lg text-emerald-700 font-bold hover:bg-surface-container-high transition-all">
                                    Batal
                                </a>
                                <button
                                    class="px-10 py-3 bg-gradient-to-br from-primary to-primary-container text-white font-bold rounded-lg shadow-lg hover:shadow-emerald-900/10 active:scale-[0.98] transition-all"
                                    type="submit">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right: Contextual Info Panel -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Live Preview Card -->
                    <div class="bg-emerald-900 text-white p-6 rounded-xl relative overflow-hidden group">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16 blur-3xl group-hover:bg-white/10 transition-colors">
                        </div>
                        <p class="text-[0.6875rem] font-bold uppercase tracking-widest text-emerald-400 mb-6">
                            Pratinjau Data
                        </p>
                        <div class="space-y-4">
                            <div class="flex justify-between items-end">
                                <span class="text-sm opacity-70">Estimasi Perubahan Saldo</span>
                                <span class="text-2xl font-bold font-['Manrope']">+ Rp0.00</span>
                            </div>
                            <div class="h-[1px] bg-white/10"></div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span class="opacity-70">Kategori Pajak</span>
                                    <span class="font-medium">Bebas (Strategis)</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="opacity-70">Waktu Penyelesaian</span>
                                    <span class="font-medium text-emerald-300">Instant (T+0)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentation Hint -->
                    <div class="bg-surface-container-low p-6 rounded-xl space-y-4">
                        <div class="flex items-center gap-3 text-emerald-700">
                            <span class="material-symbols-outlined">verified_user</span>
                            <h3 class="font-bold">Verifikasi Kepatuhan</h3>
                        </div>
                        <p class="text-xs text-on-surface-variant leading-relaxed">
                            Semua transaksi yang melebihi Rp10.000.000 memerlukan otorisasi sekunder dari direktur
                            regional. Pastikan semua catatan tambahan mencantumkan kode proyek yang diperlukan untuk
                            rekonsiliasi audit..
                        </p>
                        <a class="inline-flex items-center text-xs font-bold text-emerald-600 hover:gap-2 transition-all"
                            href="#">
                            Lihat Dokumentasi Protokol
                            <span class="material-symbols-outlined text-sm ml-1">arrow_forward</span>
                        </a>
                    </div>

                    <!-- Visual Decoration Image -->
                    <div class="rounded-xl overflow-hidden h-40 relative group">
                        <img alt="Financial Analytics"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDh2YMD1OPtBZ9vS8HqLLVcjxMOBhfl7-NugvONkEWYGgZZl7W_tE3YhK9CDO7gasggIUExFIi-5Y0umlzprrwjMyNZcCF5jlJlmS4_lG3Sa0JcFYrF2W8KF_xjR7yiZfnl76BxyqaPa-fHP7te_P3VsJAF5CKMsTWK0Gv_57O5-kh9BYhrP4im-SlHhKgK4il2bbS35x92t4XuKCQ2BEFNnFjB1-wJYgInhFnND1ytI24y8zfVNZQQhM8sjurCgsMNAizCDuKxE6CA" />
                        <div class="absolute inset-0 bg-emerald-900/40 mix-blend-multiply"></div>
                        <div class="absolute inset-0 flex items-center justify-center p-6 text-center">
                            <p class="text-white text-sm font-medium leading-tight">
                                "Efisiensi merupakan hasil dari ketelitian."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer
        class="ml-64 p-6 flex justify-between items-center bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-4">
            <span class="font-['Inter'] text-xs text-slate-400">
                © 2026 CashTrack. All rights reserved.
            </span>
        </div>
        <div class="flex items-center gap-6">
            <a class="font-['Inter'] text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                href="#">
                Privacy Policy
            </a>
            <a class="font-['Inter'] text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                href="#">
                Terms
            </a>
            <span
                class="font-['Inter'] text-xs text-slate-400 px-3 py-1 bg-slate-200/50 dark:bg-slate-800 rounded-full">
                Version 2.4.1
            </span>
        </div>
    </footer>
    <script>
        const jenisInput = document.getElementById('jenis');
        const jumlahField = document.getElementById('jumlahField');
        const jumlahInput = document.getElementById('jumlah');
        const categoryInput = document.getElementById('category');
        const assetFields = document.getElementById('assetFields');
        const assetNameInput = document.getElementById('asset_name');
        const assetTypeInput = document.getElementById('asset_type');
        const assetValueInput = document.getElementById('asset_value');
        const categoryOptions = {
            income: [
                'Gaji',
                'Bonus',
                'Penjualan',
                'Investasi',
                'Hadiah',
                'Lainnya'
            ],
            expense: [
                'Makanan',
                'Transportasi',
                'Tagihan',
                'Belanja',
                'Investasi',
                'Pembelian Aset',
                'Lainnya'
            ]
        };

        function renderCategoryOptions() {
            const selectedJenis = jenisInput.value;
            const currentCategory = categoryInput.value;
            categoryInput.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = selectedJenis ? 'Pilih kategori transaksi' : 'Pilih jenis transaksi dulu';
            categoryInput.appendChild(placeholder);

            if (!categoryOptions[selectedJenis]) {
                categoryInput.value = '';
                toggleAssetFields();
                return;
            }

            categoryOptions[selectedJenis].forEach(function (category) {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categoryInput.appendChild(option);
            });

            categoryInput.value = categoryOptions[selectedJenis].includes(currentCategory) ? currentCategory : '';
            toggleAssetFields();
        }

        function toggleAssetFields() {
            const shouldShow = categoryInput.value === 'Pembelian Aset';
            assetFields.classList.toggle('hidden', !shouldShow);
            jumlahField.classList.toggle('hidden', shouldShow);
            jumlahInput.required = !shouldShow;
            assetNameInput.required = shouldShow;
            assetTypeInput.required = shouldShow;
            assetValueInput.required = shouldShow;
            jenisInput.setCustomValidity(
                shouldShow && jenisInput.value === 'income' ?
                    'Pembelian Aset hanya bisa digunakan untuk Dana Keluar.' :
                    ''
            );
        }

        categoryInput.addEventListener('change', toggleAssetFields);
        jenisInput.addEventListener('change', renderCategoryOptions);
        renderCategoryOptions();
    </script>
</body>

</html>