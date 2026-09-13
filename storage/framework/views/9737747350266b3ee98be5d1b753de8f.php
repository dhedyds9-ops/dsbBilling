<?php $__env->startSection('header_title', 'Monitor Provisioning'); ?>

<div class="space-y-4 p-4" wire:poll.3s>

    
    <?php
        $overallDone = ($status['pipeline_status'] ?? '') === 'completed';
        $overallFailed = ($status['pipeline_status'] ?? '') === 'failed';
    ?>
    <div class="p-4 rounded-2xl text-white shadow-md flex items-center gap-3
        <?php echo e($overallDone ? 'bg-gradient-to-r from-emerald-500 to-teal-600' : ($overallFailed ? 'bg-gradient-to-r from-red-500 to-rose-600' : 'bg-gradient-to-r from-indigo-500 to-blue-600')); ?>">
        <div class="w-12 h-12 bg-white dark:bg-slate-800/20 rounded-full flex items-center justify-center shrink-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overallDone): ?>
                <span class="material-symbols-outlined fill" style="font-size:28px">check_circle</span>
            <?php elseif($overallFailed): ?>
                <span class="material-symbols-outlined" style="font-size:28px">error</span>
            <?php else: ?>
                <span class="material-symbols-outlined animate-spin" style="font-size:28px">sync</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <p class="text-xs font-semibold opacity-80 uppercase tracking-wider">Status Provisioning</p>
            <p class="text-lg font-black"><?php echo e($overallDone ? 'SELESAI & ONLINE' : ($overallFailed ? 'GAGAL' : 'SEDANG BERJALAN...')); ?></p>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($status['customer_name'])): ?>
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Pelanggan</p>
        <p class="text-base font-black text-slate-900 dark:text-white"><?php echo e($status['customer_name']); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($status['username'])): ?>
        <div class="flex items-center gap-1.5 mt-1 text-sm text-slate-500 dark:text-slate-400">
            <span class="material-symbols-outlined text-indigo-400" style="font-size:14px">account_circle</span>
            <?php echo e($status['username']); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">fact_check</span>
            <h2 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider">Status Sistem</h2>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
            
            <div class="flex items-center justify-between px-4 py-3 <?php echo e(($status['onu_status'] ?? '') === 'completed' ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : ''); ?>">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined <?php echo e(($status['onu_status'] ?? '') === 'completed' ? 'text-emerald-500' : 'text-slate-400'); ?>" style="font-size:20px">router</span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">ONU / ONT Device</span>
                </div>
                <span class="text-[10px] font-black px-2.5 py-1 rounded-full <?php echo e(($status['onu_status'] ?? '') === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'); ?>">
                    <?php echo e(strtoupper($status['onu_status'] ?? 'PENDING')); ?>

                </span>
            </div>
            
            <div class="flex items-center justify-between px-4 py-3 <?php echo e(in_array($status['tr069_status'] ?? '', ['completed','skipped']) ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : ''); ?>">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined <?php echo e(in_array($status['tr069_status'] ?? '', ['completed','skipped']) ? 'text-emerald-500' : 'text-slate-400'); ?>" style="font-size:20px">settings_remote</span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">TR-069 (GenieACS)</span>
                </div>
                <span class="text-[10px] font-black px-2.5 py-1 rounded-full <?php echo e(in_array($status['tr069_status'] ?? '', ['completed','skipped']) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'); ?>">
                    <?php echo e(strtoupper($status['tr069_status'] ?? 'PENDING')); ?>

                </span>
            </div>
            
            <div class="flex items-center justify-between px-4 py-3 <?php echo e(($status['radius_status'] ?? '') === 'completed' ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : ''); ?>">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined <?php echo e(($status['radius_status'] ?? '') === 'completed' ? 'text-emerald-500' : 'text-slate-400'); ?>" style="font-size:20px">shield_locked</span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">PPPoE / RADIUS</span>
                </div>
                <span class="text-[10px] font-black px-2.5 py-1 rounded-full <?php echo e(($status['radius_status'] ?? '') === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'); ?>">
                    <?php echo e(strtoupper($status['radius_status'] ?? 'PENDING')); ?>

                </span>
            </div>
            
            <div class="flex items-center justify-between px-4 py-3 <?php echo e(($status['verification_status'] ?? '') === 'completed' ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : ''); ?>">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined <?php echo e(($status['verification_status'] ?? '') === 'completed' ? 'text-emerald-500' : 'text-slate-400'); ?>" style="font-size:20px">public</span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Koneksi Internet</span>
                </div>
                <span class="text-[10px] font-black px-2.5 py-1 rounded-full <?php echo e(($status['verification_status'] ?? '') === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'); ?>">
                    <?php echo e(($status['verification_status'] ?? '') === 'completed' ? 'ONLINE' : 'WAITING'); ?>

                </span>
            </div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">format_list_bulleted</span>
            <h2 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider">Log Proses</h2>
        </div>
        <div class="p-4 space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $status['steps'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex items-start gap-3">
                <div class="shrink-0 mt-0.5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($step['status'], ['completed','skipped'])): ?>
                        <div class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400" style="font-size:14px">check</span>
                        </div>
                    <?php elseif($step['status'] === 'failed'): ?>
                        <div class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400" style="font-size:14px">close</span>
                        </div>
                    <?php elseif($step['status'] === 'in_progress'): ?>
                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 animate-spin" style="font-size:14px">sync</span>
                        </div>
                    <?php else: ?>
                        <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600"></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <p class="text-sm font-bold <?php echo e($step['status'] === 'pending' ? 'text-slate-400 dark:text-slate-500' : 'text-slate-800 dark:text-slate-200'); ?>">
                        <?php echo e($step['name']); ?>

                    </p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step['error']): ?>
                        <p class="text-xs text-red-500 mt-1 bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded-lg border border-red-100 dark:border-red-900/50"><?php echo e($step['error']); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($status['can_activate'] ?? false) && ($status['customer_service_status'] ?? '') === 'active'): ?>
    <div class="p-4 bg-emerald-500 text-white rounded-2xl text-sm text-center font-bold shadow-md shadow-emerald-500/30 animate-pulse flex items-center justify-center gap-2">
        <span class="material-symbols-outlined" style="font-size:20px">celebration</span>
        INSTALASI SELESAI & ONLINE!
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\isp\technician\provisioning\show.blade.php ENDPATH**/ ?>