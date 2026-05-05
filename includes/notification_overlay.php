<?php
$notifications = $notifications ?? [
    [
        'title' => 'Data baru masuk',
        'message' => 'Ada data baru yang perlu diperiksa.',
        'time' => 'Baru saja',
        'icon' => 'notifications',
        'is_read' => false,
    ],
    [
        'title' => 'Laporan diperbarui',
        'message' => 'Laporan berhasil diperbarui.',
        'time' => '10 menit lalu',
        'icon' => 'description',
        'is_read' => false,
    ],
    [
        'title' => 'Login berhasil',
        'message' => 'Akun kamu baru saja digunakan untuk login.',
        'time' => '1 jam lalu',
        'icon' => 'login',
        'is_read' => true,
    ],
];
?>
<div id="notificationOverlay"
    class="fixed inset-0 z-[90] hidden items-start justify-end bg-slate-950/30 p-4 pt-20 backdrop-blur-sm">
    <div id="notificationPanel"
        class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl animate-scale-in">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-5">
            <div>
                <h2 class="text-xl font-bold">Notifikasi</h2>
                <p class="text-sm text-slate-500">Daftar aktivitas terbaru kamu</p>
            </div>
            <button type="button" onclick="closeNotificationOverlay()"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="max-h-[420px] overflow-y-auto">
            <?php foreach ($notifications as $notif): ?>
                <div
                    class="border-b border-slate-100 p-5 transition hover:bg-slate-50 <?php echo !$notif['is_read'] ? 'bg-emerald-50/60' : ''; ?>">
                    <div class="flex gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <span class="material-symbols-outlined">
                                <?php echo htmlspecialchars($notif['icon']); ?>
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($notif['title']); ?>
                                </h3>

                                <?php if (!$notif['is_read']): ?>
                                    <span
                                        class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">
                                        Baru
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="mt-1 text-sm text-slate-600">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                <?php echo htmlspecialchars($notif['time']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-end bg-slate-50 p-4">
            <button type="button" onclick="closeNotificationOverlay()"
                class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    const notificationOverlay = document.getElementById('notificationOverlay');

    function openNotificationOverlay() {
        if (!notificationOverlay) {
            return;
        }

        notificationOverlay.classList.remove('hidden');
        notificationOverlay.classList.add('flex');
    }

    function closeNotificationOverlay() {
        if (!notificationOverlay) {
            return;
        }

        notificationOverlay.classList.add('hidden');
        notificationOverlay.classList.remove('flex');
    }

    if (notificationOverlay) {
        notificationOverlay.addEventListener('click', function (event) {
            if (event.target === notificationOverlay) {
                closeNotificationOverlay();
            }
        });
    }
</script>
