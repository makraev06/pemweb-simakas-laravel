<?php
include 'includes/auth_check.php';
include 'config/database.php';
include 'includes/csrf.php';

$user_id = (int) $_SESSION['user_id'];

$stmtUser = mysqli_prepare($conn, "SELECT id, name, email, profile_photo FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmtUser, "i", $user_id);
mysqli_stmt_execute($stmtUser);
$userResult = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($userResult);

if (!$user) {
    header("Location: process/logout.php");
    exit;
}

$stmtAccounts = mysqli_prepare($conn, "
    SELECT account_id, account_name, account_type, balance, created_at, updated_at
    FROM accounts
    WHERE user_id = ?
    ORDER BY created_at DESC, account_id DESC
");
mysqli_stmt_bind_param($stmtAccounts, "i", $user_id);
mysqli_stmt_execute($stmtAccounts);
$accounts = mysqli_stmt_get_result($stmtAccounts);

$profileSuccess = $_SESSION['profile_success'] ?? '';
$profileError = $_SESSION['profile_error'] ?? '';
$accountSuccess = $_SESSION['account_success'] ?? '';
$accountError = $_SESSION['account_error'] ?? '';
unset($_SESSION['profile_success'], $_SESSION['profile_error'], $_SESSION['account_success'], $_SESSION['account_error']);

$defaultProfilePhoto = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCfYtprLb6iuOCIeRlfYs1UCStaw5irQW5oDr6hg3NjHy9jqtiC0HyYP9IR-FTpBRX7JjYeXGy6446aDn0wLX9TaS0POtkwExKv6q4kT55HvqLQfwdVNyBFsWBpnyBzP0dGnKjTqF_EXw1GS9WugDKf-mx-c5AJ3P2CADaN-AnlkkX5xRMBsjyIb79ZSYkxQR29FvGW258dVRtRshU-kah0HCP7faNJT5weUE8Iwnf8cK3tkU_dETlOO2tqkrHntXfo7f70Ye2FWhE2';
$profilePhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : $defaultProfilePhoto;
$accountTypes = [
    'bank' => 'Bank',
    'ewallet' => 'E-Wallet',
    'cash' => 'Cash',
];

function formatRupiah($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

$pageTitle = 'Profile | CashTrack';
$activePage = 'profile';
$hideSearch = true;
?>
<!DOCTYPE html>
<html class="light" lang="id">
<?php include 'includes/head.php'; ?>

<body class="bg-surface text-on-surface antialiased">
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <main class="ml-64 min-h-screen p-8 animate-fade-up">
        <div class="mx-auto max-w-6xl space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <nav class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <a class="hover:text-emerald-600" href="dashboard.php">Dashboard</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-emerald-700">Profile</span>
                    </nav>
                    <h2 class="text-4xl font-bold tracking-tight text-on-background">Profile Pengguna</h2>
                    <p class="mt-2 text-sm text-on-surface-variant">
                        Kelola identitas akun dan sumber dana yang dipakai untuk pencatatan.
                    </p>
                </div>
                <a href="dashboard.php"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-emerald-600">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>

            <?php if (!empty($profileSuccess) || !empty($accountSuccess)): ?>
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    <?php echo htmlspecialchars($profileSuccess ?: $accountSuccess); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($profileError) || !empty($accountError)): ?>
                <div class="rounded-lg bg-error-container px-4 py-3 text-sm font-semibold text-on-error-container">
                    <?php echo htmlspecialchars($profileError ?: $accountError); ?>
                </div>
            <?php endif; ?>

            <section class="grid grid-cols-1 gap-8 xl:grid-cols-12">
                <div class="xl:col-span-4">
                    <div class="rounded-xl bg-surface-container-lowest p-8 shadow-sm">
                        <div class="flex flex-col items-center text-center">
                            <img src="<?php echo htmlspecialchars($profilePhoto); ?>"
                                alt="Foto profil <?php echo htmlspecialchars($user['name']); ?>"
                                class="h-32 w-32 rounded-full object-cover shadow-md ring-4 ring-emerald-50">
                            <h3 class="mt-5 text-2xl font-bold"><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="mt-1 text-sm text-slate-500"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="mt-8 rounded-lg bg-surface-container-low p-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Keamanan</p>
                            <p class="mt-2 text-sm text-on-surface-variant">
                                Password hanya berubah jika kolom password baru diisi saat menyimpan profil.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-8">
                    <div class="rounded-xl bg-surface-container-lowest p-8 shadow-sm">
                        <div class="mb-8">
                            <h3 class="text-2xl font-bold">Edit Profil</h3>
                            <p class="mt-1 text-sm text-slate-500">Nama, email, foto profil, dan password akun.</p>
                        </div>

                        <form action="process/profile_update.php" method="POST" enctype="multipart/form-data"
                            class="space-y-6">
                            <?php echo csrfField(); ?>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                        Nama
                                    </label>
                                    <input name="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                                        class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                                        type="text" required>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                        Email
                                    </label>
                                    <input name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                        class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                                        type="email" required>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                    Foto Profil
                                </label>
                                <input name="profile_photo"
                                    class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800"
                                    type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <p class="text-xs text-slate-400">Format jpg, jpeg, png, atau webp. Maksimal 2MB.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                        Password Baru
                                    </label>
                                    <input name="password"
                                        class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                                        type="password" autocomplete="new-password">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                        Konfirmasi Password
                                    </label>
                                    <input name="password_confirmation"
                                        class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                                        type="password" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-br from-primary to-primary-container px-8 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/10 transition hover:shadow-emerald-500/20">
                                    <span class="material-symbols-outlined text-[18px]">save</span>
                                    Simpan Profil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section id="accounts" class="grid grid-cols-1 gap-8 xl:grid-cols-12">
                <div class="xl:col-span-4">
                    <div class="rounded-xl bg-surface-container-lowest p-8 shadow-sm">
                        <h3 class="text-2xl font-bold">Sumber Dana / Dompet</h3>
                        <p class="mt-2 text-sm text-slate-500">
                            Tambahkan rekening bank, e-wallet, atau cash yang kamu pakai.
                        </p>

                        <form action="process/account_store.php" method="POST" class="mt-8 space-y-5">
                            <?php echo csrfField(); ?>
                            <div class="space-y-2">
                                <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                    Nama Sumber Dana
                                </label>
                                <input name="account_name"
                                    class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                                    placeholder="Bank BCA, E-Wallet DANA, Cash" type="text" required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                    Kategori
                                </label>
                                <select name="account_type"
                                    class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10"
                                    required>
                                    <option value="">Pilih kategori</option>
                                    <?php foreach ($accountTypes as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>">
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                                    Saldo / Nominal Awal
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">
                                        Rp
                                    </span>
                                    <input name="balance"
                                        class="w-full rounded-lg border-none bg-surface-container-low py-3 pl-10 pr-4 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                                        placeholder="0" type="number" min="0" step="0.01" required>
                                </div>
                            </div>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                                Tambah Sumber Dana
                            </button>
                        </form>
                    </div>
                </div>

                <div class="xl:col-span-8">
                    <div class="rounded-xl bg-surface-container-lowest p-8 shadow-sm">
                        <div class="mb-6 flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-2xl font-bold">Daftar Sumber Dana</h3>
                                <p class="mt-1 text-sm text-slate-500">Semua data hanya untuk akun yang sedang login.</p>
                            </div>
                        </div>

                        <?php if (mysqli_num_rows($accounts) > 0): ?>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <?php while ($account = mysqli_fetch_assoc($accounts)): ?>
                                    <div class="rounded-xl border border-outline-variant/20 bg-surface-container-low p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-lg font-bold"><?php echo htmlspecialchars($account['account_name']); ?></p>
                                                <span
                                                    class="mt-2 inline-flex rounded bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-700">
                                                    <?php echo htmlspecialchars($accountTypes[$account['account_type']] ?? ucfirst($account['account_type'])); ?>
                                                </span>
                                                <p class="mt-4 text-2xl font-bold text-emerald-700">
                                                    <?php echo htmlspecialchars(formatRupiah($account['balance'])); ?>
                                                </p>
                                                <p class="mt-2 text-xs text-slate-400">
                                                    Dibuat <?php echo htmlspecialchars(date('d M Y', strtotime($account['created_at']))); ?>
                                                    <?php if (!empty($account['updated_at'])): ?>
                                                        <span class="mx-1">.</span>
                                                        Update <?php echo htmlspecialchars(date('d M Y', strtotime($account['updated_at']))); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <span class="material-symbols-outlined text-emerald-700">account_balance_wallet</span>
                                        </div>

                                        <div class="mt-5 flex items-center justify-end gap-2">
                                            <button type="button"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:text-emerald-700"
                                                data-account-id="<?php echo (int) $account['account_id']; ?>"
                                                data-account-name="<?php echo htmlspecialchars($account['account_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-account-type="<?php echo htmlspecialchars($account['account_type'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-account-balance="<?php echo htmlspecialchars($account['balance'], ENT_QUOTES, 'UTF-8'); ?>"
                                                onclick="openAccountEditModal(this)">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                Edit
                                            </button>

                                            <form action="process/account_delete.php" method="POST"
                                                onsubmit="return confirm('Hapus sumber dana ini? Transaksi lama akan tetap tersimpan.');">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="account_id" value="<?php echo (int) $account['account_id']; ?>">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-red-100 bg-white px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="rounded-xl bg-surface-container-low p-10 text-center">
                                <p class="text-sm font-semibold text-slate-500">Belum ada sumber dana.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div id="accountEditOverlay"
        class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <div id="accountEditPanel" class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl animate-scale-in">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold">Edit Sumber Dana</h3>
                    <p class="mt-1 text-sm text-slate-500">Perbarui nama, kategori, dan saldo akun.</p>
                </div>
                <button type="button" onclick="closeAccountEditModal()"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="process/account_update.php" method="POST" class="space-y-5">
                <?php echo csrfField(); ?>
                <input id="accountEditId" type="hidden" name="account_id" value="">
                <div class="space-y-2">
                    <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                        Nama Sumber Dana
                    </label>
                    <input id="accountEditName" name="account_name"
                        class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                        type="text" required>
                </div>

                <div class="space-y-2">
                    <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                        Kategori
                    </label>
                    <select id="accountEditType" name="account_type"
                        class="w-full rounded-lg border-none bg-surface-container-low px-4 py-3 focus:ring-2 focus:ring-emerald-500/10"
                        required>
                        <?php foreach ($accountTypes as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>">
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[0.6875rem] font-bold uppercase tracking-widest text-on-surface-variant">
                        Saldo
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">
                            Rp
                        </span>
                        <input id="accountEditBalance" name="balance"
                            class="w-full rounded-lg border-none bg-surface-container-low py-3 pl-10 pr-4 focus:ring-2 focus:ring-emerald-500/10 input-focus"
                            type="number" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" onclick="closeAccountEditModal()"
                        class="rounded-lg px-5 py-3 text-sm font-bold text-emerald-700 transition hover:bg-surface-container-high">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const accountEditOverlay = document.getElementById('accountEditOverlay');
        const accountEditId = document.getElementById('accountEditId');
        const accountEditName = document.getElementById('accountEditName');
        const accountEditType = document.getElementById('accountEditType');
        const accountEditBalance = document.getElementById('accountEditBalance');

        function openAccountEditModal(button) {
            accountEditId.value = button.dataset.accountId || '';
            accountEditName.value = button.dataset.accountName || '';
            accountEditType.value = button.dataset.accountType || 'bank';
            accountEditBalance.value = button.dataset.accountBalance || '0';
            accountEditOverlay.classList.remove('hidden');
            accountEditOverlay.classList.add('flex');
            accountEditName.focus();
        }

        function closeAccountEditModal() {
            accountEditOverlay.classList.add('hidden');
            accountEditOverlay.classList.remove('flex');
        }

        if (accountEditOverlay) {
            accountEditOverlay.addEventListener('click', function (event) {
                if (event.target === accountEditOverlay) {
                    closeAccountEditModal();
                }
            });
        }
    </script>
</body>

</html>
