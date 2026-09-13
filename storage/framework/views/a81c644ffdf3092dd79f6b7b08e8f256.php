<?php $__env->startSection('header_title', 'Dukungan & Tiket'); ?>

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] relative pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Riwayat Pengaduan</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pantau status tiket bantuan teknis Anda.</p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-5 p-4 rounded-xl flex items-start gap-3 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 border border-emerald-100">
            <span class="material-symbols-outlined shrink-0">check_circle</span>
            <div class="text-sm"><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <a href="#" class="block bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/60 active:scale-[0.98] transition-transform relative overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->priority === 'critical' || $ticket->priority === 'high'): ?>
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
            <?php else: ?>
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="pl-2">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm line-clamp-1 pr-2"><?php echo e($ticket->title); ?></h3>
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold 
                        <?php echo e($ticket->status === 'resolved' || $ticket->status === 'closed' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700'); ?>">
                        <?php echo e(ucfirst($ticket->status)); ?>

                    </span>
                </div>
                
                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-3">Kategori: <?php echo e($ticket->category); ?></p>

                <div class="flex items-center justify-between text-[10px] text-slate-400">
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                        <span><?php echo e($ticket->created_at->format('d M Y')); ?></span>
                    </div>
                    <div class="flex items-center text-indigo-500 font-medium">
                        Lihat Detail
                        <span class="material-symbols-outlined text-[14px] ml-0.5">chevron_right</span>
                    </div>
                </div>
            </div>
        </a>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm">
            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3 block">support_agent</span>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada tiket terbuka.</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-6">
        <?php echo e($tickets->links('pagination::tailwind')); ?>

    </div>

        <!-- Floating Action Button -->
    <a href="<?php echo e(route('customer-portal.support.ticket-create')); ?>" class="fixed bottom-20 right-4 sm:right-auto sm:ml-[360px] w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full flex items-center justify-center shadow-lg shadow-indigo-600/30 transition-transform active:scale-95 z-50">
        <span class="material-symbols-outlined text-[28px]">add</span>
    </a>
</div><?php /**PATH D:\dsBilling\resources\views\livewire\customer-portal\support\ticket-list.blade.php ENDPATH**/ ?>