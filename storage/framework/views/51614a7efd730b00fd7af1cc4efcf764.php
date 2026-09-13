<div class="space-y-6">
    
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Neraca Saldo</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Laporan neraca saldo untuk periode tertentu</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export PDF
            </button>
            <button class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 md:flex-initial">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="filters.start_date" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex-1 md:flex-initial">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="filters.end_date" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
            </div>
            <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                Tampilkan
            </button>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $attributes = $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $component = $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Status Balance</p>
                <p class="text-lg font-semibold <?php echo e($totals['is_balanced'] ? 'text-green-600' : 'text-red-600'); ?>">
                    <?php echo e($totals['is_balanced'] ? 'Seimbang' : 'Tidak Seimbang'); ?>

                </p>
            </div>
            <div class="flex gap-8">
                <div class="text-right">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Debit</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($totals['debit'], 0, ',', '.')); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Kredit</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($totals['credit'], 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $attributes = $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $component = $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kode Akun</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Nama Akun</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trialBalance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        
                        <tr class="bg-slate-100 dark:bg-slate-800">
                            <td colspan="4" class="px-6 py-3">
                                <span class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($section['label']); ?></span>
                            </td>
                        </tr>
                        
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50">
                                <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400"><?php echo e($item['code']); ?></td>
                                <td class="px-6 py-3 text-sm text-slate-900 dark:text-slate-100"><?php echo e($item['name']); ?></td>
                                <td class="px-6 py-3 text-sm text-slate-900 dark:text-slate-100 text-right">
                                    <?php echo e($item['debit'] > 0 ? 'Rp ' . number_format($item['debit'], 0, ',', '.') : '-'); ?>

                                </td>
                                <td class="px-6 py-3 text-sm text-slate-900 dark:text-slate-100 text-right">
                                    <?php echo e($item['credit'] > 0 ? 'Rp ' . number_format($item['credit'], 0, ',', '.') : '-'); ?>

                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        
                        
                        <tr class="bg-slate-50 dark:bg-slate-900/50 font-semibold">
                            <td colspan="2" class="px-6 py-3 text-sm text-slate-900 dark:text-slate-100">Total <?php echo e($section['label']); ?></td>
                            <td class="px-6 py-3 text-sm text-slate-900 dark:text-slate-100 text-right">
                                <?php echo e($section['total_debit'] > 0 ? 'Rp ' . number_format($section['total_debit'], 0, ',', '.') : '-'); ?>

                            </td>
                            <td class="px-6 py-3 text-sm text-slate-900 dark:text-slate-100 text-right">
                                <?php echo e($section['total_credit'] > 0 ? 'Rp ' . number_format($section['total_credit'], 0, ',', '.') : '-'); ?>

                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    
                    
                    <tr class="bg-primary-50 font-bold">
                        <td colspan="2" class="px-6 py-4 text-lg text-primary-900">Grand Total</td>
                        <td class="px-6 py-4 text-lg text-primary-900 text-right">
                            Rp <?php echo e(number_format($totals['debit'], 0, ',', '.')); ?>

                        </td>
                        <td class="px-6 py-4 text-lg text-primary-900 text-right">
                            Rp <?php echo e(number_format($totals['credit'], 0, ',', '.')); ?>

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $attributes = $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $component = $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\reports\trial-balance.blade.php ENDPATH**/ ?>