<div class="space-y-6">
    
    <div class="flex items-center gap-4">
        <a href="<?php echo e(route('crm.installations.index')); ?>" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100"><?php echo e($installation->customer_name); ?></h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    <?php if($installation->status === 'scheduled'): ?> bg-blue-100 dark:bg-blue-900/50 text-blue-600
                    <?php elseif($installation->status === 'in_progress'): ?> bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600
                    <?php elseif($installation->status === 'completed'): ?> bg-green-100 dark:bg-green-900/50 text-green-600
                    <?php else: ?> bg-red-100 dark:bg-red-900/50 text-red-600
                    <?php endif; ?>
                ">
                    <?php echo e(ucfirst(str_replace('_', ' ', $installation->status))); ?>

                </span>
                <span class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($installation->installation_date?->format('d/m/Y')); ?></span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('crm.installations.edit', $installation->id)); ?>" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
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

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi Installation</h3>
                 <?php $__env->endSlot(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Nama Pelanggan</label>
                        <p class="text-slate-900 dark:text-slate-100"><?php echo e($installation->customer_name); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Tanggal Instalasi</label>
                            <p class="text-slate-900 dark:text-slate-100"><?php echo e($installation->installation_date?->format('d/m/Y') ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Teknisi</label>
                            <p class="text-slate-900 dark:text-slate-100"><?php echo e($installation->technician ?? '-'); ?></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Catatan</label>
                        <p class="text-slate-900 dark:text-slate-100"><?php echo e($installation->notes ?? '-'); ?></p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Timeline</h3>
                 <?php $__env->endSlot(); ?>
                <div class="space-y-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full 
                                    <?php if($item['type'] === 'create'): ?> bg-primary-500
                                    <?php else: ?> bg-slate-400
                                    <?php endif; ?>
                                "></div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                                    <div class="w-0.5 flex-1 bg-slate-200"></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="flex-1 pb-6">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1"><?php echo e($item['date']->format('d/m/Y H:i')); ?></p>
                                <p class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($item['title']); ?></p>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1"><?php echo e($item['description']); ?></p>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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

        <div class="space-y-6">
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

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Aktivitas Terbaru</h3>
                 <?php $__env->endSlot(); ?>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-slate-600 dark:text-slate-400"><?php echo e(substr($activity['user'], 0, 1)); ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-900 dark:text-slate-100">
                                    <span class="font-medium"><?php echo e($activity['user']); ?></span> <?php echo e($activity['action']); ?>

                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-full text-xs"><?php echo e($activity['module']); ?></span>
                                    <?php echo e($activity['time']); ?>

                                </p>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\crm\installation\show.blade.php ENDPATH**/ ?>