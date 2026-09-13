<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Saldo Deposit</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola dan pantau modal saldo internet Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('reseller-portal.finance.topup')); ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size: 18px">add_circle</span>
                Isi Saldo (Top Up)
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="relative overflow-hidden rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:64px">account_balance_wallet</span>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-bold text-indigo-500 uppercase tracking-widest mb-2">Saldo Aktif Saat Ini</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3">Rp <?php echo e(number_format($user->balance, 0, ',', '.')); ?></div>
                <div class="flex items-center text-xs">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->is_balance_active): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                            Siap Digunakan
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                            Dibekukan / Tidak Aktif
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="relative overflow-hidden rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:64px">payments</span>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-widest mb-2">Total Pengisian Bulan Ini</h3>
                <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3">Rp <?php echo e(number_format($totalTopup, 0, ',', '.')); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Akumulasi top up yang telah disetujui
                </div>
            </div>
        </div>

        
        <div class="relative overflow-hidden rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-amber-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:64px">hourglass_top</span>
            </div>
            <div class="p-6">
                <h3 class="text-sm font-bold text-amber-500 uppercase tracking-widest mb-2">Menunggu Persetujuan</h3>
                <div class="text-4xl font-black text-amber-700 dark:text-amber-300 mb-3">Rp <?php echo e(number_format($totalPending, 0, ',', '.')); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Sedang diperiksa oleh Admin
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center">
            <span class="material-symbols-outlined notranslate mr-2 text-indigo-500" translate="no">info</span>
            Informasi Penggunaan Saldo
        </h3>
        
        <div class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
            <p>Saldo Deposit Anda adalah modal utama untuk menjalankan bisnis di jaringan kami. Saldo ini akan terpotong secara otomatis ketika Anda melakukan tindakan berikut:</p>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong class="text-slate-800 dark:text-slate-200">Mendaftarkan Pelanggan Baru:</strong> Saldo terpotong sesuai harga dasar (modal) profil paket PPPoE/Hotspot yang dipilih.</li>
                <li><strong class="text-slate-800 dark:text-slate-200">Mencetak Voucher:</strong> Saldo terpotong sejumlah kuantitas voucher dikali harga dasar profil voucher.</li>
                <li><strong class="text-slate-800 dark:text-slate-200">Perpanjangan Otomatis (Auto-Renewal):</strong> Saldo akan ditarik ketika sistem memperpanjang layanan pelanggan aktif Anda di bulan berikutnya.</li>
            </ul>
            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700">
                <p class="font-medium">?? Tips:</p>
                <p class="mt-1">Pastikan saldo Anda selalu cukup sebelum awal bulan untuk menghindari isolir otomatis massal pada pelanggan Anda karena gagal perpanjang layanan.</p>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\reseller-portal\finance\balance.blade.php ENDPATH**/ ?>