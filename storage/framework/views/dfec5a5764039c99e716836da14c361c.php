
<div class="h-full flex flex-col overflow-hidden noc-bg" wire:poll.60000ms x-data="{ tab: '<?php echo e($activeTab); ?>' }">


<div class="flex-none px-3 py-2 border-b noc-border flex items-center gap-3 flex-wrap noc-panel-bg">
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('noc.olts.index')); ?>" class="noc-muted hover:noc-text-secondary text-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="text-base font-bold noc-text"><?php echo e($olt->name); ?></h1>
        <span class="text-xs noc-muted noc-mono"><?php echo e($olt->code ?? ''); ?></span>
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
        <span class="noc-muted">IP: <span class="noc-mono noc-text-secondary"><?php echo e($olt->ip_address); ?></span></span>
        <span class="noc-muted">Model: <span class="noc-text-secondary"><?php echo e($olt->vendor->name ?? '-'); ?> <?php echo e($olt->model ?? ''); ?></span></span>
        <span class="noc-muted">Uptime: <span class="noc-mono noc-text-secondary"><?php echo e($olt->uptime_text ?? '-'); ?></span></span>
        <span class="noc-muted">Temp:
            <span class="noc-mono <?php echo e($olt->temperature > 60 ? 'text-red-500' : 'text-emerald-500'); ?>"><?php echo e($olt->temperature ?? '-'); ?> °C</span>
        </span>
        
        <button wire:click="syncOlt" wire:loading.attr="disabled" class="btn btn-sm btn-primary ml-2 py-0.5 px-2 text-xs">
            <i class="bi bi-arrow-repeat" wire:loading.class="animate-spin"></i> Sync Status
        </button>
    </div>
</div>


<div class="flex-none px-3 border-b noc-border flex items-center gap-1 overflow-x-auto noc-scroll noc-panel-bg">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['overview' => 'Overview', 'pon' => 'PON Ports', 'onus' => 'ONUs', 'traffic' => 'Traffic', 'alarms' => 'Alarms', 'events' => 'Events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <button wire:click="setTab('<?php echo e($key); ?>')"
        class="px-3 py-2 text-xs whitespace-nowrap border-b-2 transition-colors
               <?php echo e($activeTab === $key ? 'noc-tab-active' : 'noc-tab-inactive'); ?>">
        <?php echo e($label); ?>

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
                <div class="flex justify-between"><dt class="noc-muted">Code</dt><dd class="noc-mono noc-text-secondary"><?php echo e($olt->code ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">IP Address</dt><dd class="noc-mono noc-text-secondary"><?php echo e($olt->ip_address); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">SNMP Port</dt><dd class="noc-mono noc-text-secondary"><?php echo e($olt->snmp_port ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Firmware</dt><dd class="noc-mono noc-text-secondary"><?php echo e($olt->firmware_version ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">POP</dt><dd class="noc-text-secondary"><?php echo e($olt->pop->name ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Last Polled</dt><dd class="noc-text-secondary"><?php echo e($olt->last_polled_at ? $olt->last_polled_at->diffForHumans() : '-'); ?></dd></div>
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

                <div class="noc-summary-label">PON Ports</div>
                <div class="noc-summary-value text-blue-500 noc-mono"><?php echo e($olt->pon_ports_count ?? 0); ?></div>
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

                <div class="noc-summary-label">Total ONU</div>
                <div class="noc-summary-value noc-mono"><?php echo e($olt->onus_count ?? 0); ?></div>
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

                <div class="noc-summary-label">Active ONU</div>
                <div class="noc-summary-value text-emerald-500 noc-mono"><?php echo e($olt->active_onu_count ?? 0); ?></div>
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
                <div class="noc-summary-value noc-mono <?php echo e($olt->temperature > 60 ? 'text-red-500' : 'text-emerald-500'); ?>"><?php echo e($olt->temperature ?? '-'); ?><span class="text-sm">°C</span></div>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($impact['customer_count'] ?? 0) > 0): ?>
        <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['class' => 'flex-none w-80 border-red-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-none w-80 border-red-500']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <span class="text-xs font-bold text-red-500 uppercase tracking-widest"><i class="bi bi-exclamation-triangle-fill"></i> Impact if Offline</span>
             <?php $__env->endSlot(); ?>
            <dl class="space-y-2 text-xs">
                <div class="flex justify-between"><dt class="noc-muted">Customers</dt><dd class="noc-mono text-red-500 font-bold"><?php echo e($impact['customer_count']); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Services</dt><dd class="noc-mono text-red-500 font-bold"><?php echo e($impact['service_count']); ?></dd></div>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'pon'): ?>
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
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Port Name</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Code</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Type</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);">Total ONU</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);">Active ONU</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ponPorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $port): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  font-medium noc-text"><?php echo e($port->name); ?></td>
                        <td class="p-3  font-mono noc-muted"><?php echo e($port->code); ?></td>
                        <td class="p-3  uppercase noc-muted"><?php echo e($port->type); ?></td>
                        <td class="p-3">
                            <?php if (isset($component)) { $__componentOriginalb2f7f1aacbef06468a34871d3105efae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2f7f1aacbef06468a34871d3105efae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.stat-badge','data' => ['status' => $port->status === 'active' ? 'ONLINE' : 'OFFLINE']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.stat-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($port->status === 'active' ? 'ONLINE' : 'OFFLINE')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                <?php echo e($port->status === 'active' ? 'UP' : 'DOWN'); ?>

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
                        <td class="p-3  text-right noc-mono"><?php echo e($port->onus_count); ?></td>
                        <td class="p-3  text-right noc-mono text-emerald-500"><?php echo e($port->active_onu_count); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'onus'): ?>
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
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Name</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">SN</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Port / ODP</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Status</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Rx Power</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Last Seen</th>
                        <th class="p-3 font-semibold text-right" style="border-color: var(--noc-border); color: var(--noc-muted);"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $onus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $onu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  font-medium noc-text"><?php echo e($onu->name); ?></td>
                        <td class="p-3  font-mono noc-muted"><?php echo e($onu->serial_number); ?></td>
                        <td class="p-3  noc-muted">
                            <div><?php echo e($onu->ponPort->name ?? '-'); ?></div>
                            <div class="text-[10px]"><?php echo e($onu->odp->name ?? ''); ?></div>
                        </td>
                        <td class="p-3">
                            <?php if (isset($component)) { $__componentOriginalb2f7f1aacbef06468a34871d3105efae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2f7f1aacbef06468a34871d3105efae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.stat-badge','data' => ['status' => $this->getOnuStatus($onu)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.stat-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getOnuStatus($onu))]); ?>
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
                        <td class="p-3  font-mono <?php echo e(($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -27) ? 'text-red-500' : 'text-emerald-500'); ?>">
                            <?php echo e($onu->rx_power_dbm ?? '-'); ?> dBm
                        </td>
                        <td class="p-3  noc-muted"><?php echo e($onu->last_seen_at?->diffForHumans(short: true) ?? '-'); ?></td>
                        <td class="p-3  text-right">
                            <a href="<?php echo e(route('noc.onus.show', $onu->id)); ?>" class="text-primary-600 hover:underline">View</a>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="7" class="p-3  text-center noc-muted">No ONUs found.</td>
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
        <div class="mt-2 flex-none">
            <?php echo e($onus->links(data: ['scrollTo' => false])); ?>

        </div>
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

                            <div class="text-[10px] noc-muted"><?php echo e($alarm->source_name); ?></div>
                        </td>
                        <td class="p-3  noc-muted"><?php echo e($alarm->started_at->format('M d H:i')); ?></td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium noc-badge-info">OPEN</span>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="4" class="p-3  text-center noc-muted">No active alarms.</td>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'traffic' || $activeTab === 'events'): ?>
    <div class="p-3 h-full overflow-hidden flex flex-col justify-center items-center text-center">
        <i class="bi bi-tools text-4xl noc-muted mb-2 opacity-50"></i>
        <div class="noc-muted opacity-70"><?php echo e(ucfirst($activeTab)); ?> monitoring module is under construction.</div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>

</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\noc\olt\show.blade.php ENDPATH**/ ?>