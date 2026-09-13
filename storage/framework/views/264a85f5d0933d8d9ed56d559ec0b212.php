<?php $__env->startSection('header_title', 'Tagihan Saya'); ?>

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)]">
    
    <div class="mb-4">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Riwayat Tagihan</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar semua invoice dan status pembayaran Anda.</p>
    </div>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/60 relative overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->status === 'paid'): ?>
                <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500/10 rounded-bl-full flex items-start justify-end p-2">
                    <span class="material-symbols-outlined text-emerald-500 text-lg">check_circle</span>
                </div>
            <?php elseif($invoice->status === 'overdue'): ?>
                <div class="absolute top-0 right-0 w-16 h-16 bg-red-500/10 rounded-bl-full flex items-start justify-end p-2">
                    <span class="material-symbols-outlined text-red-500 text-lg">warning</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex justify-between items-start mb-3 pr-10">
                <div>
                    <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-0.5">INV-<?php echo e($invoice->invoice_number); ?></p>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></h3>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3">
                <div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Tanggal Terbit</p>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300"><?php echo e($invoice->issue_date?->format('d/m/Y') ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Jatuh Tempo</p>
                    <p class="text-xs font-semibold <?php echo e($invoice->status === 'overdue' ? 'text-red-600' : 'text-slate-700 dark:text-slate-300'); ?>"><?php echo e($invoice->due_date?->format('d/m/Y') ?? '-'); ?></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->status !== 'paid'): ?>
                    <a href="<?php echo e(route('customer-portal.billing.invoice-show', $invoice->id)); ?>" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-center py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Bayar Sekarang
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('customer-portal.billing.invoice-show', $invoice->id)); ?>" class="flex-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-center py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    Lihat Detail
                </a>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm">
            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3 block">receipt_long</span>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada tagihan.</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-6">
        <?php echo e($invoices->links('pagination::tailwind')); ?>

    </div>
</div><?php /**PATH D:\dsBilling\resources\views\livewire\customer-portal\billing\invoice-list.blade.php ENDPATH**/ ?>