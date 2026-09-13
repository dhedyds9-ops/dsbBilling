<?php
/** @var \App\Livewire\Jaringan\Fiber\Index $this */
/** @var mixed $rows */
$summaryItems = $this->getSummaryItems();
$toolbarActions = $this->getToolbarActions();
$bulkActions = $this->getBulkActions();
$filterConfig = $this->getFilterConfig();
$tabCounts = [
    'olt' => $this->summary['total_olt'] ?? null,
    'onu' => $this->summary['total_onu'] ?? null,
    'odp' => $this->summary['odp_aktif'] ?? null,
    'odc' => $this->summary['odc_aktif'] ?? null,
];
$tabsWithCounts = [];
foreach ($this->tabs as $k => $label) {
    $c = $tabCounts[$k] ?? null;
    $tabsWithCounts[$k] = $c !== null ? ['label' => $label, 'count' => $c] : $label;
}
?>
<div class="flex flex-col h-full min-h-0 bg-slate-50 dark:bg-slate-900">

    <?php echo $__env->make('partials.enterprise.list-toolbar', [
        'title' => 'Jaringan > Fiber & ONU',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari nama, SN, host, alamat...',
        'showFiltersToggle' => true,
        'tabs' => $tabsWithCounts,
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
    <?php else: ?>
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="w-10 px-3 py-2">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->activeTab === 'olt'): ?>
                            <th class="px-3 py-2 text-left cursor-pointer hover:text-slate-900 dark:text-slate-100" wire:click="sortBy('id')">ID <?php if (isset($component)) { $__componentOriginal80626b412c9489d98de013c8202fdf83 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80626b412c9489d98de013c8202fdf83 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sort-ind','data' => ['field' => 'id','current' => $this->sortField,'dir' => $this->sortDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sort-ind'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('id'),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->sortField),'dir' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->sortDirection)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80626b412c9489d98de013c8202fdf83)): ?>
<?php $attributes = $__attributesOriginal80626b412c9489d98de013c8202fdf83; ?>
<?php unset($__attributesOriginal80626b412c9489d98de013c8202fdf83); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80626b412c9489d98de013c8202fdf83)): ?>
<?php $component = $__componentOriginal80626b412c9489d98de013c8202fdf83; ?>
<?php unset($__componentOriginal80626b412c9489d98de013c8202fdf83); ?>
<?php endif; ?></th>
                            <th class="px-3 py-2 text-left cursor-pointer hover:text-slate-900 dark:text-slate-100" wire:click="sortBy('name')">Nama <?php if (isset($component)) { $__componentOriginal80626b412c9489d98de013c8202fdf83 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80626b412c9489d98de013c8202fdf83 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sort-ind','data' => ['field' => 'name','current' => $this->sortField,'dir' => $this->sortDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sort-ind'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['field' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('name'),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->sortField),'dir' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->sortDirection)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80626b412c9489d98de013c8202fdf83)): ?>
<?php $attributes = $__attributesOriginal80626b412c9489d98de013c8202fdf83; ?>
<?php unset($__attributesOriginal80626b412c9489d98de013c8202fdf83); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80626b412c9489d98de013c8202fdf83)): ?>
<?php $component = $__componentOriginal80626b412c9489d98de013c8202fdf83; ?>
<?php unset($__componentOriginal80626b412c9489d98de013c8202fdf83); ?>
<?php endif; ?></th>
                            <th class="px-3 py-2 text-left">Host</th>
                            <th class="px-3 py-2 text-left">POP</th>
                            <th class="px-3 py-2 text-right">Port PON</th>
                            <th class="px-3 py-2 text-right">Total ONU</th>
                            <th class="px-3 py-2 text-right">ONU Online</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">CPU%</th>
                            <th class="px-3 py-2 text-right">MEM%</th>
                        <?php elseif($this->activeTab === 'onu'): ?>
                            <th class="px-3 py-2 text-left">SN</th>
                            <th class="px-3 py-2 text-left">Nomor Seri</th>
                            <th class="px-3 py-2 text-left">Pelanggan</th>
                            <th class="px-3 py-2 text-left">OLT</th>
                            <th class="px-3 py-2 text-right">PON Port</th>
                            <th class="px-3 py-2 text-left">ODP</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">RX(dBm)</th>
                            <th class="px-3 py-2 text-right">TX(dBm)</th>
                            <th class="px-3 py-2 text-left">Last Reg</th>
                        <?php elseif($this->activeTab === 'odp'): ?>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Lokasi</th>
                            <th class="px-3 py-2 text-left">POP</th>
                            <th class="px-3 py-2 text-left">OLT</th>
                            <th class="px-3 py-2 text-left">Splitter</th>
                            <th class="px-3 py-2 text-right">Port Tot/Used</th>
                        <?php elseif($this->activeTab === 'odc'): ?>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Lokasi</th>
                            <th class="px-3 py-2 text-left">POP</th>
                            <th class="px-3 py-2 text-left">Rak</th>
                            <th class="px-3 py-2 text-right">Port Tot/Used</th>
                        <?php elseif($this->activeTab === 'pop'): ?>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Alamat</th>
                            <th class="px-3 py-2 text-left">Koordinat</th>
                            <th class="px-3 py-2 text-right">OLT Total</th>
                        <?php elseif($this->activeTab === 'fiber'): ?>
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Awal → Akhir</th>
                            <th class="px-3 py-2 text-left">Tipe</th>
                            <th class="px-3 py-2 text-right">Panjang</th>
                            <th class="px-3 py-2 text-right">Loss(dB)</th>
                        <?php elseif($this->activeTab === 'los'): ?>
                            <th class="px-3 py-2 text-left">ONU SN</th>
                            <th class="px-3 py-2 text-left">Pelanggan</th>
                            <th class="px-3 py-2 text-left">OLT</th>
                            <th class="px-3 py-2 text-left">Sejak LOS</th>
                            <th class="px-3 py-2 text-right">Durasi</th>
                            <th class="px-3 py-2 text-left">Severity</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php
                        $pageRows = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator ? $rows->items() : (is_array($rows) ? $rows : $rows->all());
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $pageRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/60">
                            <td class="px-3 py-2">
                                <input type="checkbox" wire:model.live="selected" value="<?php echo e((string) $r->id); ?>" class="rounded border-slate-300 dark:border-slate-600">
                            </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->activeTab === 'olt'): ?>
                                <td class="px-3 py-2 font-mono text-xs text-slate-500 dark:text-slate-400"><?php echo e($r->id); ?></td>
                                <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-100"><?php echo e($r->name); ?></td>
                                <td class="px-3 py-2 font-mono text-xs"><?php echo e($r->host); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->pop->name ?? '-'); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($r->ponPorts->count() ?? 0); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($r->onus_count ?? 0); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($r->onus_online ?? 0); ?></td>
                                <td class="px-3 py-2"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $r->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->status)]); ?>
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
                                <td class="px-3 py-2 text-right"><?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['val' => $r->cpu_pct ?? 0,'color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['val' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->cpu_pct ?? 0),'color' => 'blue']); ?>
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
                                <td class="px-3 py-2 text-right"><?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['val' => $r->mem_pct ?? 0,'color' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['val' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->mem_pct ?? 0),'color' => 'amber']); ?>
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
                            <?php elseif($this->activeTab === 'onu'): ?>
                                <td class="px-3 py-2 font-mono text-xs"><?php echo e($r->code ?? $r->id); ?></td>
                                <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-200"><?php echo e($r->serial_number); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->customer->name ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->olt->name ?? '-'); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($r->pon_port); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->odp->name ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $r->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->status)]); ?>
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
                                <td class="px-3 py-2 text-right font-mono text-xs"><?php echo e($r->rx_power ?? '-'); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs"><?php echo e($r->tx_power ?? '-'); ?></td>
                                <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400"><?php echo e($r->last_registered_at ? \Illuminate\Support\Carbon::parse($r->last_registered_at)->diffForHumans() : '-'); ?></td>
                            <?php elseif($this->activeTab === 'odp'): ?>
                                <td class="px-3 py-2 font-medium"><?php echo e($r->name); ?></td>
                                <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400"><?php echo e($r->location ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->pop->name ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->olt->name ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->splitter->name ?? '-'); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs"><?php echo e($r->port_total ?? 0); ?>/<?php echo e($r->port_used ?? 0); ?></td>
                            <?php elseif($this->activeTab === 'odc'): ?>
                                <td class="px-3 py-2 font-medium"><?php echo e($r->name); ?></td>
                                <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400"><?php echo e($r->location ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->pop->name ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->rack->name ?? '-'); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs"><?php echo e($r->port_total ?? 0); ?>/<?php echo e($r->port_used ?? 0); ?></td>
                            <?php elseif($this->activeTab === 'pop'): ?>
                                <td class="px-3 py-2 font-medium"><?php echo e($r->name); ?></td>
                                <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate"><?php echo e($r->address); ?></td>
                                <td class="px-3 py-2 font-mono text-xs"><?php echo e(($r->latitude ?? '-') . ', ' . ($r->longitude ?? '-')); ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($r->olts->count() ?? 0); ?></td>
                            <?php elseif($this->activeTab === 'fiber'): ?>
                                <td class="px-3 py-2 font-mono text-xs"><?php echo e($r->code); ?></td>
                                <td class="px-3 py-2 text-xs"><?php echo e($r->startOdc->name ?? '-'); ?> → <?php echo e($r->endOdc->name ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php if (isset($component)) { $__componentOriginal3e43da63772e725970863e9067088b49 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e43da63772e725970863e9067088b49 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.chip','data' => ['label' => $r->type,'color' => 'slate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('chip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->type),'color' => 'slate']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3e43da63772e725970863e9067088b49)): ?>
<?php $attributes = $__attributesOriginal3e43da63772e725970863e9067088b49; ?>
<?php unset($__attributesOriginal3e43da63772e725970863e9067088b49); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3e43da63772e725970863e9067088b49)): ?>
<?php $component = $__componentOriginal3e43da63772e725970863e9067088b49; ?>
<?php unset($__componentOriginal3e43da63772e725970863e9067088b49); ?>
<?php endif; ?></td>
                                <td class="px-3 py-2 text-right"><?php echo e($r->length); ?> m</td>
                                <td class="px-3 py-2 text-right font-mono text-xs"><?php echo e($r->loss_db ?? 0); ?> dB</td>
                            <?php elseif($this->activeTab === 'los'): ?>
                                <td class="px-3 py-2 font-mono text-xs text-red-600 dark:text-red-400"><?php echo e($r->serial_number); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->customer->name ?? '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->olt->name ?? '-'); ?></td>
                                <td class="px-3 py-2 text-xs"><?php echo e($r->last_seen_at ? \Illuminate\Support\Carbon::parse($r->last_seen_at)->diffForHumans() : '-'); ?></td>
                                <td class="px-3 py-2 text-right font-mono text-xs"><?php echo e($r->los_duration_hours ?? 0); ?>j</td>
                                <td class="px-3 py-2"><?php if (isset($component)) { $__componentOriginal9c7e36731c424e782043de6127b0ac28 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9c7e36731c424e782043de6127b0ac28 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.severity-badge','data' => ['severity' => $r->los_severity ?? 'high']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('severity-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['severity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->los_severity ?? 'high')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9c7e36731c424e782043de6127b0ac28)): ?>
<?php $attributes = $__attributesOriginal9c7e36731c424e782043de6127b0ac28; ?>
<?php unset($__attributesOriginal9c7e36731c424e782043de6127b0ac28); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9c7e36731c424e782043de6127b0ac28)): ?>
<?php $component = $__componentOriginal9c7e36731c424e782043de6127b0ac28; ?>
<?php unset($__componentOriginal9c7e36731c424e782043de6127b0ac28); ?>
<?php endif; ?></td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-0.5">
                                    <button wire:click="rowEdit(<?php echo e($r->id); ?>)" class="p-1 text-slate-500 dark:text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:bg-blue-900/30 rounded dark:hover:bg-blue-900/30" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="rowDetail(<?php echo e($r->id); ?>)" class="p-1 text-slate-500 dark:text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:bg-blue-900/30 rounded dark:hover:bg-blue-900/30" title="Detail">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="rowSync(<?php echo e($r->id); ?>)" class="p-1 text-slate-500 dark:text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:bg-emerald-900/30 rounded dark:hover:bg-emerald-900/30" title="Sync">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                    <button wire:click="rowShowMap(<?php echo e($r->id); ?>)" class="p-1 text-slate-500 dark:text-slate-400 hover:text-cyan-600 hover:bg-cyan-50 rounded dark:hover:bg-cyan-900/30" title="Map">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->activeTab === 'onu'): ?>
                                        <button wire:click="rowTestLos(<?php echo e($r->id); ?>)" class="p-1 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:bg-red-900/30 rounded dark:hover:bg-red-900/30" title="Test LOS">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($r->status ?? '') === 'active'): ?>
                                        <button wire:click="rowDisable(<?php echo e($r->id); ?>)" class="p-1 text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:bg-amber-900/30 rounded dark:hover:bg-amber-900/30" title="Disable">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        </button>
                                    <?php else: ?>
                                        <button wire:click="rowEnable(<?php echo e($r->id); ?>)" class="p-1 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:bg-emerald-900/30 rounded dark:hover:bg-emerald-900/30" title="Enable">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="100" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <div class="font-medium">Data <?php echo e($this->tabs[$this->activeTab] ?? ''); ?> kosong</div>
                                <div class="text-xs mt-1">Ubah filter atau buat data baru</div>
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
                    <select wire:model.live="perPage" class="text-xs rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-1 px-2 dark:bg-slate-900 dark:text-slate-100">
                        <option value="10">10 / hal</option>
                        <option value="25">25 / hal</option>
                        <option value="50">50 / hal</option>
                        <option value="100">100 / hal</option>
                    </select>
                    <?php echo e($rows->links('livewire::simple-tailwind')); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showLosTest && $this->losTestResult): ?>
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold">Hasil Test LOS ONU</h3>
                    <button @click="show = false; $wire.closeLosTest()" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">SN:</span><span class="font-mono"><?php echo e($this->losTestResult['serial_number']); ?></span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Status:</span><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $this->losTestResult['status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->losTestResult['status'])]); ?>
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
<?php endif; ?></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">RX Power:</span><span class="font-mono"><?php echo e($this->losTestResult['rx_power_dbm'] ?? '-'); ?> dBm</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">TX Power:</span><span class="font-mono"><?php echo e($this->losTestResult['tx_power_dbm'] ?? '-'); ?> dBm</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">Diuji:</span><span class="text-xs"><?php echo e($this->losTestResult['tested_at']); ?></span></div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end">
                    <button @click="show = false; $wire.closeLosTest()" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Tutup</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->showMapPopup && $this->mapData): ?>
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-semibold">Lokasi: <?php echo e($this->mapData['name']); ?></h3>
                    <button @click="show = false; $wire.closeMapPopup()" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4">
                    <div class="aspect-video bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-400 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_40%,#e0f2fe,transparent_50%),radial-gradient(circle_at_70%_60%,#fef3c7,transparent_50%)] dark:bg-[radial-gradient(circle_at_30%_40%,#1e293b,transparent_50%),radial-gradient(circle_at_70%_60%,#334155,transparent_50%)]"></div>
                        <div class="relative z-10 text-center">
                            <svg class="w-10 h-10 mx-auto text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
                            <div class="font-mono text-xs mt-2"><?php echo e($this->mapData['lat']); ?>, <?php echo e($this->mapData['lng']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-2">
                    <button class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700">Open GIS</button>
                    <button @click="show = false; $wire.closeMapPopup()" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Tutup</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if (! $__env->hasRenderedOnce('3d4e00c5-fa74-4331-96c3-0b8f156b2e97')): $__env->markAsRenderedOnce('3d4e00c5-fa74-4331-96c3-0b8f156b2e97'); ?>
    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fiberIndex', () => ({
                init() {
                    Livewire.hook('commit', ({ component, succeed }) => {
                        if (component.name !== 'jaringan.fiber.index') return;
                        succeed(() => {});
                    });
                }
            }));
        });
    </script>
    <?php $__env->stopPush(); ?>
    <?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\jaringan\fiber\index.blade.php ENDPATH**/ ?>