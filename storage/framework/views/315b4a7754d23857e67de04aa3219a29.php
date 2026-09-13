<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Riwayat Pembayaran</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lacak semua pembayaran yang telah dilakukan oleh pelanggan Anda.</p>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
        <div class="flex-1 w-full relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari no referensi atau pelanggan..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <select wire:model.live="status" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="success">Berhasil (Success)</option>
                <option value="pending">Tertunda (Pending)</option>
                <option value="failed">Gagal (Failed)</option>
            </select>
            <select wire:model.live="perPage" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 text-left font-semibold">Tgl Bayar</th>
                        <th class="px-4 py-3 text-left font-semibold">No Referensi</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold">Nominal</th>
                        <th class="px-4 py-3 text-left font-semibold">Metode</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                <?php echo e($row->paid_at ? \Carbon\Carbon::parse($row->paid_at)->format('d M Y') : $row->created_at->format('d M Y')); ?><br>
                                <span class="text-xs text-slate-400"><?php echo e($row->paid_at ? \Carbon\Carbon::parse($row->paid_at)->format('H:i') : $row->created_at->format('H:i')); ?></span>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium font-mono text-xs">
                                <?php echo e($row->reference_number); ?>

                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200"><?php echo e($row->customer->name ?? 'Unknown'); ?></div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?php echo e($row->customer->username ?? '-'); ?></div>
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">
                                Rp <?php echo e(number_format($row->amount ?? 0, 0, ',', '.')); ?>

                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 uppercase text-xs">
                                <?php echo e(str_replace('_', ' ', $row->method)); ?>

                            </td>
                            <td class="px-4 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->status === 'success'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        Sukses
                                    </span>
                                <?php elseif($row->status === 'failed'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800/50">
                                        Gagal
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                        Pending
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">payments</span>
                                    <p>Belum ada data pembayaran pelanggan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payments->hasPages()): ?>
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <?php echo e($payments->links(data: ['scrollTo' => false])); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\reseller-portal\billing\payments.blade.php ENDPATH**/ ?>