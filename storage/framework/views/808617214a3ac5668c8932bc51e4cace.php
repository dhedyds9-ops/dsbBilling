<div class="h-full flex flex-col noc-bg">
    
    <div class="flex-none px-4 py-3 border-b noc-border flex items-center justify-between noc-panel-bg">
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-bold noc-text">OLT Monitoring</h1>
            <span class="noc-count-badge"><?php echo e($summary['total']); ?> Total</span>
            <span class="noc-count-online"><?php echo e($summary['online']); ?> Online</span>
            <span class="noc-count-offline"><?php echo e($summary['offline']); ?> Offline</span>
            <span class="noc-count-warning"><?php echo e($summary['warning']); ?> Warning</span>
        </div>

        <div class="flex items-center gap-2">
            <select wire:model.live="statusFilter" class="noc-input px-2 py-1 text-sm rounded border focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                <option value="all">All Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="warning">Warning</option>
            </select>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search OLT..." class="noc-input px-3 py-1 text-sm rounded border focus:outline-none w-64 dark:bg-slate-900 dark:text-slate-100">
        </div>
    </div>

    
    <div class="flex-1 overflow-auto noc-scroll">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
                    <th class="p-3 font-semibold" wire:click="sort('name')">Name <?php echo $sortField === 'name' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold" wire:click="sort('ip_address')">IP Address <?php echo $sortField === 'ip_address' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold">Vendor / Model</th>
                    <th class="p-3 font-semibold" wire:click="sort('temperature')">Temp <?php echo $sortField === 'temperature' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold" wire:click="sort('onu_active_count')">ONUs <?php echo $sortField === 'onu_active_count' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold" wire:click="sort('status')">Status <?php echo $sortField === 'status' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold" wire:click="sort('last_polled_at')">Last Polled <?php echo $sortField === 'last_polled_at' ? ($sortDirection === 'asc' ? '&uarr;' : '&darr;') : ''; ?></th>
                    <th class="p-3 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $olts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $olt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors noc-row-hover transition-colors">
                    <td class="p-3  noc-text font-medium"><?php echo e($olt->name); ?></td>
                    <td class="p-3  noc-mono noc-muted"><?php echo e($olt->ip_address); ?></td>
                    <td class="p-3  noc-muted"><?php echo e($olt->vendor->name ?? '-'); ?> / <?php echo e($olt->model ?? '-'); ?></td>
                    <td class="p-3  noc-mono <?php echo e($olt->temperature > 60 ? 'text-red-500' : 'text-emerald-500'); ?>"><?php echo e($olt->temperature ?? '-'); ?> °C</td>
                    <td class="p-3  noc-mono noc-muted"><?php echo e($olt->onu_active_count ?? 0); ?></td>
                    <td class="p-3">
                        <?php
                            $status = $this->getOltStatus($olt);
                            $badge = match($status) {
                                'ONLINE'  => 'noc-badge-online',
                                'WARNING' => 'noc-badge-warning',
                                'OFFLINE' => 'noc-badge-offline',
                                default   => 'noc-badge-unknown',
                            };
                        ?>
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo e($badge); ?>"><?php echo e($status); ?></span>
                    </td>
                    <td class="p-3  noc-muted text-xs"><?php echo e($olt->last_polled_at ? $olt->last_polled_at->diffForHumans() : '-'); ?></td>
                    <td class="p-3">
                        <a href="<?php echo e(route('noc.olts.show', $olt->id)); ?>" class="text-blue-500 hover:text-blue-400 text-xs font-medium">Manage →</a>
                    </td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td colspan="8" class="p-3  text-center noc-muted opacity-70">No OLTs found matching the criteria.</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($olts->hasPages()): ?>
    <div class="flex-none px-4 py-3 border-t noc-border noc-panel-bg">
        <?php echo e($olts->links(data: ['scrollTo' => false])); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\noc\olt\index.blade.php ENDPATH**/ ?>