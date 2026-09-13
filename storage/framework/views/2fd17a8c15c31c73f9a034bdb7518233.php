
<div class="h-full flex flex-col noc-bg">
    <div class="flex-none px-3 py-2 border-b noc-border flex items-center justify-between gap-3 flex-wrap noc-panel-bg">
        <div class="flex items-center gap-3">
            <h1 class="text-base font-bold noc-text">Router Monitoring</h1>
            <span class="noc-count-badge"><?php echo e($summary['total']); ?> Total</span>
            <span class="noc-count-online"><?php echo e($summary['online']); ?> Online</span>
            <span class="noc-count-offline"><?php echo e($summary['offline']); ?> Offline</span>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="statusFilter" class="px-2 py-1 text-xs rounded border focus:outline-none noc-input dark:bg-slate-900 dark:text-slate-100">
                <option value="all">All Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Name / IP / Model…" class="px-2 py-1 text-xs rounded border focus:outline-none w-56 noc-input dark:bg-slate-900 dark:text-slate-100">
        </div>
    </div>

    <div class="flex-1 overflow-auto noc-scroll">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
                    <th class="p-3 font-semibold" wire:click="sort('name')">Name <?php echo $sortField === 'name' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold" wire:click="sort('ip_address')">IP Address <?php echo $sortField === 'ip_address' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold">Vendor / Model</th>
                    <th class="p-3 font-semibold">POP</th>
                    <th class="p-3 font-semibold">CPU</th>
                    <th class="p-3 font-semibold">Memory</th>
                    <th class="p-3 font-semibold">Uptime</th>
                    <th class="p-3 font-semibold" wire:click="sort('status')">Status <?php echo $sortField === 'status' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold">Last Seen</th>
                    <th class="p-3 font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $routers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $log = $r->latestMonitoringLog;
                    $stale = $log && $log->created_at->diffInMinutes(now()) > 5;
                    if (!$log) { $status = 'UNKNOWN'; }
                    elseif (!$log->is_online) { $status = 'OFFLINE'; }
                    elseif ($stale) { $status = 'STALE'; }
                    elseif ($log->cpu_load > 80) { $status = 'WARNING'; }
                    else { $status = 'ONLINE'; }
                    $badge = match($status) {
                        'ONLINE' => 'noc-badge-online',
                        'WARNING' => 'noc-badge-warning',
                        'OFFLINE' => 'noc-badge-offline',
                        default => 'noc-badge-unknown',
                    };
                ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover">
                    <td class="p-3  font-medium noc-text"><?php echo e($r->name); ?></td>
                    <td class="p-3  noc-mono noc-muted"><?php echo e($r->ip_address); ?></td>
                    <td class="p-3  noc-muted"><?php echo e($r->vendor->name ?? '-'); ?> / <?php echo e($r->model ?? '-'); ?></td>
                    <td class="p-3  noc-muted"><?php echo e($r->pop->name ?? '-'); ?></td>
                    <td class="p-3  noc-mono <?php echo e($log && $log->cpu_load > 80 ? 'text-red-500' : 'text-emerald-500'); ?>"><?php echo e($log->cpu_load ? $log->cpu_load . '%' : '-'); ?></td>
                    <td class="p-3  noc-mono noc-muted">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log && $log->total_memory): ?>
                            <?php echo e(round((1 - $log->free_memory / $log->total_memory) * 100)); ?>%
                        <?php else: ?>
                            -
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="p-3  noc-mono noc-muted"><?php echo e($log->uptime ?? '-'); ?></td>
                    <td class="p-3">
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium <?php echo e($badge); ?>"><?php echo e($status); ?></span>
                    </td>
                    <td class="p-3  noc-muted"><?php echo e($log ? $log->created_at->diffForHumans() : ($r->last_seen_at ? $r->last_seen_at->diffForHumans() : '-')); ?></td>
                    <td class="p-3">
                        <a href="<?php echo e(route('noc.routers.show', $r->id)); ?>" class="text-blue-500 hover:text-blue-400">View</a>
                    </td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="10" class="p-3  text-center noc-muted opacity-70">No routers found</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($routers->hasPages()): ?>
    <div class="flex-none px-3 py-2 border-t noc-panel-bg noc-border">
        <?php echo e($routers->links(data: ['scrollTo' => false])); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\noc\router\index.blade.php ENDPATH**/ ?>