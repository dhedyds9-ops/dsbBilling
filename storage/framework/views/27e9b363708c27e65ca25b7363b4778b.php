
<div
    class="h-full flex flex-col overflow-hidden"
    style="background:#0a0e1a;"
    wire:poll.30000ms="refreshData"
    x-data="{ trafficChart: null }"
>


<div class="flex-none px-3 py-2 border-b flex items-center gap-4 flex-wrap" style="background:#111827;border-color:#1f2937;">

    
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">OLT</span>
        <span class="text-sm font-bold noc-mono
            <?php echo e(($header['olt']['online'] ?? 0) < ($header['olt']['total'] ?? 1) ? 'text-yellow-400' : 'text-green-400'); ?>">
            <?php echo e($header['olt']['online'] ?? 0); ?>/<?php echo e($header['olt']['total'] ?? 0); ?>

        </span>
    </div>

    <div class="text-gray-700 dark:text-gray-300">|</div>

    
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">ONU</span>
        <span class="text-sm font-bold noc-mono
            <?php echo e(($header['onu']['online'] ?? 0) < ($header['onu']['total'] ?? 1) ? 'text-yellow-400' : 'text-green-400'); ?>">
            <?php echo e($header['onu']['online'] ?? 0); ?>/<?php echo e($header['onu']['total'] ?? 0); ?>

        </span>
    </div>

    <div class="text-gray-700 dark:text-gray-300">|</div>

    
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Router</span>
        <span class="text-sm font-bold noc-mono
            <?php echo e(($header['router']['online'] ?? 0) < ($header['router']['total'] ?? 1) ? 'text-red-400' : 'text-green-400'); ?>">
            <?php echo e($header['router']['online'] ?? 0); ?>/<?php echo e($header['router']['total'] ?? 0); ?>

        </span>
    </div>

    <div class="text-gray-700 dark:text-gray-300">|</div>

    
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">PPPoE</span>
        <span class="text-sm font-bold text-blue-400 noc-mono"><?php echo e(number_format($header['pppoe'] ?? 0)); ?></span>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($health['alarms']['critical'] ?? 0) > 0): ?>
    <div class="ml-auto flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold" style="background:#450a0a;color:#ef4444;border:1px solid #7f1d1d;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?php echo e($health['alarms']['critical']); ?> CRITICAL
    </div>
    <?php elseif(($health['alarms']['warning'] ?? 0) > 0): ?>
    <div class="ml-auto flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold" style="background:#451a03;color:#f59e0b;border:1px solid #78350f;">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?php echo e($health['alarms']['warning']); ?> WARNING
    </div>
    <?php else: ?>
    <div class="ml-auto flex items-center gap-1 px-2 py-0.5 rounded text-xs" style="background:#064e3b;color:#10b981;border:1px solid #065f46;">
        <i class="bi bi-check-circle-fill"></i>
        Network Healthy
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<div class="flex-1 flex overflow-hidden min-h-0">

    
    <aside class="flex-none w-44 border-r overflow-y-auto noc-scroll" style="background:#111827;border-color:#1f2937;">
        <div class="px-3 py-2 border-b" style="border-color:#1f2937;">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Network Health</span>
        </div>

        
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">OLT</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Online</span>
                    <span class="text-green-400 noc-mono font-medium"><?php echo e($health['olt']['online'] ?? 0); ?></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Offline</span>
                    <span class="<?php echo e(($health['olt']['offline'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e($health['olt']['offline'] ?? 0); ?>

                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Warning</span>
                    <span class="<?php echo e(($health['olt']['warning'] ?? 0) > 0 ? 'text-yellow-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e($health['olt']['warning'] ?? 0); ?>

                    </span>
                </div>
            </div>
        </div>

        
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">ONU</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Online</span>
                    <span class="text-green-400 noc-mono font-medium"><?php echo e(number_format($health['onu']['online'] ?? 0)); ?></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Offline</span>
                    <span class="<?php echo e(($health['onu']['offline'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e(number_format($health['onu']['offline'] ?? 0)); ?>

                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">LOS</span>
                    <span class="<?php echo e(($health['onu']['los'] ?? 0) > 0 ? 'text-purple-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e($health['onu']['los'] ?? 0); ?>

                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Low RX</span>
                    <span class="<?php echo e(($health['onu']['low_rx'] ?? 0) > 0 ? 'text-yellow-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e($health['onu']['low_rx'] ?? 0); ?>

                    </span>
                </div>
            </div>
        </div>

        
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">PON</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Healthy</span>
                    <span class="text-green-400 noc-mono font-medium"><?php echo e($health['pon']['healthy'] ?? 0); ?></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Warning</span>
                    <span class="<?php echo e(($health['pon']['warning'] ?? 0) > 0 ? 'text-yellow-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e($health['pon']['warning'] ?? 0); ?>

                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Down</span>
                    <span class="<?php echo e(($health['pon']['down'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e($health['pon']['down'] ?? 0); ?>

                    </span>
                </div>
            </div>
        </div>

        
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Router</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Online</span>
                    <span class="text-green-400 noc-mono font-medium"><?php echo e($health['router']['online'] ?? 0); ?></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Offline</span>
                    <span class="<?php echo e(($health['router']['offline'] ?? 0) > 0 ? 'text-red-400' : 'text-gray-600 dark:text-gray-400'); ?> noc-mono font-medium">
                        <?php echo e($health['router']['offline'] ?? 0); ?>

                    </span>
                </div>
            </div>
        </div>

        
        <div class="px-3 py-2 border-b" style="border-color:#1a2332;">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Services</div>
            <div class="space-y-0.5">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">PPPoE</span>
                    <span class="text-blue-400 noc-mono font-medium"><?php echo e(number_format($health['services']['pppoe'] ?? 0)); ?></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Hotspot</span>
                    <span class="text-blue-400 noc-mono font-medium"><?php echo e(number_format($health['services']['hotspot'] ?? 0)); ?></span>
                </div>
            </div>
        </div>

        
        <div class="px-3 py-2">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Svc Health</div>
            <div class="space-y-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-center gap-1.5">
                    <?php
                        $dotColor = match($svc['status'] ?? 'unknown') {
                            'healthy' => 'bg-green-500',
                            'warning' => 'bg-yellow-500',
                            'down'    => 'bg-red-500',
                            default   => 'bg-gray-600',
                        };
                    ?>
                    <span class="w-1.5 h-1.5 rounded-full flex-none <?php echo e($dotColor); ?>"></span>
                    <span class="text-xs text-gray-400 truncate" title="<?php echo e($svc['detail'] ?? ''); ?>">
                        <?php echo e($svc['name']); ?>

                    </span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </aside>

    
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        
        <div class="flex-none border-b" style="border-color:#1f2937;">
            <?php if (isset($component)) { $__componentOriginal655b85c939680c1cc3cf2460e5784bf1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal655b85c939680c1cc3cf2460e5784bf1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.noc.traffic-graph','data' => ['traffic' => $traffic,'period' => $trafficPeriod,'heightClass' => 'h-48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('noc.traffic-graph'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['traffic' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($traffic),'period' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trafficPeriod),'heightClass' => 'h-48']); ?>
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
        </div>

        
        <div class="flex-none border-b" style="background:#0d1117;border-color:#1f2937;">
            <div class="flex items-center justify-between px-3 py-1.5 border-b" style="border-color:#1a2332;">
                <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Active Alarms</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('noc.alarms.index')): ?>
                <a href="<?php echo e(route('noc.alarms.index')); ?>" class="text-xs text-blue-400 hover:text-blue-300">View All →</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alarms->isEmpty()): ?>
            <div class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                <i class="bi bi-check-circle text-green-500"></i> No active alarms
            </div>
            <?php else: ?>
            <div class="divide-y" style="border-color:#1a2332;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $alarms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-center gap-2 px-3 py-1.5">
                    <?php
                        $levelColor = match($alarm->level) {
                            'critical' => 'text-red-400',
                            'warning'  => 'text-yellow-400',
                            default    => 'text-blue-400',
                        };
                    ?>
                    <span class="w-12 text-xs font-bold <?php echo e($levelColor); ?> uppercase noc-mono"><?php echo e(strtoupper($alarm->level)); ?></span>
                    <span class="flex-1 text-xs text-gray-300 truncate"><?php echo e($alarm->title); ?></span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 noc-mono flex-none"><?php echo e($alarm->source_name); ?></span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 noc-mono flex-none"><?php echo e($alarm->started_at?->diffForHumans()); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('noc.alarms.show')): ?>
                    <a href="<?php echo e(route('noc.alarms.show', $alarm->id)); ?>" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-300 flex-none">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="flex-none px-3 py-1.5 flex items-center gap-4 border-b text-xs" style="border-color:#1f2937;background:#0d1117;">
            <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Provisioning</span>
            <span class="text-gray-500 dark:text-gray-400">Queued: <span class="text-yellow-400 noc-mono"><?php echo e($provisioning['queued'] ?? 0); ?></span></span>
            <span class="text-gray-500 dark:text-gray-400">Running: <span class="text-blue-400 noc-mono"><?php echo e($provisioning['running'] ?? 0); ?></span></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($provisioning['failed'] ?? 0) > 0): ?>
            <span class="text-red-400 font-medium">Failed: <span class="noc-mono"><?php echo e($provisioning['failed']); ?></span></span>
            <?php else: ?>
            <span class="text-gray-500">Failed: <span class="text-gray-600 dark:text-gray-400 noc-mono">0</span></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('noc.provisioning.index')): ?>
            <a href="<?php echo e(route('noc.provisioning.index')); ?>" class="ml-auto text-blue-400 hover:text-blue-300">View →</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>


<div class="flex-none border-t" style="background:#111827;border-color:#1f2937;">

    
    <div class="flex items-center gap-2 px-3 py-1.5 border-b" style="border-color:#1a2332;">
        <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Devices</span>

        
        <input
            type="text"
            wire:model.live.debounce.400ms="deviceSearch"
            placeholder="Search device / IP / serial…"
            class="flex-1 max-w-xs px-2 py-0.5 text-xs rounded border dark:bg-slate-900 dark:text-slate-100"
            style="background:#0d1117;border-color:#374151;color:#e5e7eb;"
        >

        
        <div class="flex gap-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['all' => 'All', 'olt' => 'OLT', 'onu' => 'ONU', 'router' => 'Router', 'pppoe' => 'PPPoE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <button
                wire:click="setDeviceFilter('<?php echo e($key); ?>')"
                class="px-2 py-0.5 text-xs rounded transition-colors
                       <?php echo e($deviceFilter === $key ? 'bg-blue-800 text-blue-200' : 'text-gray-500 dark:text-gray-400 hover:text-gray-300 hover:bg-gray-800'); ?>"
            ><?php echo e($label); ?></button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    
    <div class="overflow-x-auto" style="max-height:200px;overflow-y:auto;">
        <table class="w-full text-xs">
            <thead>
                <tr style="background:#0d1117;color:#6b7280;">
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Device</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Type</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">IP</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Status</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Last Seen</th>
                    <th class="px-3 py-1.5 text-left font-medium uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color:#1a2332;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-gray-900/50 transition-colors">
                    <td class="px-3 py-1.5 font-medium text-gray-300"><?php echo e($device->name ?? '—'); ?></td>
                    <td class="px-3 py-1.5">
                        <span class="px-1.5 py-0.5 rounded text-xs noc-mono" style="background:#1f2937;color:#9ca3af;">
                            <?php echo e($device->type ?? '—'); ?>

                        </span>
                    </td>
                    <td class="px-3 py-1.5 noc-mono text-gray-400"><?php echo e($device->ip ?? '—'); ?></td>
                    <td class="px-3 py-1.5">
                        <?php
                            $st = strtolower($device->status ?? 'unknown');
                            $badge = match(true) {
                                $st === 'online'  => 'noc-badge-online',
                                $st === 'warning' => 'noc-badge-warning',
                                $st === 'offline' => 'noc-badge-offline',
                                default           => 'noc-badge-unknown',
                            };
                        ?>
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium <?php echo e($badge); ?>">
                            <?php echo e(strtoupper($device->status ?? 'UNKNOWN')); ?>

                        </span>
                    </td>
                    <td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 noc-mono">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($device->last_seen): ?>
                            <?php echo e(\Carbon\Carbon::parse($device->last_seen)->diffForHumans()); ?>

                        <?php else: ?>
                            —
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-3 py-1.5">
                        <?php
                            $deviceType = strtolower($device->type ?? '');
                            $routeMap   = ['olt' => 'noc.olts.show', 'router' => 'noc.routers.show', 'onu' => 'noc.onus.show'];
                            $deviceRoute = $routeMap[$deviceType] ?? null;
                            // Extract numeric ID from prefixed IDs like 'olt_5'
                            $rawId = $device->id ?? null;
                            preg_match('/(\d+)$/', (string)$rawId, $matches);
                            $numericId = $matches[1] ?? null;
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deviceRoute && $numericId && Route::has($deviceRoute)): ?>
                        <a href="<?php echo e(route($deviceRoute, $numericId)); ?>" class="text-blue-400 hover:text-blue-300 text-xs">View</a>
                        <?php else: ?>
                        <span class="text-gray-600 dark:text-gray-400">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-600 dark:text-gray-400">No devices found</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($devices->hasPages()): ?>
    <div class="px-3 py-1.5 flex items-center justify-between border-t" style="border-color:#1a2332;">
        <span class="text-xs text-gray-600 dark:text-gray-400">
            <?php echo e($devices->firstItem()); ?>–<?php echo e($devices->lastItem()); ?> of <?php echo e($devices->total()); ?>

        </span>
        <div class="flex gap-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($devices->onFirstPage()): ?>
                <span class="px-2 py-0.5 text-xs text-gray-700 dark:text-gray-300">←</span>
            <?php else: ?>
                <button wire:click="previousPage" class="px-2 py-0.5 text-xs text-gray-400 hover:text-gray-200">←</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($devices->hasMorePages()): ?>
                <button wire:click="nextPage" class="px-2 py-0.5 text-xs text-gray-400 hover:text-gray-200">→</button>
            <?php else: ?>
                <span class="px-2 py-0.5 text-xs text-gray-700 dark:text-gray-300">→</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\noc\overview.blade.php ENDPATH**/ ?>