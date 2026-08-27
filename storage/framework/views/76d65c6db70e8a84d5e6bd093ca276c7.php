<?php
/** @var \App\Livewire\Jaringan\Monitoring\Index $this */
/** @var mixed $rows */
$summaryItems = $this->getSummaryItems();
$toolbarActions = $this->getToolbarActions();
$bulkActions = $this->getBulkActions();
$filterConfig = $this->getFilterConfig();
?>
<div class="flex flex-col h-full min-h-0 bg-slate-50 dark:bg-slate-900">

    <?php echo $__env->make('partials.enterprise.list-toolbar', [
        'title' => 'Jaringan > Monitoring',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari username, IP, MAC, router...',
        'showFiltersToggle' => true,
        'tabs' => $this->tabs,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make('partials.enterprise.summary-cards', ['items' => $summaryItems], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showFilters): ?>
        <?php echo $__env->make('partials.enterprise.filters', ['filters' => $filterConfig], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.bulk-bar', ['bulkActions' => $bulkActions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->errorMessage): ?>
        <div class="px-3 py-2 bg-red-50 border-b border-red-100 dark:bg-red-900/30 dark:border-red-800 text-red-700 dark:text-red-200 text-sm">
            <?php echo e($this->errorMessage); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->loading): ?>
        <div class="flex-1 flex items-center justify-center py-16">
            <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Memuat data...
            </div>
        </div>
    <?php elseif($this->activeTab === 'realtime'): ?>
        <div class="flex-1 overflow-auto min-h-0" wire:poll.15s="refreshAll">
            <div class="p-3 grid grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-sm">Live Counters</h3>
                        <div class="flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Auto-refresh 15s</span>
                            <span class="text-slate-400 ml-1"><?php echo e(\Illuminate\Support\Carbon::parse($this->summary['updated_at'] ?? now())->format('H:i:s')); ?></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['router_up'=>'Router UP','router_down'=>'Router DOWN','pppoe_online'=>'PPPoE','hotspot_online'=>'Hotspot']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $color = $k === 'router_down' ? 'red' : ($k === 'router_up' ? 'emerald' : ($k === 'pppoe_online' ? 'blue' : 'purple'));
                            ?>
                            <div class="p-2 rounded-lg border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                                <div class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php echo e($label); ?></div>
                                <div class="text-2xl font-bold text-<?php echo e($color); ?>-600 dark:text-<?php echo e($color); ?>-400 tabular-nums mt-0.5">
                                    <?php echo e($this->summary[$k] ?? 0); ?>

                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="p-2 rounded-lg bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-950/40 dark:to-cyan-950/40 border border-blue-100 dark:border-blue-900">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Total Bandwidth</div>
                            <div class="text-xl font-bold text-cyan-700 dark:text-cyan-300 tabular-nums"><?php echo e($this->summary['total_bandwidth_mbps'] ?? 0); ?> <span class="text-xs font-normal">Mbps</span></div>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/40 border border-amber-100 dark:border-amber-900">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Alarms</div>
                            <div class="text-xl font-bold text-amber-700 dark:text-amber-300 tabular-nums"><?php echo e($this->summary['alarms_active'] ?? 0); ?></div>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-slate-50 to-zinc-100 dark:from-slate-800 dark:to-zinc-900 border border-slate-200 dark:border-slate-700">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Ticket Open</div>
                            <div class="text-xl font-bold text-slate-700 dark:text-slate-200 tabular-nums"><?php echo e($this->summary['ticket_open'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                    <h3 class="font-semibold text-sm mb-2">Recent Alarms</h3>
                    <div class="space-y-1 max-h-96 overflow-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->recentAlarms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex gap-2 p-2 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700/50 border border-slate-100 dark:border-slate-700/50">
                                <div class="flex-shrink-0">
                                    <?php
                                        $sevColor = match($a['severity'] ?? 'info') {
                                            'critical' => 'bg-red-500', 'high' => 'bg-orange-500', 'warning' => 'bg-amber-500', 'info' => 'bg-blue-500', default => 'bg-slate-500'
                                        };
                                    ?>
                                    <span class="inline-block w-2 h-2 rounded-full mt-1.5 <?php echo e($sevColor); ?>"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-medium truncate"><?php echo e($a['router']); ?></span>
                                        <span class="text-[10px] uppercase text-slate-400"><?php echo e($a['type']); ?></span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate"><?php echo e($a['message']); ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5"><?php echo e(\Illuminate\Support\Carbon::parse($a['created_at'])->diffForHumans()); ?></div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="text-xs text-slate-400 text-center py-8">Tidak ada alarm</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif($this->activeTab === 'bandwidth'): ?>
        <div class="flex-1 overflow-auto min-h-0">
            <div class="p-3 space-y-3">
                <?php
                    $byRouter = collect($this->bandwidthData ?? [])->groupBy('router_name');
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $byRouter; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routerName => $ifs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                        <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <h4 class="font-semibold text-sm"><?php echo e($routerName); ?></h4>
                            <?php
                                $tot = $ifs->sum('in_mbps') + $ifs->sum('out_mbps');
                            ?>
                            <span class="text-xs text-cyan-600 dark:text-cyan-400 font-medium"><?php echo e(round($tot)); ?> Mbps total</span>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="text-[10px] uppercase text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/40">
                                <tr>
                                    <th class="px-3 py-1.5 text-left">Interface</th>
                                    <th class="px-3 py-1.5 w-48">Download (sparkline)</th>
                                    <th class="px-3 py-1.5 text-right">In Mbps</th>
                                    <th class="px-3 py-1.5 w-48">Upload (sparkline)</th>
                                    <th class="px-3 py-1.5 text-right">Out Mbps</th>
                                    <th class="px-3 py-1.5 text-right">95th</th>
                                    <th class="px-3 py-1.5 w-28 text-right">Pct</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                        <td class="px-3 py-2 font-mono text-xs"><?php echo e($i['interface']); ?></td>
                                        <td class="px-3 py-2"><?php echo $__env->make('livewire.partials.sparkline', ['data' => $i['spark_in'], 'color' => '#22d3ee'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                        <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-cyan-600 dark:text-cyan-400"><?php echo e($i['in_mbps']); ?></td>
                                        <td class="px-3 py-2"><?php echo $__env->make('livewire.partials.sparkline', ['data' => $i['spark_out'], 'color' => '#a78bfa'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                        <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-purple-600 dark:text-purple-400"><?php echo e($i['out_mbps']); ?></td>
                                        <td class="px-3 py-2 text-right font-mono text-xs tabular-nums"><?php echo e($i['pct_95th_in']); ?>/<?php echo e($i['pct_95th_out']); ?></td>
                                        <td class="px-3 py-2"><?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['val' => min(100, $i['pct_util']),'color' => $i['pct_util'] > 80 ? 'red' : ($i['pct_util'] > 50 ? 'amber' : 'emerald')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['val' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(min(100, $i['pct_util'])),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($i['pct_util'] > 80 ? 'red' : ($i['pct_util'] > 50 ? 'amber' : 'emerald'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $attributes = $__attributesOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__attributesOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $component = $__componentOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__componentOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?></td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    <?php elseif($this->activeTab === 'resources'): ?>
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="px-3 py-2 text-left">Router</th>
                        <th class="px-3 py-2 text-left">Host</th>
                        <th class="px-3 py-2 text-right">CPU%</th>
                        <th class="px-3 py-2 text-right">MEM%</th>
                        <th class="px-3 py-2 text-right">Disk%</th>
                        <th class="px-3 py-2 text-right">Temp</th>
                        <th class="px-3 py-2 text-left">Uptime</th>
                        <th class="px-3 py-2 text-left">FW Ver</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->resourcesData ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-3 py-2 font-medium"><?php echo e($r['name']); ?></td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-500"><?php echo e($r['host']); ?></td>
                            <td class="px-3 py-2 w-32"><?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['val' => $r['cpu_pct'],'color' => $r['cpu_pct'] > 85 ? 'red' : ($r['cpu_pct'] > 60 ? 'amber' : 'emerald')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['val' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r['cpu_pct']),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r['cpu_pct'] > 85 ? 'red' : ($r['cpu_pct'] > 60 ? 'amber' : 'emerald'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $attributes = $__attributesOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__attributesOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $component = $__componentOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__componentOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?></td>
                            <td class="px-3 py-2 w-32"><?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['val' => $r['mem_pct'],'color' => $r['mem_pct'] > 85 ? 'red' : ($r['mem_pct'] > 60 ? 'amber' : 'blue')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['val' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r['mem_pct']),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r['mem_pct'] > 85 ? 'red' : ($r['mem_pct'] > 60 ? 'amber' : 'blue'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $attributes = $__attributesOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__attributesOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $component = $__componentOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__componentOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?></td>
                            <td class="px-3 py-2 w-32"><?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['val' => $r['disk_pct'],'color' => 'slate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['val' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r['disk_pct']),'color' => 'slate']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $attributes = $__attributesOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__attributesOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $component = $__componentOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__componentOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?></td>
                            <td class="px-3 py-2 text-right font-mono text-xs tabular-nums <?php echo e($r['temp_c'] > 65 ? 'text-red-600 dark:text-red-400 font-semibold' : ''); ?>"><?php echo e($r['temp_c']); ?>°C</td>
                            <td class="px-3 py-2 text-xs text-slate-500"><?php echo e($r['uptime']); ?></td>
                            <td class="px-3 py-2 font-mono text-xs"><?php echo e($r['firmware_version']); ?></td>
                            <td class="px-3 py-2"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $r['status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r['status'])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></td>
                            <td class="px-3 py-2 text-right">
                                <button wire:click="refreshRouter(<?php echo e($r['id']); ?>)" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-md border border-slate-200 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Refresh
                                </button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="10" class="px-6 py-16 text-center text-slate-500">Tidak ada router</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="w-10 px-3 py-2"><input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600"></th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->activeTab === 'pppoe'): ?>
                            <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('username')">Username</th>
                            <th class="px-3 py-2 text-left">Pelanggan</th>
                            <th class="px-3 py-2 text-left">Router</th>
                            <th class="px-3 py-2 text-left">IP</th>
                            <th class="px-3 py-2 text-right">Uptime</th>
                            <th class="px-3 py-2 text-right">RX Mbps</th>
                            <th class="px-3 py-2 text-right">TX Mbps</th>
                            <th class="px-3 py-2 text-left">Session Start</th>
                        <?php else: ?>
                            <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('username')">Username</th>
                            <th class="px-3 py-2 text-left">Router</th>
                            <th class="px-3 py-2 text-left">IP</th>
                            <th class="px-3 py-2 text-left font-mono text-xs">MAC</th>
                            <th class="px-3 py-2 text-right">Uptime</th>
                            <th class="px-3 py-2 text-right">RX</th>
                            <th class="px-3 py-2 text-right">TX</th>
                            <th class="px-3 py-2 text-right">Bytes In/Out</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php
                        $pageRows = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator ? $rows->items() : (is_array($rows) ? $rows : $rows->all());
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pageRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-3 py-2"><input type="checkbox" wire:model.live="selected" value="<?php echo e((string) $r->id); ?>" class="rounded border-slate-300 dark:border-slate-600"></td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->activeTab === 'pppoe'): ?>
                                <td class="px-3 py-2 font-mono text-xs text-blue-600 dark:text-blue-400"><?php echo e($r->username); ?></td>
                                <td class="px-3 py-2 text-xs"><?php echo e($r->pppoeUser?->customer?->name ?? '-'); ?></td>
                                <td class="px-3 py-2 text-xs"><?php echo e($r->router?->name ?? '-'); ?></td>
                                <td class="px-3 py-2 font-mono text-xs"><?php echo e($r->ip_address); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums"><?php echo e($r->uptime); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-cyan-600 dark:text-cyan-400"><?php echo e(round(($r->download_rate ?? 0)/1e6, 2)); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-purple-600 dark:text-purple-400"><?php echo e(round(($r->upload_rate ?? 0)/1e6, 2)); ?></td>
                                <td class="px-3 py-2 text-xs text-slate-500"><?php echo e($r->session_started_at ? \Illuminate\Support\Carbon::parse($r->session_started_at)->diffForHumans() : '-'); ?></td>
                            <?php else: ?>
                                <td class="px-3 py-2 font-mono text-xs text-purple-600 dark:text-purple-400"><?php echo e($r->username); ?></td>
                                <td class="px-3 py-2 text-xs"><?php echo e($r->router?->name ?? '-'); ?></td>
                                <td class="px-3 py-2 font-mono text-xs"><?php echo e($r->ip_address); ?></td>
                                <td class="px-3 py-2 font-mono text-[10px] text-slate-500"><?php echo e($r->mac_address); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums"><?php echo e($r->uptime); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-cyan-600 dark:text-cyan-400"><?php echo e($r->rx_bytes); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-purple-600 dark:text-purple-400"><?php echo e($r->tx_bytes); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-[10px] text-slate-500"><?php echo e($r->bytes_in ?? 0); ?> / <?php echo e($r->bytes_out ?? 0); ?></td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <td class="px-3 py-2 text-right">
                                <button wire:click="kickSession('<?php echo e($r->id); ?>')" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-md bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-300 border border-red-100 dark:border-red-900">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    Kick
                                </button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="100" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                <div class="font-medium">Tidak ada sesi <?php echo e($this->tabs[$this->activeTab] ?? ''); ?> online</div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages()): ?>
            <div class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 flex items-center justify-between text-sm">
                <div class="text-slate-500 dark:text-slate-400 text-xs">
                    Menampilkan <?php echo e($rows->firstItem()); ?>-<?php echo e($rows->lastItem()); ?> dari <?php echo e($rows->total()); ?>

                </div>
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="text-xs rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-1 px-2">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <?php echo e($rows->links('livewire::simple-tailwind')); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\jaringan\monitoring\index.blade.php ENDPATH**/ ?>