
<div class="h-full flex flex-col overflow-hidden noc-bg" wire:poll.60000ms>


<div class="flex-none px-3 py-2 border-b noc-border flex items-center gap-3 flex-wrap noc-panel-bg">
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('noc.onus.index')); ?>" class="noc-muted hover:noc-text-secondary text-sm"><i class="bi bi-arrow-left"></i></a>
        <h1 class="text-base font-bold noc-text"><?php echo e($onu->name ?? $onu->serial_number); ?></h1>
        <span class="text-xs noc-muted noc-mono"><?php echo e($onu->serial_number); ?></span>
        <?php if (isset($component)) { $__componentOriginalb2f7f1aacbef06468a34871d3105efae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2f7f1aacbef06468a34871d3105efae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.stat-badge','data' => ['status' => $status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.stat-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb2f7f1aacbef06468a34871d3105efae)): ?>
<?php $attributes = $__attributesOriginalb2f7f1aacbef06468a34871d3105efae; ?>
<?php unset($__attributesOriginalb2f7f1aacbef06468a34871d3105efae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb2f7f1aacbef06468a34871d3105efae)): ?>
<?php $component = $__componentOriginalb2f7f1aacbef06468a34871d3105efae; ?>
<?php unset($__componentOriginalb2f7f1aacbef06468a34871d3105efae); ?>
<?php endif; ?>
    </div>
    <div class="flex items-center gap-3 text-xs ml-auto">
        <span class="noc-muted">OLT: <span class="noc-text-secondary"><?php echo e($onu->olt->name ?? '-'); ?></span></span>
        <span class="noc-muted">PON: <span class="noc-mono noc-text-secondary"><?php echo e($onu->ponPort->name ?? '-'); ?></span></span>
        <span class="noc-muted">ODP: <span class="noc-text-secondary"><?php echo e($onu->odp->code ?? '-'); ?></span></span>
        <span class="noc-muted">RX: <span class="noc-mono font-medium <?php echo e($rxClass); ?>"><?php echo e($onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm-1) . ' dBm' : '-'); ?></span></span>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
        <button wire:click="syncTR069" wire:loading.attr="disabled" class="btn btn-sm btn-outline-secondary ml-2 py-0.5 px-2 text-xs">
            <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin"></i> TR-069
        </button>
        <button wire:click="rebootOnu" wire:confirm="Are you sure you want to reboot this ONU ?? " wire:loading.attr="disabled" class="btn btn-sm btn-danger py-0.5 px-2 text-xs">
            <i class="bi bi-power"></i> Reboot
        </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>


<div class="flex-none px-3 border-b noc-border flex items-center gap-1 overflow-x-auto noc-scroll noc-panel-bg">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['overview' => 'Overview', 'optical' => 'Optical History', 'service' => 'Service', 'provisioning' => 'Provisioning', 'alarms' => 'Alarms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <button wire:click="setTab('<?php echo e($k); ?>')"
        class="px-3 py-2 text-xs whitespace-nowrap border-b-2 transition-colors
               <?php echo e($activeTab === $k ? 'noc-tab-active' : 'noc-tab-inactive'); ?>">
        <?php echo e($l); ?>

    </button>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>


<div class="flex-1 overflow-hidden noc-scroll">

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'overview'): ?>
    <div class="p-3 flex gap-3 overflow-auto noc-scroll h-full">
        
        <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['class' => 'flex-none w-80']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-none w-80']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <span class="text-xs font-bold noc-text-secondary uppercase tracking-widest">Device Info</span>
             <?php $__env->endSlot(); ?>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Serial</dt><dd class="noc-mono noc-text-secondary"><?php echo e($onu->serial_number); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">MAC</dt><dd class="noc-mono noc-text-secondary"><?php echo e($onu->mac_address ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Model</dt><dd class="noc-text-secondary"><?php echo e($onu->model ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Vendor</dt><dd class="noc-text-secondary"><?php echo e($onu->vendor->name ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Firmware</dt><dd class="noc-mono noc-text-secondary"><?php echo e($onu->firmware_version ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Hardware</dt><dd class="noc-mono noc-text-secondary"><?php echo e($onu->hardware_version ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Provision</dt><dd class="noc-text-secondary"><?php echo e($onu->provision_status ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Provisioned</dt><dd class="noc-text-secondary"><?php echo e($onu->provisioned_at ? $onu->provisioned_at->diffForHumans() : '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Last Seen</dt><dd class="noc-text-secondary"><?php echo e($onu->last_seen_at ? $onu->last_seen_at->diffForHumans() : '-'); ?></dd></div>
            </dl>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>

        
        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3 content-start">
            <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="noc-summary-label">RX Power</div>
                <div class="noc-summary-value noc-mono <?php echo e($rxClass); ?>"><?php echo e($onu->rx_power_dbm !== null ? number_format($onu->rx_power_dbm-1) : '-'); ?><span class="text-sm"> dBm</span></div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="noc-summary-label">TX Power</div>
                <div class="noc-summary-value noc-mono"><?php echo e($onu->tx_power_dbm !== null ? number_format($onu->tx_power_dbm-1) : '-'); ?><span class="text-sm"> dBm</span></div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="noc-summary-label">SNR</div>
                <div class="noc-summary-value text-blue-500 noc-mono"><?php echo e($onu->snr_db ?? '-'); ?><span class="text-sm"> dB</span></div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="noc-summary-label">Temperature</div>
                <div class="noc-summary-value noc-mono"><?php echo e($onu->temperature ?? '-'); ?><span class="text-sm"> °C</span></div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canManage): ?>
            <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['class' => 'col-span-2 border-red-500/30']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'col-span-2 border-red-500/30']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                 <?php $__env->slot('header', null, []); ?> 
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Danger Zone</span>
                 <?php $__env->endSlot(); ?>
                <div class="flex gap-2">
                    <button wire:click="factoryResetOnu" wire:confirm="WARNING: This will wipe all configuration on the ONU. Proceed ?? " class="btn btn-sm btn-outline-danger">Factory Reset TR-069</button>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($onu->customerService): ?>
        <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['class' => 'flex-none w-80']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-none w-80']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <span class="text-xs font-bold text-blue-500 uppercase tracking-widest">Layanan Pelanggan</span>
             <?php $__env->endSlot(); ?>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Pelanggan</dt><dd class="noc-text-secondary font-medium"><?php echo e($onu->customerService->customer->name ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Kode</dt><dd class="noc-mono noc-muted"><?php echo e($onu->customerService->customer->code ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Tipe</dt><dd class="noc-text-secondary"><?php echo e(strtoupper($onu->customerService->service_type ?? '-')); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Status</dt><dd class="noc-text-secondary"><?php echo e($onu->customerService->status ?? '-'); ?></dd></div>
            </dl>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'optical'): ?>
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['noPadding' => true,'class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['noPadding' => true,'class' => 'flex-1']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Time</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Rx Power</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Tx Power</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">SNR</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Temp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $signals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  noc-muted"><?php echo e($s->measured_at->format('Y-m-d H:i:s')); ?></td>
                        <td class="p-3">
                            <?php if (isset($component)) { $__componentOriginalb2f7f1aacbef06468a34871d3105efae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2f7f1aacbef06468a34871d3105efae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.stat-badge','data' => ['status' => $s->status === 'online' ? 'ONLINE' : ($s->status === 'los' ? 'LOS' : 'OFFLINE')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.stat-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($s->status === 'online' ? 'ONLINE' : ($s->status === 'los' ? 'LOS' : 'OFFLINE'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb2f7f1aacbef06468a34871d3105efae)): ?>
<?php $attributes = $__attributesOriginalb2f7f1aacbef06468a34871d3105efae; ?>
<?php unset($__attributesOriginalb2f7f1aacbef06468a34871d3105efae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb2f7f1aacbef06468a34871d3105efae)): ?>
<?php $component = $__componentOriginalb2f7f1aacbef06468a34871d3105efae; ?>
<?php unset($__componentOriginalb2f7f1aacbef06468a34871d3105efae); ?>
<?php endif; ?>
                        </td>
                        <td class="p-3  font-mono <?php echo e(($s->rx_power_dbm !== null && $s->rx_power_dbm < -27) ? 'text-red-500' : 'text-emerald-500'); ?>"><?php echo e($s->rx_power_dbm ?? '-'); ?> dBm</td>
                        <td class="p-3  font-mono noc-muted"><?php echo e($s->tx_power_dbm ?? '-'); ?> dBm</td>
                        <td class="p-3  font-mono noc-muted"><?php echo e($s->snr_db ?? '-'); ?> dB</td>
                        <td class="p-3  font-mono noc-muted"><?php echo e($s->temperature ?? '-'); ?> °C</td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="6" class="p-3  text-center noc-muted">No signal history available.</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'alarms'): ?>
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['noPadding' => true,'class' => 'flex-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['noPadding' => true,'class' => 'flex-1']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700"  style="background-color: var(--noc-subpanel);">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Level</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Title</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Started</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $alarms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3">
                            <?php if (isset($component)) { $__componentOriginalb2f7f1aacbef06468a34871d3105efae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2f7f1aacbef06468a34871d3105efae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.stat-badge','data' => ['status' => strtoupper($alarm->level)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.stat-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(strtoupper($alarm->level))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb2f7f1aacbef06468a34871d3105efae)): ?>
<?php $attributes = $__attributesOriginalb2f7f1aacbef06468a34871d3105efae; ?>
<?php unset($__attributesOriginalb2f7f1aacbef06468a34871d3105efae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb2f7f1aacbef06468a34871d3105efae)): ?>
<?php $component = $__componentOriginalb2f7f1aacbef06468a34871d3105efae; ?>
<?php unset($__componentOriginalb2f7f1aacbef06468a34871d3105efae); ?>
<?php endif; ?>
                        </td>
                        <td class="p-3  font-medium noc-text">
                            <?php echo e($alarm->title); ?>

                            <div class="text-[10px] noc-muted truncate max-w-sm"><?php echo e($alarm->description); ?></div>
                        </td>
                        <td class="p-3  noc-muted"><?php echo e($alarm->started_at->format('M d H:i')); ?></td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium noc-badge-info">OPEN</span>
                        </td>
                        <td class="p-3  text-right">
                            <a href="<?php echo e(route('noc.alarms.show', $alarm->id)); ?>" class="text-primary-600 hover:underline">View</a>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="5" class="p-3  text-center noc-muted">No active alarms. ONU is healthy.</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $attributes = $__attributesOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__attributesOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45646619fa9c4f8799ad78d440d816d7)): ?>
<?php $component = $__componentOriginal45646619fa9c4f8799ad78d440d816d7; ?>
<?php unset($__componentOriginal45646619fa9c4f8799ad78d440d816d7); ?>
<?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($activeTab, ['service', 'provisioning'])): ?>
    <div class="p-3 h-full flex flex-col items-center justify-center text-center">
        <i class="bi bi-tools text-4xl noc-muted mb-2 opacity-50"></i>
        <div class="noc-muted opacity-70">
            Modul <?php echo e(ucfirst($activeTab)); ?> sedang dikembangkan dan akan terintegrasi langsung.
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>

</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\noc\onu\show.blade.php ENDPATH**/ ?>