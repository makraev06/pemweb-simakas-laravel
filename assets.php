<?php
include 'includes/auth_check.php';
include 'config/database.php';

$user_id = $_SESSION['user_id'];

$query_assets = mysqli_query($conn, "
    SELECT * FROM assets
    WHERE user_id = $user_id
    ORDER BY id DESC
");

$query_total = mysqli_query($conn, "
    SELECT SUM(nilai) as total_asset, COUNT(*) as total_item
    FROM assets
    WHERE user_id = $user_id
");

$data_total = mysqli_fetch_assoc($query_total);

$total_asset = $data_total['total_asset'] ?? 0;
$total_item = $data_total['total_item'] ?? 0;
?>

<!DOCTYPE html>

<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Assets | The Sovereign Ledger</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Manrope:wght@600;700;800&amp;display=swap"
        rel="stylesheet" />
    <!-- Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "on-secondary-container": "#456a56",
                    "on-error": "#ffffff",
                    "on-primary-fixed-variant": "#005235",
                    "surface-container-high": "#e6e8eb",
                    "on-surface-variant": "#3e4942",
                    "primary-container": "#00875a",
                    "secondary": "#416652",
                    "secondary-container": "#c0e9cf",
                    "surface-container-lowest": "#ffffff",
                    "primary-fixed-dim": "#71dba6",
                    "surface-bright": "#f7f9fc",
                    "on-error-container": "#93000a",
                    "on-tertiary-fixed-variant": "#7d2a2a",
                    "inverse-surface": "#2d3133",
                    "on-primary": "#ffffff",
                    "surface-dim": "#d8dadd",
                    "on-primary-fixed": "#002113",
                    "on-tertiary-container": "#ffffff",
                    "outline": "#6e7a71",
                    "on-tertiary": "#ffffff",
                    "tertiary": "#9b403e",
                    "inverse-on-surface": "#eff1f4",
                    "on-background": "#191c1e",
                    "on-secondary-fixed": "#002113",
                    "surface-tint": "#006c47",
                    "surface-variant": "#e0e3e6",
                    "on-secondary": "#ffffff",
                    "primary": "#006b47",
                    "surface-container": "#eceef1",
                    "surface-container-highest": "#e0e3e6",
                    "on-secondary-fixed-variant": "#294e3b",
                    "error": "#ba1a1a",
                    "tertiary-fixed": "#ffdad7",
                    "surface": "#f7f9fc",
                    "background": "#f7f9fc",
                    "outline-variant": "#bdcac0",
                    "surface-container-low": "#f2f4f7",
                    "on-tertiary-fixed": "#410005",
                    "on-primary-container": "#ffffff",
                    "error-container": "#ffdad6",
                    "secondary-fixed-dim": "#a7d0b7",
                    "on-surface": "#191c1e",
                    "tertiary-container": "#ba5855",
                    "primary-fixed": "#8df7c1",
                    "secondary-fixed": "#c2ecd2",
                    "inverse-primary": "#71dba6",
                    "tertiary-fixed-dim": "#ffb3af"
                },
                "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
                },
                "fontFamily": {
                    "headline": ["Manrope"],
                    "body": ["Inter"],
                    "label": ["Inter"]
                }
            },
        },
    }
    </script>
    <style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL'0, 'wght'400, 'GRAD'0, 'opsz'24;
        display: inline-block;
        vertical-align: middle;
    }

    body {
        font-family: 'Inter', sans-serif;
    }

    h1,
    h2,
    h3 {
        font-family: 'Manrope', sans-serif;
    }
    </style>
</head>

<body class="bg-surface text-on-surface">
    <!-- SideNavBar Shell -->
    <aside
        class="fixed left-0 top-0 h-full flex flex-col p-4 bg-slate-50 dark:bg-slate-900 h-screen w-64 border-r-0 shadow-[12px_0px_32px_rgba(25,28,30,0.04)] z-50">
        <div class="flex items-center gap-3 mb-10 px-2">
            <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-white"
                    style="font-variation-settings: 'FILL' 1;">account_balance</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-emerald-800 dark:text-emerald-100 leading-tight">Sovereign Ledger</h2>
                <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Institutional Grade</p>
            </div>
        </div>
        <nav class="flex-1 space-y-2">
            <!-- Dashboard Tab -->
            <a class="flex items-center gap-3 px-4 py-3 text-slate-500 dark:text-slate-400 hover:text-emerald-600 hover:bg-slate-200/50 dark:hover:bg-slate-800/50 transition-colors font-['Manrope'] font-semibold tracking-tight rounded-lg"
                href="dashboard.php">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <!-- Transactions Tab -->
            <a class="flex items-center gap-3 px-4 py-3 text-slate-500 dark:text-slate-400 hover:text-emerald-600 hover:bg-slate-200/50 dark:hover:bg-slate-800/50 transition-colors font-['Manrope'] font-semibold tracking-tight rounded-lg"
                href="transaction.php">
                <span class="material-symbols-outlined">account_balance_wallet</span>
                <span>Transactions</span>
            </a>
            <!-- Assets Tab (ACTIVE) -->
            <a class="flex items-center gap-3 px-4 py-3 text-emerald-700 dark:text-emerald-400 font-bold bg-white dark:bg-slate-800 shadow-sm rounded-lg translate-x-1 transition-transform font-['Manrope'] tracking-tight"
                href="assets.php">
                <span class="material-symbols-outlined">account_balance</span>
                <span>Assets</span>
            </a>
        </nav>
        <div class="mt-auto pt-6 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3 px-2">
                <img alt="User Profile" class="w-10 h-10 rounded-full object-cover grayscale"
                    data-alt="Professional corporate headshot of a male executive in a navy blue suit with soft office lighting"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCfYtprLb6iuOCIeRlfYs1UCStaw5irQW5oDr6hg3NjHy9jqtiC0HyYP9IR-FTpBRX7JjYeXGy6446aDn0wLX9TaS0POtkwExKv6q4kT55HvqLQfwdVNyBFsWBpnyBzP0dGnKjTqF_EXw1GS9WugDKf-mx-c5AJ3P2CADaN-AnlkkX5xRMBsjyIb79ZSYkxQR29FvGW258dVRtRshU-kah0HCP7faNJT5weUE8Iwnf8cK3tkU_dETlOO2tqkrHntXfo7f70Ye2FWhE2" />
                <div class="overflow-hidden">
                    <p class="text-sm font-semibold truncate">Alexander Sterling</p>
                    <p class="text-[10px] text-slate-400">Chief Asset Manager</p>
                </div>
            </div>
        </div>
    </aside>
    <!-- Main Content Area -->
    <div class="ml-64 flex flex-col min-h-screen">
        <!-- TopNavBar Shell -->
        <header
            class="sticky top-0 z-40 flex justify-between items-center px-8 h-16 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-6">
                <div class="relative group">
                    <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-lg">search</span>
                    <input
                        class="bg-surface-container-low border-none rounded-full py-1.5 pl-10 pr-4 text-sm w-64 focus:ring-2 focus:ring-emerald-500/20 transition-all font-['Inter']"
                        placeholder="Search assets..." type="text" />
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button
                    class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-emerald-500 transition-colors">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <button
                    class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-emerald-500 transition-colors">
                    <span class="material-symbols-outlined">settings</span>
                </button>
            </div>
        </header>
        <!-- Canvas Container -->
        <main class="p-8 space-y-8 flex-1">

            <!-- Hero Hero Section: Total Asset Value -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="md:col-span-2 relative overflow-hidden rounded-xl bg-gradient-to-br from-primary to-primary-container p-8 text-white shadow-lg">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <span
                                class="text-primary-fixed text-xs font-bold uppercase tracking-widest opacity-80">Portfolio
                                Summary</span>
                            <h1 class="text-[2.75rem] font-bold leading-tight mt-1 font-['Manrope']">
                                Rp<?php echo number_format($total_asset, 0, ',', '.'); ?>
                            </h1>
                        </div>
                        <div class="mt-8 flex items-center gap-6">
                            <div class="flex flex-col">
                                <span class="text-xs opacity-70">24h Change</span>
                                <span class="text-lg font-bold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">trending_up</span>
                                    +2.45%
                                </span>
                            </div>
                            <div class="w-px h-8 bg-white/20"></div>
                            <div class="flex flex-col">
                                <span class="text-xs opacity-70">Active Positions</span>
                                <span class="text-lg font-bold"><?php echo $total_item; ?> Aset</span>
                            </div>
                        </div>
                    </div>
                    <!-- Background aesthetic -->
                    <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute right-8 top-8 opacity-20">
                        <span class="material-symbols-outlined text-[120px]">account_balance</span>
                    </div>
                </div>
                <div class="bg-surface-container-low rounded-xl p-6 flex flex-col justify-center">
                    <h3 class="text-sm font-semibold text-on-surface-variant mb-4 uppercase tracking-tighter">Quick
                        Actions</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="add_asset.php"
                            class="flex flex-col items-center gap-2 p-4 bg-surface-container-lowest rounded-lg hover:bg-white transition-all shadow-sm group">
                            <span
                                class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">add_card</span>
                            <span class="text-xs font-medium">Add Asset</span>
                        </a>
                        <button
                            class="flex flex-col items-center gap-2 p-4 bg-surface-container-lowest rounded-lg hover:bg-white transition-all shadow-sm group">
                            <span
                                class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">ios_share</span>
                            <span class="text-xs font-medium">Export Data</span>
                        </button>
                    </div>
                </div>
            </section>
            <!-- Asset List Container -->
            <section class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold font-['Manrope'] text-on-surface">Asset Holdings</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 font-medium">Sort by:</span>
                        <select
                            class="bg-transparent border-none text-xs font-bold text-primary focus:ring-0 cursor-pointer">
                            <option>Value (High-Low)</option>
                            <option>Category</option>
                            <option>Alphabetical</option>
                        </select>
                    </div>
                </div>
                <!-- Custom Card Table -->
                <?php if(mysqli_num_rows($query_assets) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query_assets)) : ?>
                <div class="bg-surface-container-lowest p-6 rounded-xl shadow">
                    <h3 class="text-lg font-bold">
                        <?php echo $row['nama_aset']; ?>
                    </h3>

                    <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">
                        <?php echo $row['kategori']; ?>
                    </span>

                    <p class="text-2xl font-bold mt-4 text-primary">
                        Rp<?php echo number_format($row['nilai'], 0, ',', '.'); ?>
                    </p>

                    <p class="text-sm text-slate-400 mt-2">
                        <?php echo $row['deskripsi']; ?>
                    </p>

                    <p class="text-xs text-slate-400 mt-1">
                        <?php echo $row['tanggal_perolehan']; ?>
                    </p>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p class="text-slate-400">Belum ada asset.</p>
                <?php endif; ?>
                <div class="px-6 py-4 bg-slate-50 border-t border-outline-variant/10 flex justify-center">
                    <button class="text-xs font-bold text-primary hover:underline">View All Assets (14)</button>
                </div>
    </div>
    </section>
    <!-- Analytics Visual Component (Contextual Addition) -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-bold font-['Manrope'] mb-6">Asset Allocation</h3>
            <div class="flex items-center gap-8">
                <div
                    class="w-32 h-32 rounded-full border-[12px] border-primary-container relative flex items-center justify-center">
                    <div
                        class="absolute inset-0 border-[12px] border-emerald-100 rounded-full border-t-transparent border-r-transparent -rotate-45">
                    </div>
                    <span class="text-xs font-bold text-primary">Balanced</span>
                </div>
                <div class="flex-1 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-primary"></div>
                            <span class="text-xs text-slate-500">Real Estate</span>
                        </div>
                        <span class="text-xs font-bold">45%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-secondary"></div>
                            <span class="text-xs text-slate-500">Equities</span>
                        </div>
                        <span class="text-xs font-bold">30%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-200"></div>
                            <span class="text-xs text-slate-500">Other</span>
                        </div>
                        <span class="text-xs font-bold">25%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm overflow-hidden relative">
            <div class="relative z-10">
                <h3 class="text-lg font-bold font-['Manrope'] mb-2">Portfolio Insights</h3>
                <p class="text-sm text-slate-500 mb-6 leading-relaxed">Your real estate holdings are performing
                    12% above benchmark this quarter. Consider rebalancing equity positions.</p>
                <button
                    class="bg-primary text-white px-5 py-2 rounded-md text-xs font-bold flex items-center gap-2 hover:opacity-90 transition-opacity">
                    View Full Report
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </div>
            <div class="absolute right-0 bottom-0 opacity-5 translate-x-4 translate-y-4">
                <span class="material-symbols-outlined text-[160px]">insights</span>
            </div>
        </div>
    </section>
    </main>
    <!-- Footer Shell -->
    <footer
        class="ml-64 p-6 flex justify-between items-center bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
        <p class="font-['Inter'] text-xs text-slate-400">© 2024 The Sovereign Ledger. All rights reserved.</p>
        <div class="flex gap-4">
            <a class="font-['Inter'] text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                href="#">Version 2.4.1</a>
            <a class="font-['Inter'] text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                href="#">Privacy Policy</a>
            <a class="font-['Inter'] text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                href="#">Terms</a>
        </div>
    </footer>
    </div>
</body>

</html>