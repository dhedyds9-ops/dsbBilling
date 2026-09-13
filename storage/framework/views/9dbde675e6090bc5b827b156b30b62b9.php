
<div class="h-full flex flex-col overflow-hidden noc-bg" wire:poll.60000ms>

<div class="flex-none px-3 py-2 border-b noc-border flex items-center gap-3 flex-wrap noc-panel-bg">
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('noc.routers.index')); ?>" class="noc-muted hover:noc-text-secondary text-sm"><i class="bi bi-arrow-left"></i></a>
        <h1 class="text-base font-bold noc-text"><?php echo e($router->name); ?></h1>
        <span class="text-xs noc-muted noc-mono"><?php echo e($router->code ?? ''); ?></span>
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
        <span class="noc-muted">IP: <span class="noc-mono noc-text-secondary"><?php echo e($router->ip_address); ?></span></span>
        <span class="noc-muted">API: <span class="noc-mono noc-text-secondary"><?php echo e($router->api_port ?? '-'); ?></span></span>
        <span class="noc-muted">Model: <span class="noc-text-secondary"><?php echo e($router->vendor->name ?? '-'); ?> <?php echo e($router->model ?? ''); ?></span></span>
        <span class="noc-muted">RouterOS: <span class="noc-mono noc-text-secondary"><?php echo e($router->routeros_version ?? '-'); ?></span></span>
        
        <button wire:click="rebootRouter" wire:confirm="Are you sure you want to reboot this router ?? " class="btn btn-sm btn-danger ml-2 py-0.5 px-2 text-xs">
            <i class="bi bi-power"></i> Reboot
        </button>
    </div>
</div>


<div class="flex-none px-3 border-b noc-border flex items-center gap-1 overflow-x-auto noc-scroll noc-panel-bg">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['overview' => 'Overview', 'interfaces' => 'Net Monitor', 'sessions' => 'Active Sessions', 'health' => 'Health History', 'traffic' => 'Traffic', 'alarms' => 'Alarms', 'logs' => 'Logs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
                <div class="flex justify-between"><dt class="noc-muted">Code</dt><dd class="noc-mono noc-text-secondary"><?php echo e($router->code ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Hostname</dt><dd class="noc-text-secondary"><?php echo e($router->hostname ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">IP Address</dt><dd class="noc-mono noc-text-secondary"><?php echo e($router->ip_address); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">API Port</dt><dd class="noc-mono noc-text-secondary"><?php echo e($router->api_port ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">COA Port</dt><dd class="noc-mono noc-text-secondary"><?php echo e($router->coa_port ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Model</dt><dd class="noc-text-secondary"><?php echo e($router->model ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Vendor</dt><dd class="noc-text-secondary"><?php echo e($router->vendor->name ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">POP</dt><dd class="noc-text-secondary"><?php echo e($router->pop->name ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">RouterOS</dt><dd class="noc-mono noc-text-secondary"><?php echo e($router->routeros_version ?? '-'); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Last Seen</dt><dd class="noc-text-secondary"><?php echo e($router->last_seen_at ? $router->last_seen_at->diffForHumans() : '-'); ?></dd></div>
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

                <div class="noc-summary-label">CPU Load</div>
                <div class="noc-summary-value noc-mono <?php echo e($log && $log->cpu_load > 80 ? 'text-red-500' : 'text-emerald-500'); ?>"><?php echo e($log->cpu_load ?? '-'); ?><span class="text-sm">%</span></div>
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

                <div class="noc-summary-label">Free Memory</div>
                <div class="noc-summary-value noc-mono text-blue-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log && $log->total_memory): ?>
                        <?php echo e(round($log->free_memory / 1024 / 1024- 1)); ?><span class="text-sm"> MB</span>
                    <?php else: ?> - <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

                <div class="noc-summary-label">Uptime</div>
                <div class="text-xl font-bold noc-text noc-mono" style="font-size:1.1rem;"><?php echo e($log->uptime ?? '-'); ?></div>
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

                <div class="noc-summary-label">Identity</div>
                <div class="text-sm font-bold noc-text truncate" title="<?php echo e($log->identity); ?>"><?php echo e($log->identity ?? '-'); ?></div>
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
                <div class="flex justify-between"><dt class="noc-muted">PPPoE</dt><dd class="noc-mono noc-text-secondary"><?php echo e($impact['breakdown']['pppoe'] ?? 0); ?></dd></div>
                <div class="flex justify-between"><dt class="noc-muted">Hotspot</dt><dd class="noc-mono noc-text-secondary"><?php echo e($impact['breakdown']['hotspot'] ?? 0); ?></dd></div>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'interfaces'): ?>
    <div class="h-full flex flex-col md:flex-row bg-[#0b1120]" wire:poll.2s>
        <!-- Sidebar: List of interfaces -->
        <div class="w-full md:w-64 border-r border-gray-800 flex flex-col h-full bg-[#111827]">
            <div class="p-3 border-b border-gray-800 flex justify-between items-center bg-[#1e293b]">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest"><i class="bi bi-diagram-3 mr-2"></i> Interfaces</h3>
            </div>
            <div class="flex-1 overflow-y-auto noc-scroll">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $liveInterfaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $iface): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div wire:click="toggleInterfaceSelection('<?php echo e($iface['name']); ?>')" class="p-2 border-b border-gray-800 cursor-pointer hover:bg-gray-800 flex justify-between items-center transition-colors <?php echo e(in_array($iface['name'], $selectedInterfaces) ? 'bg-gray-800 border-l-2 border-blue-500' : 'border-l-2 border-transparent'); ?>">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full <?php echo e($iface['status'] === 'link-up' ? 'bg-emerald-500' : 'bg-red-500'); ?>"></div>
                        <div>
                            <div class="text-xs font-bold text-gray-200"><?php echo e($iface['name']); ?></div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400"><?php echo e($iface['type']); ?></div>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($iface['name'], $selectedInterfaces)): ?>
                        <span class="text-[9px] bg-blue-500/20 text-blue-400 px-1.5 rounded">MONITOR</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
        
        <!-- Main Panel: Graphs -->
        <div class="flex-1 flex flex-col bg-[#0f172a] overflow-hidden" >
            <div class="p-3 border-b border-gray-800 flex justify-between items-center bg-[#1e293b]">
                <div class="flex gap-4">
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Router</div>
                        <div class="text-sm font-bold text-gray-200"><i class="bi bi-router mr-1 text-blue-500"></i> <?php echo e($router->hostname ?? $router->name); ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">IP</div>
                        <div class="text-sm font-bold text-gray-200"><?php echo e($router->ip_address); ?></div>
                    </div>
                </div>
                <div class="flex gap-4 text-right">
                    <div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Total Interfaces</div>
                        <div class="text-sm font-bold text-emerald-400"><?php echo e(count($liveInterfaces)); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto noc-scroll p-4 space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($selectedInterfaces)): ?>
                    <div class="h-full flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-activity text-4xl mb-3 text-gray-700 dark:text-gray-300"></i>
                        <p class="text-sm">Pilih satu atau lebih interface dari sidebar untuk memonitor traffic.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedInterfaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ifaceName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $stat = collect($liveInterfaces)->firstWhere('name', $ifaceName);
                        $rx = $stat['rx_bps'] ?? 0;
                        $tx = $stat['tx_bps'] ?? 0;
                        
                        if (!function_exists('formatBps')) {
                            function formatBps($bps) {
                                if ($bps >= 1000000) return round($bps / 1000000, 2) . ' Mbps';
                                if ($bps >= 1000) return round($bps / 1000, 2) . ' Kbps';
                                return round($bps) . ' bps';
                            }
                        }
                    ?>
                    <div class="bg-[#111827] border border-gray-800 rounded-lg p-3">
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded text-xs font-bold font-mono"># <?php echo e($ifaceName); ?></span>
                            </div>
                            <div class="flex gap-4 text-xs font-mono">
                                <div class="text-emerald-400" id="rx-label-<?php echo e(md5($ifaceName)); ?>">RX (In): <?php echo e(formatBps($rx)); ?></div>
                                <div class="text-blue-400" id="tx-label-<?php echo e(md5($ifaceName)); ?>">TX (Out): <?php echo e(formatBps($tx)); ?></div>
                            </div>
                        </div>
                        
                        <div class="w-full h-[200px]" id="chart-<?php echo e(md5($ifaceName)); ?>" data-iface="<?php echo e($ifaceName); ?>" wire:ignore></div>
                        
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>
    
        
                <?php
        $__scriptKey = '3483136193-0';
        ob_start();
    ?>
<script>
    window.nocSeriesData = window.nocSeriesData || {};

    const formatBps = (bps) => {
        if (bps >= 1000000) return (bps / 1000000).toFixed(2) + ' Mbps';
        if (bps >= 1000) return (bps / 1000).toFixed(2) + ' Kbps';
        return Math.round(bps) + ' bps';
    };

    Livewire.on('traffic-updated', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        const trafficList = payload.traffic || [];
        const time = new Date().getTime();
        
        if (typeof window.echarts === 'undefined') {
            if (!window.loadingEcharts) {
                window.loadingEcharts = true;
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js';
                document.head.appendChild(script);
            }
            return;
        }

        const chartDivs = document.querySelectorAll('[id^="chart-"]');
        chartDivs.forEach(el => {
            const id = el.id;
            const ifaceName = el.getAttribute('data-iface');
            const stat = trafficList.find(i => i.name === ifaceName);
            if (!stat) return;
            
            const rx = stat.rx_bps || 0;
            const tx = stat.tx_bps || 0;
            
            const rxLabel = document.getElementById(id.replace('chart-', 'rx-label-'));
            const txLabel = document.getElementById(id.replace('chart-', 'tx-label-'));
            if (rxLabel) rxLabel.innerText = 'RX (In): ' + formatBps(rx);
            if (txLabel) txLabel.innerText = 'TX (Out): ' + formatBps(tx);
            
            // Get the ECharts instance attached to this exact DOM element
            let chartInstance = window.echarts.getInstanceByDom(el);
            
            if (!chartInstance) {
                // This DOM element is new (maybe tab was switched or Livewire replaced it)
                chartInstance = window.echarts.init(el, 'dark');
            }
            
            if (!window.nocSeriesData[id]) {
                window.nocSeriesData[id] = { rx: [], tx: [], times: [] };
            }
            
            let dt = new Date(time);
            let timeStr = dt.getHours().toString().padStart(2, '0') + ':' + dt.getMinutes().toString().padStart(2, '0') + ':' + dt.getSeconds().toString().padStart(2, '0');
            
            window.nocSeriesData[id].rx.push(rx);
            window.nocSeriesData[id].tx.push(tx);
            window.nocSeriesData[id].times.push(timeStr);
            
            if (window.nocSeriesData[id].rx.length > 60) {
                window.nocSeriesData[id].rx.shift();
                window.nocSeriesData[id].tx.shift();
                window.nocSeriesData[id].times.shift();
            }
            
            const option = {
                backgroundColor: 'transparent',
                tooltip: { trigger: 'axis' },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', boundaryGap: false, data: window.nocSeriesData[id].times, axisLine: { show: false }, splitLine: { show: false } },
                yAxis: { type: 'value', axisLabel: { formatter: (val) => formatBps(val) }, splitLine: { lineStyle: { color: '#1f2937', type: 'dashed' } } },
                series: [
                    {
                        name: 'RX (In)',
                        type: 'line',
                        smooth: true,
                        showSymbol: false,
                        lineStyle: { color: '#34d399', width: 2 },
                        areaStyle: { color: new window.echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(52, 211, 153, 0.2)' }, { offset: 1, color: 'rgba(52, 211, 153, 0)' }]) },
                        data: window.nocSeriesData[id].rx
                    },
                    {
                        name: 'TX (Out)',
                        type: 'line',
                        smooth: true,
                        showSymbol: false,
                        lineStyle: { color: '#60a5fa', width: 2 },
                        areaStyle: { color: new window.echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(96, 165, 250, 0.2)' }, { offset: 1, color: 'rgba(96, 165, 250, 0)' }]) },
                        data: window.nocSeriesData[id].tx
                    }
                ]
            };
            
            chartInstance.setOption(option);
        });
    });
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'logs'): ?>
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
                        <th class="p-3 font-semibold w-32" style="border-color: var(--noc-border); color: var(--noc-muted);">Time</th>
                        <th class="p-3 font-semibold w-32" style="border-color: var(--noc-border); color: var(--noc-muted);">Topics</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $liveLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $logItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  font-mono noc-muted whitespace-nowrap"><?php echo e($logItem['time'] ?? '-'); ?></td>
                        <td class="p-3  font-mono noc-muted"><?php echo e($logItem['topics'] ?? '-'); ?></td>
                        <td class="p-3  noc-text"><?php echo e($logItem['message'] ?? '-'); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="3" class="p-3  text-center noc-muted">No logs found or router offline.</td>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'sessions'): ?>
    <div class="p-3 h-full overflow-hidden flex flex-col gap-2">
        <div class="flex-none flex items-center gap-2 border-b noc-border pb-2">
            <button wire:click="setSessionSubTab('pppoe')"
                class="px-3 py-1 text-xs rounded border transition-colors <?php echo e($sessionSubTab === 'pppoe' ? 'border-blue-500 text-blue-400 bg-blue-500/10' : 'border-gray-700 noc-muted hover:noc-text-secondary'); ?>">
                PPPoE Active <span class="ml-1 font-mono font-bold"><?php echo e($sessionCounts['pppoe']); ?></span>
            </button>
            <button wire:click="setSessionSubTab('hotspot')"
                class="px-3 py-1 text-xs rounded border transition-colors <?php echo e($sessionSubTab === 'hotspot' ? 'border-emerald-500 text-emerald-400 bg-emerald-500/10' : 'border-gray-700 noc-muted hover:noc-text-secondary'); ?>">
                Hotspot Active <span class="ml-1 font-mono font-bold"><?php echo e($sessionCounts['hotspot']); ?></span>
            </button>
            <button wire:click="setSessionSubTab('all')"
                class="px-3 py-1 text-xs rounded border transition-colors <?php echo e($sessionSubTab === 'all' ? 'border-amber-500 text-amber-400 bg-amber-500/10' : 'border-gray-700 noc-muted hover:noc-text-secondary'); ?>">
                All Sessions <span class="ml-1 font-mono font-bold"><?php echo e($sessionCounts['all']); ?></span>
            </button>
            
            <div class="ml-auto">
                <input wire:model.live.debounce.300ms="searchSession" type="text" placeholder="Search by username, IP, or MAC..." 
                class="bg-[#1e293b] border border-gray-700 text-xs rounded-md px-3 py-1.5 text-gray-300 focus:outline-none focus:border-blue-500 w-64 placeholder-gray-500 dark:bg-slate-900 dark:text-slate-100">
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sessionSubTab === 'pppoe'): ?>
        <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['noPadding' => true,'class' => 'flex-1 overflow-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['noPadding' => true,'class' => 'flex-1 overflow-auto']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <table class="w-full text-left text-sm">
                <thead style="background-color: var(--noc-subpanel);">
                    <tr>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Username</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Service</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">IP Address</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">MAC (Caller-ID)</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Uptime</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rx / Tx</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rate ↓ / ↑</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $pppSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="noc-row-hover transition-colors">
                        <td class="p-3 font-medium noc-text font-mono text-xs"><?php echo e($s->name); ?></td>
                        <td class="p-3 noc-muted text-xs">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" style="background-color: var(--noc-subpanel); color: var(--noc-muted);"><?php echo e($s->service ?? 'ppp'); ?></span>
                        </td>
                        <td class="p-3 font-mono noc-muted text-xs"><?php echo e($s->address ?? '-'); ?></td>
                        <td class="p-3 font-mono noc-muted text-xs"><?php echo e($s->caller_id ?? '-'); ?></td>
                        <td class="p-3 font-mono noc-muted text-xs"><?php echo e($s->uptime ?? '-'); ?></td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ <?php echo e(number_format(($s->bytes_in ?? 0) / 1024 / 1024, 1)); ?> MB</span><br>
                            <span class="text-blue-400">↑ <?php echo e(number_format(($s->bytes_out ?? 0) / 1024 / 1024, 1)); ?> MB</span>
                        </td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ <?php echo e($s->rate_down ?? '-'); ?></span><br>
                            <span class="text-blue-400">↑ <?php echo e($s->rate_up ?? '-'); ?></span>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="7" class="p-6 text-center text-xs noc-muted">Tidak ada sesi PPPoE aktif.</td></tr>
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
        <div class="flex-none mt-1"><?php echo e($pppSessions?->links(data: ['scrollTo' => false])); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sessionSubTab === 'hotspot'): ?>
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

            <table class="w-full text-left text-sm">
                <thead style="background-color: var(--noc-subpanel);">
                    <tr>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">User</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">IP Address</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">MAC Address</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Server</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Login By</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Uptime</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rx / Tx</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $hotspotSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="noc-row-hover transition-colors">
                        <td class="p-3 font-medium noc-text font-mono text-xs"><?php echo e($s->user); ?></td>
                        <td class="p-3 font-mono noc-muted text-xs"><?php echo e($s->address ?? '-'); ?></td>
                        <td class="p-3 font-mono noc-muted text-xs"><?php echo e($s->mac_address ?? '-'); ?></td>
                        <td class="p-3 noc-muted text-xs"><?php echo e($s->server ?? '-'); ?></td>
                        <td class="p-3 text-xs">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" style="background-color: var(--noc-subpanel); color: var(--noc-muted);"><?php echo e($s->login_by ?? '-'); ?></span>
                        </td>
                        <td class="p-3 font-mono noc-muted text-xs"><?php echo e($s->uptime ?? '-'); ?></td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ <?php echo e(number_format(($s->bytes_in ?? 0) / 1024 / 1024, 1)); ?> MB</span><br>
                            <span class="text-blue-400">↑ <?php echo e(number_format(($s->bytes_out ?? 0) / 1024 / 1024, 1)); ?> MB</span>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="7" class="p-6 text-center text-xs noc-muted">Tidak ada sesi Hotspot aktif.</td></tr>
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
        <div class="flex-none mt-1"><?php echo e($hotspotSessions?->links(data: ['scrollTo' => false])); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sessionSubTab === 'all'): ?>
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

            <table class="w-full text-left text-sm">
                <thead style="background-color: var(--noc-subpanel);">
                    <tr>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Protocol</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">User</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">IP / MAC</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Uptime</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Rx/Tx Rate</th>
                        <th class="p-3 font-semibold text-xs" style="color: var(--noc-muted);">Started</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sess): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="noc-row-hover transition-colors">
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-xs font-medium uppercase" style="background-color: var(--noc-subpanel); color: var(--noc-muted);"><?php echo e($sess->protocol); ?></span></td>
                        <td class="p-3 font-medium noc-text text-xs"><?php echo e($sess->username); ?></td>
                        <td class="p-3 font-mono noc-muted text-xs">
                            <div><?php echo e($sess->address); ?></div>
                            <div class="text-[10px]"><?php echo e($sess->caller_id); ?></div>
                        </td>
                        <td class="p-3 font-mono noc-muted text-xs"><?php echo e($sess->uptime); ?></td>
                        <td class="p-3 font-mono text-xs">
                            <span class="text-emerald-500">↓ <?php echo e($sess->rate_down ?? '-'); ?></span><br>
                            <span class="text-blue-500">↑ <?php echo e($sess->rate_up ?? '-'); ?></span>
                        </td>
                        <td class="p-3 noc-muted text-xs"><?php echo e($sess->session_started_at?->format('d M H:i:s')); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="6" class="p-6 text-center text-xs noc-muted">No active sessions.</td></tr>
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
        <div class="flex-none mt-1"><?php echo e($sessions?->links(data: ['scrollTo' => false])); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'health'): ?>
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
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">CPU</th>
                        <th class="p-3 font-semibold" style="border-color: var(--noc-border); color: var(--noc-muted);">Free RAM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60"  style="border-color: var(--noc-border); background-color: var(--noc-panel);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                        <td class="p-3  noc-muted"><?php echo e($h->created_at->format('Y-m-d H:i:s')); ?></td>
                        <td class="p-3">
                            <?php if (isset($component)) { $__componentOriginalb2f7f1aacbef06468a34871d3105efae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2f7f1aacbef06468a34871d3105efae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.stat-badge','data' => ['status' => $h->is_online ? 'ONLINE' : 'OFFLINE']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.stat-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($h->is_online ? 'ONLINE' : 'OFFLINE')]); ?>
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
                        <td class="p-3  font-mono noc-muted"><?php echo e($h->cpu_load); ?>%</td>
                        <td class="p-3  font-mono noc-muted">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($h->total_memory): ?>
                                <?php echo e(round($h->free_memory / 1024 / 1024- 1)); ?> MB
                            <?php else: ?> - <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="4" class="p-3  text-center noc-muted">No health history available.</td>
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

                            <div class="text-xs noc-muted font-normal truncate max-w-md"><?php echo e($alarm->description); ?></div>
                        </td>
                        <td class="p-3  noc-muted"><?php echo e($alarm->started_at->format('M d H:i')); ?> (<?php echo e($alarm->started_at->diffForHumans(short: true)); ?>)</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium noc-badge-info">OPEN</span>
                        </td>
                        <td class="p-3  text-right">
                            <a href="<?php echo e(route('noc.alarms.show', $alarm->id)); ?>" class="text-primary-600 hover:underline">View</a>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="5" class="p-3  text-center noc-muted">No active alarms. Router is healthy.</td>
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
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'traffic'): ?>
    <div class="p-3 h-full overflow-hidden flex flex-col">
        <?php if (isset($component)) { $__componentOriginal45646619fa9c4f8799ad78d440d816d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45646619fa9c4f8799ad78d440d816d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.card','data' => ['class' => 'flex-1 w-full h-full','noPadding' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-1 w-full h-full','noPadding' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <?php if (isset($component)) { $__componentOriginal655b85c939680c1cc3cf2460e5784bf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal655b85c939680c1cc3cf2460e5784bf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.traffic-graph','data' => ['traffic' => $traffic,'period' => $trafficPeriod,'heightClass' => 'h-[350px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.traffic-graph'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['traffic' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($traffic),'period' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trafficPeriod),'heightClass' => 'h-[350px]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal655b85c939680c1cc3cf2460e5784bf1)): ?>
<?php $attributes = $__attributesOriginal655b85c939680c1cc3cf2460e5784bf1; ?>
<?php unset($__attributesOriginal655b85c939680c1cc3cf2460e5784bf1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal655b85c939680c1cc3cf2460e5784bf1)): ?>
<?php $component = $__componentOriginal655b85c939680c1cc3cf2460e5784bf1; ?>
<?php unset($__componentOriginal655b85c939680c1cc3cf2460e5784bf1); ?>
<?php endif; ?>
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
</div>
</div>















<?php /**PATH D:\dsBilling\resources\views\livewire\noc\router\show.blade.php ENDPATH**/ ?>