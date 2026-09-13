<?php $__env->startSection('header_title', 'Daftar Slip Gaji'); ?>

<div class="space-y-4 p-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payrolls->isEmpty()): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center border border-slate-200 dark:border-slate-700">
            <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 mb-2" style="font-size:48px">request_quote</span>
            <h3 class="text-slate-500 dark:text-slate-400 font-semibold text-sm">Belum ada data slip gaji</h3>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a href="<?php echo e(route('technician.payroll.show', $p->id)); ?>" class="block bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm active:scale-[0.98] transition-transform">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center">
                                <span class="material-symbols-outlined" style="font-size:20px">receipt_long</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm"><?php echo e(\Carbon\Carbon::createFromDate($p->period_year, $p->period_month, 1)->translatedFormat('F Y')); ?></h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Rp <?php echo e(number_format($p->net_salary, 0, ',', '.')); ?></p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400" style="font-size:20px">chevron_right</span>
                    </div>
                    <div class="flex gap-2 text-[10px] font-bold uppercase tracking-wider mt-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->status == 'paid'): ?>
                            <span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 px-2 py-1 rounded-md">Dibayar</span>
                        <?php else: ?>
                            <span class="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-1 rounded-md">Belum Dibayar</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views/livewire/isp/technician/payroll/index.blade.php ENDPATH**/ ?>