<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Laporan Pendapatan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total uang yang berhasil ditagihkan ke pelanggan pada periode <?php echo e($currentMonthName); ?>.</p>
        </div>

    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">account_balance_wallet</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Uang Masuk</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">receipt</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Jumlah Transaksi Sukses</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100"><?php echo e($transactionCount); ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">analytics</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Rata-Rata per Transaksi</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($averagePayment, 0, ',', '.')); ?></p>
            </div>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-slate-800 dark:text-slate-200">5 Pembayaran Masuk Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-6 py-3 text-left font-semibold">Tgl Transaksi</th>
                        <th class="px-6 py-3 text-left font-semibold">Referensi</th>
                        <th class="px-6 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="px-6 py-3 text-left font-semibold">Metode</th>
                        <th class="px-6 py-3 text-right font-semibold">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400"><?php echo e($payment->created_at->format('d M Y H:i')); ?></td>
                            <td class="px-6 py-3 font-mono text-slate-700 dark:text-slate-300 text-xs"><?php echo e($payment->reference_number); ?></td>
                            <td class="px-6 py-3 text-slate-700 dark:text-slate-300"><?php echo e($payment->customer->name ?? '-'); ?></td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400 uppercase text-xs"><?php echo e(str_replace('_', ' ', $payment->method)); ?></td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp <?php echo e(number_format($payment->amount, 0, ',', '.')); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">Belum ada data pendapatan bulan ini.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\reseller-portal\reports\revenue.blade.php ENDPATH**/ ?>