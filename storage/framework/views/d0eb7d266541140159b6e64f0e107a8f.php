<?php $__env->startSection('header_title', 'Tugas Troubleshooting'); ?>

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] relative pb-24">
    <!-- Tabs -->
    <div class="flex items-center gap-2 mb-6 bg-slate-200/50 dark:bg-slate-800/50 p-1 rounded-xl">
        <button wire:click="switchTab('open')" 
            class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all <?php echo e($activeTab === 'open' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300'); ?>">
            Tugas Aktif
        </button>
        <button wire:click="switchTab('resolved')" 
            class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all <?php echo e($activeTab === 'resolved' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300'); ?>">
            Riwayat
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-xl flex gap-2 items-center text-sm border border-emerald-100">
            <span class="material-symbols-outlined">check_circle</span>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/60 relative overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->priority === 'critical' || $ticket->priority === 'high'): ?>
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
            <?php else: ?>
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="pl-2">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm line-clamp-1 pr-2">#<?php echo e($ticket->id); ?> - <?php echo e($ticket->title); ?></h3>
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold 
                        <?php echo e($ticket->status === 'resolved' || $ticket->status === 'closed' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700'); ?>">
                        <?php echo e(ucfirst($ticket->status)); ?>

                    </span>
                </div>
                
                <div class="mb-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2"><?php echo e($ticket->description); ?></p>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 mb-3 border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">person</span>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300"><?php echo e($ticket->customer->name ?? 'Pelanggan'); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">home</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1"><?php echo e($ticket->customer->address ?? '-'); ?></span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="text-[10px] text-slate-400">
                        Masuk: <?php echo e($ticket->created_at->format('d M H:i')); ?>

                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'open'): ?>
                        <button wire:click="markResolved(<?php echo e($ticket->id); ?>)" wire:confirm="Yakin ingin menyelesaikan tiket ini?" class="text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">task_alt</span>
                            Selesai
                        </button>
                    <?php else: ?>
                        <div class="text-[10px] text-emerald-500 font-medium">
                            Selesai: <?php echo e($ticket->resolved_at ? $ticket->resolved_at->format('d M H:i') : '-'); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm">
            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3 block">assignment_turned_in</span>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada tugas saat ini.</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="mt-6">
        <?php echo e($tickets->links('pagination::tailwind')); ?>

    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\isp\technician\tickets\index.blade.php ENDPATH**/ ?>