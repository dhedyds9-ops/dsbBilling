<?php $__env->startSection('header_title', 'Histori Koneksi'); ?>

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Riwayat Sesi Internet</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catatan login dan pemakaian kuota perangkat Anda.</p>
    </div>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                // Calculate Duration
                $duration = '-';
                $isOnline = false;
                if ($session->acct_session_time > 0) {
                    $hours = floor($session->acct_session_time / 3600);
                    $minutes = floor(($session->acct_session_time / 60) % 60);
                    $seconds = $session->acct_session_time % 60;
                    $duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                } elseif (!$session->acct_stop_time) {
                    $duration = 'Sedang Online';
                    $isOnline = true;
                }

                // Calculate Quota (Bytes to MB/GB)
                $totalBytes = $session->acct_input_octets + $session->acct_output_octets;
                $quota = '-';
                if ($totalBytes > 0) {
                    if ($totalBytes >= 1073741824) {
                        $quota = number_format($totalBytes / 1073741824, 2) . ' GB';
                    } elseif ($totalBytes >= 1048576) {
                        $quota = number_format($totalBytes / 1048576, 2) . ' MB';
                    } elseif ($totalBytes >= 1024) {
                        $quota = number_format($totalBytes / 1024, 2) . ' KB';
                    } else {
                        $quota = $totalBytes . ' B';
                    }
                }
            ?>

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/60 relative overflow-hidden">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOnline): ?>
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                <?php else: ?>
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-300 dark:bg-slate-600"></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <div class="pl-2">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5"><?php echo e($session->username); ?></p>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-slate-100">MAC: <span class="font-mono"><?php echo e($session->calling_station_id ?? '-'); ?></span></h3>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOnline): ?>
                            <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 flex items-center gap-1 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                            </span>
                        <?php else: ?>
                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                OFFLINE
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-y-3 gap-x-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 mb-2">
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">IP Address</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 font-mono"><?php echo e($session->framed_ip_address ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Total Kuota</p>
                            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400"><?php echo e($quota); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Mulai Login</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300"><?php echo e($session->acct_start_time ? date('d/m/Y H:i', strtotime($session->acct_start_time)) : '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Durasi</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300"><?php echo e($duration); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm">
                <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3 block">history</span>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada riwayat koneksi.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($sessions, 'links')): ?>
        <div class="mt-6">
            <?php echo e($sessions->links('pagination::tailwind')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH D:\dsBilling\resources\views\livewire\customer-portal\connection-info.blade.php ENDPATH**/ ?>