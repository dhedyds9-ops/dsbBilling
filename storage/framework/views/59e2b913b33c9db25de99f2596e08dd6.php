<div class="space-y-4 p-4">

    
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined" style="font-size:120px">engineering</span>
        </div>
        <div class="relative z-10">
            <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wider mb-1">Selamat Datang</p>
            <h1 class="text-2xl font-black mb-0.5"><?php echo e(auth()->user()->name); ?></h1>
            <p class="text-indigo-200 text-sm"><?php echo e(now()->translatedFormat('l, d F Y')); ?></p>
        </div>
        <div class="relative z-10 mt-4 flex gap-2">
            <a href="<?php echo e(route('technician.my-jobs.index')); ?>"
               class="flex-1 py-2.5 bg-white dark:bg-slate-800/20 hover:bg-white dark:bg-slate-800/30 border border-white/30 rounded-xl text-white text-sm font-bold flex items-center justify-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined" style="font-size:18px">assignment</span>
                Tugas Saya
            </a>
            <a href="<?php echo e(route('technician.installation.wizard')); ?>"
               class="flex-1 py-2.5 bg-amber-400 hover:bg-amber-300 text-amber-950 rounded-xl text-sm font-bold flex items-center justify-center gap-1.5 transition-colors shadow-sm">
                <span class="material-symbols-outlined" style="font-size:18px">build_circle</span>
                Instalasi
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white dark:bg-slate-800 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 flex items-center justify-center mx-auto mb-2">
                <span class="material-symbols-outlined" style="font-size:18px">sync</span>
            </div>
            <p class="text-2xl font-black text-slate-800 dark:text-slate-200"><?php echo e($recentInstallations->where('status', 'running')->count()); ?></p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">Proses</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                <span class="material-symbols-outlined" style="font-size:18px">task_alt</span>
            </div>
            <p class="text-2xl font-black text-slate-800 dark:text-slate-200"><?php echo e($recentInstallations->where('status', 'completed')->count()); ?></p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">Selesai</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm text-center">
            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 text-red-600 flex items-center justify-center mx-auto mb-2">
                <span class="material-symbols-outlined" style="font-size:18px">warning</span>
            </div>
            <p class="text-2xl font-black text-slate-800 dark:text-slate-200"><?php echo e($recentInstallations->where('status', 'failed')->count()); ?></p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">Gagal</p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">badge</span>
            <h2 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider">Layanan Karyawan</h2>
        </div>
        <div class="p-4 flex gap-3">
            <a href="<?php echo e(route('technician.payroll.index')); ?>" class="flex-1 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-xl p-3 flex flex-col items-center justify-center gap-1.5 transition-colors border border-indigo-100 dark:border-indigo-800 text-center">
                <span class="material-symbols-outlined" style="font-size:28px">request_quote</span>
                <span class="text-xs font-bold">Slip Gaji</span>
            </a>
            <!-- Tambahkan menu lain di sini nanti -->
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">history</span>
            <h2 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider">Riwayat Pemasangan</h2>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentInstallations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pipeline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full shrink-0 <?php echo e($pipeline->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600' : ($pipeline->status === 'failed' ? 'bg-red-100 dark:bg-red-900/50 text-red-600' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-600')); ?> flex items-center justify-center">
                        <span class="material-symbols-outlined" style="font-size:20px">
                            <?php echo e($pipeline->status === 'completed' ? 'check_circle' : ($pipeline->status === 'failed' ? 'error' : 'sync')); ?>

                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-900 dark:text-slate-100 text-sm truncate">
                            <?php echo e($pipeline->serviceInstance->customerService->customer->name ?? 'Unknown'); ?>

                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            <?php echo e($pipeline->created_at->format('d/m/Y H:i')); ?>

                        </p>
                    </div>
                    <div class="shrink-0 flex flex-col items-end gap-1.5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pipeline->status === 'completed'): ?>
                            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-lg text-[10px] font-bold uppercase">Selesai</span>
                        <?php elseif($pipeline->status === 'failed'): ?>
                            <span class="px-2 py-0.5 bg-red-50 dark:bg-red-900/30 text-red-700 rounded-lg text-[10px] font-bold uppercase">Gagal</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 rounded-lg text-[10px] font-bold uppercase">Proses</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <a href="<?php echo e(route('technician.provisioning.show', $pipeline->id)); ?>"
                           class="text-indigo-600 dark:text-indigo-400 text-xs font-bold flex items-center gap-0.5 hover:underline">
                            Monitor
                            <span class="material-symbols-outlined" style="font-size:14px">chevron_right</span>
                        </a>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="p-10 text-center">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 dark:text-slate-400" style="font-size:32px">inbox</span>
                    </div>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">Belum ada riwayat</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Gunakan Instalasi Cepat untuk memulai.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

</div>
<?php /**PATH D:\dsBilling\resources\views/livewire/isp/technician/dashboard.blade.php ENDPATH**/ ?>