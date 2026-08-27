<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?php echo e(route('isp.hotspot-users.index')); ?>" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                    <?php echo e(substr($hotspotUser->username, 0, 1)); ?>

                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900"><?php echo e($hotspotUser->username); ?></h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            <?php if($hotspotUser->status === 'active'): ?> bg-green-100 text-green-600
                            <?php elseif($hotspotUser->status === 'inactive'): ?> bg-slate-100 text-slate-600
                            <?php elseif($hotspotUser->status === 'suspended'): ?> bg-yellow-100 text-yellow-600
                            <?php else: ?> bg-red-100 text-red-600
                            <?php endif; ?>">
                            <?php echo e(ucfirst($hotspotUser->status)); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('isp.hotspot-users.edit', $hotspotUser->id)); ?>" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                <h3 class="text-lg font-semibold text-slate-900">Informasi Hotspot User</h3>
             <?php $__env->endSlot(); ?>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Username</label>
                        <p class="text-slate-900 font-mono"><?php echo e($hotspotUser->username); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Service Profile</label>
                        <p class="text-slate-900"><?php echo e($hotspotUser->serviceProfile?->name ?? '-'); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Status</label>
                        <p class="text-slate-900"><?php echo e(ucfirst($hotspotUser->status)); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Dibuat Oleh</label>
                        <p class="text-slate-900"><?php echo e($hotspotUser->createdBy?->name ?? '-'); ?></p>
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
    </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\isp\hotspot-user\show.blade.php ENDPATH**/ ?>