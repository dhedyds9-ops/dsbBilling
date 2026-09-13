<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['package', 'index']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['package', 'index']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div class="pricing-card-wrapper flex <?php echo e($index > 0 ? 'sm:mt-0 mt-4' : ''); ?> <?php echo e($index > 1 ? 'lg:mt-0' : ''); ?>">
    <div class="glass-card <?php echo e($index == 1 ? 'best-seller-card bg-white dark:bg-slate-800 sm:scale-[1.05] shadow-2xl relative z-10 border-2' : 'hover:scale-[1.01] transition-all duration-300 border-2 border-transparent'); ?> p-6 sm:p-8 rounded-[22px] sm:rounded-[24px] flex flex-col w-full" style="<?php echo e($index == 1 ? 'border-color: var(--ds-primary); box-shadow: 0 30px 60px -20px rgba(30,136,229,0.32);' : ''); ?>">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($index == 1): ?>
            <div class="price-best-seller-badge">Best Seller</div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <p class="uppercase text-[12px] font-bold mb-3 tracking-[0.14em] text-ds-primary">
            <?php echo e($package->service_type === 'pppoe' ? 'Rumahan' : ($package->service_type === 'hotspot' ? 'Member' : 'Voucher')); ?>

        </p>
        <h3 class="font-bold text-[20px] mb-1" style="color: var(--ds-on-surface);"><?php echo e($package->name); ?></h3>
        
        <div class="flex items-baseline gap-1 mb-5 sm:mb-6">
            <span class="text-[15px]" style="color: var(--ds-on-surface-variant);">Rp</span>
            <span class="font-black text-[30px] sm:text-[32px] leading-none" style="color: var(--ds-on-surface);"><?php echo e(number_format($package->base_price, 0, ',', '.')); ?></span>
            <span class="text-[13px] sm:text-[14px]" style="color: var(--ds-on-surface-variant);">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($package->package_type === 'time_based'): ?>
                    / <?php echo e($package->duration_value); ?> <?php echo e($package->duration_unit === 'hours' ? 'Jam' : 'Hari'); ?>

                <?php elseif($package->package_type === 'quota_based'): ?>
                    / <?php echo e($package->quota_value); ?> <?php echo e($package->quota_unit); ?>

                <?php else: ?>
                    / <?php echo e($package->validity_value ?? 30); ?> <?php echo e($package->validity_unit === 'hours' ? 'Jam' : ($package->validity_unit === 'months' ? 'Bulan' : 'Hari')); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </span>
        </div>
        
        <ul class="space-y-2.5 sm:space-y-3 mb-8 sm:mb-10 flex-grow text-[15px] sm:text-[16px]" style="color: var(--ds-on-surface);">
            <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span>Speed up to <?php echo e($package->download_speed); ?> Mbps</li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($package->description): ?>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span><?php echo e(Str::limit($package->description, 50)); ?></li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($package->package_type === 'quota_based'): ?>
                    Kuota <?php echo e($package->quota_value); ?> <?php echo e($package->quota_unit); ?>

                <?php else: ?>
                    <?php echo e($package->fup_enabled ? 'FUP ' . $package->fup_threshold . 'GB' : 'Unlimited Quota'); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($package->max_devices > 0): ?>
                <li class="flex items-center gap-2"><span class="material-symbols-outlined fill text-ds-secondary text-[20px] sm:text-[22px] flex-shrink-0">check_circle</span><?php echo e($package->max_devices); ?> Perangkat</li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>
        <a href="<?php echo e(route('customer.login')); ?>" class="w-full py-3 <?php echo e($index == 1 ? 'btn-primary-solid text-white shadow-lg border-transparent' : 'border-2 text-ds-primary hover:bg-ds-primary hover:text-white'); ?> font-bold rounded-xl text-center transition active:scale-95 min-h-[48px] flex items-center justify-center" style="<?php echo e($index != 1 ? 'border-color: var(--ds-primary);' : ''); ?>">Pilih Paket</a>
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\components\landing-package-card.blade.php ENDPATH**/ ?>