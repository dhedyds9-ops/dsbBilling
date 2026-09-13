<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginal1a2164c88256e2df02baa87be70e8a2b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.breadcrumbs','data' => ['breadcrumbs' => $this->breadcrumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->breadcrumbs)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $attributes = $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $component = $__componentOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">ACS Logs</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lihat log aktivitas ACS</p>
        </div>
    </div>

    
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
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Device</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($log->device?->serial_number ?? '-'); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    <?php echo e(ucfirst($log->type)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400"><?php echo e($log->message); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400"><?php echo e($log->created_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="delete(<?php echo e($log->id); ?>)" wire:confirm="Yakin ingin menghapus log ini?" class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg">Belum ada log</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH D:\dsBilling\resources\views\livewire\acs\log\index.blade.php ENDPATH**/ ?>