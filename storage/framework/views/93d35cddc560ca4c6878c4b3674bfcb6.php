<div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'keuangan-income-periode-'.e(now()->timestamp).''; ?>wire:key="keuangan-income-periode-<?php echo e(now()->timestamp); ?>">
    <?php echo $__env->make('partials.enterprise.list-toolbar', [
        'title' => 'Income Periode',
        'primaryLabel' => null,
        'primaryAction' => null,
        'actions' => [
            ['label' => 'Export Excel', 'icon' => 'download', 'action' => 'exportExcelAction()'],
            ['label' => 'Export PDF', 'icon' => 'file-text', 'action' => 'exportPdfAction()'],
        ],
        'searchPlaceholder' => 'Cari...',
        'showFiltersToggle' => true,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $pd = $periodData ?? [];
        $growth = $pd['growth_percent'] ?? 0;
        $growthClass = $growth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
        $growthIcon = $growth >= 0 ? 'trending-up' : 'trending-down';
    ?>
    <?php echo $__env->make('partials.enterprise.summary-cards', [
        'items' => [
            ['label' => 'Periode Aktif', 'value' => $pd['period_label'] ?? '-', 'color' => 'blue', 'icon' => 'calendar'],
            ['label' => 'Total Pendapatan', 'value' => 'Rp ' . number_format($pd['current_total'] ?? 0, 0, ',', '.'), 'color' => 'green', 'icon' => 'dollar-sign'],
            ['label' => 'Growth (' . (($pd['type'] ?? 'monthly') === 'monthly' ? 'MoM' : 'YoY') . ')', 'value' => '<span class="' . $growthClass . ' font-semibold">' . ($growth >= 0 ? '+' : '') . number_format($growth, 2) . '%</span>', 'color' => $growth >= 0 ? 'green' : 'red', 'icon' => $growthIcon],
            ['label' => 'vs Periode Lalu', 'value' => 'Rp ' . number_format($pd['prev_total'] ?? 0, 0, ',', '.'), 'color' => 'slate', 'icon' => 'activity'],
            ['label' => 'Periode Lalu', 'value' => $pd['prev_period_label'] ?? '-', 'color' => 'purple', 'icon' => 'clock'],
        ],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilters): ?>
        <?php echo $__env->make('partials.enterprise.filters', ['filters' => $filterConfig], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
        <div class="px-3 py-2 bg-red-50 dark:bg-red-900/30 border-b border-red-100 dark:border-red-800 text-sm text-red-700 dark:text-red-200">
            <?php echo e($errorMessage); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
        <div class="p-8 flex items-center justify-center text-slate-500 dark:text-slate-400">
            <svg class="w-6 h-6 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Memuat data...
        </div>
    <?php else: ?>
        <div class="p-3 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Komparasi: <?php echo e($pd['period_label'] ?? '-'); ?> vs <?php echo e($pd['prev_period_label'] ?? '-'); ?>

                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#3b82f6"></span> Saat Ini</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#cbd5e1"></span> Periode Lalu</span>
                    </div>
                </div>
                <?php
                    $labels = $pd['labels'] ?? [];
                    $curr = $pd['current_data'] ?? [];
                    $prev = $pd['prev_data'] ?? [];
                    $maxV = max(1, ...array_merge($curr, $prev));
                    $cW = 100;
                    $cH = 40;
                    $n = max(1, count($labels));
                    $groupW = $n > 0 ? ($cW / $n) : 0;
                    $barW = $groupW * 0.35;
                    $toYV = fn($v) => $cH - ($v / $maxV) * $cH;
                ?>
                <div class="w-full overflow-hidden">
                    <svg viewBox="0 -2 <?php echo e($cW + 2); ?> <?php echo e($cH + 9); ?>" preserveAspectRatio="none" class="w-full h-40 lg:h-56">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($g = 0; $g < 5; $g++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $gy = $cH * ($g / 4); ?>
                            <line x1="0" y1="<?php echo e(number_format($gy, 2)); ?>" x2="<?php echo e($cW); ?>" y2="<?php echo e(number_format($gy, 2)); ?>" stroke="#e2e8f0" stroke-width="0.15" stroke-dasharray="0.5,0.5"/>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($n > 0): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < $n; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $gx = $groupW * $i + $groupW * 0.15;
                                    $cv = $curr[$i] ?? 0;
                                    $pv = $prev[$i] ?? 0;
                                    $ch = $cH - $toYV($cv);
                                    $ph = $cH - $toYV($pv);
                                    $cy1 = $toYV($cv);
                                    $py1 = $toYV($pv);
                                ?>
                                <rect x="<?php echo e(number_format($gx, 3)); ?>" y="<?php echo e(number_format($cy1, 3)); ?>" width="<?php echo e(number_format($barW, 3)); ?>" height="<?php echo e(number_format($ch, 3)); ?>" fill="#3b82f6" rx="0.4"/>
                                <rect x="<?php echo e(number_format($gx + $barW + 0.2, 3)); ?>" y="<?php echo e(number_format($py1, 3)); ?>" width="<?php echo e(number_format($barW, 3)); ?>" height="<?php echo e(number_format($ph, 3)); ?>" fill="#cbd5e1" rx="0.4"/>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i % max(1, (int) ceil($n/12)) === 0): ?>
                                    <text x="<?php echo e(number_format($gx + $groupW * 0.3, 3)); ?>" y="<?php echo e($cH + 5); ?>" text-anchor="middle" font-size="2.2" fill="#64748b"><?php echo e($labels[$i] ?? ''); ?></text>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 overflow-x-auto border-b border-slate-200 dark:border-slate-700">
            <div class="px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-700/40 border-y border-slate-200 dark:border-slate-700">Tabel Periode</div>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/30 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('period')">
                            Periode
                        </th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Jumlah Invoice</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Jumlah Payment</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Total Pendapatan</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Growth (%)</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Top Paket</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $r = is_array($row) ? $row : $row->toArray();
                            $gr = $r['growth'] ?? 0;
                            $grClass = $gr >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400';
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-3 py-2 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100"><?php echo e($r['period'] ?? '-'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300"><?php echo e(number_format($r['invoice_count'] ?? 0, 0, ',', '.')); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300"><?php echo e(number_format($r['payment_count'] ?? 0, 0, ',', '.')); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-emerald-700 dark:text-emerald-400">Rp <?php echo e(number_format($r['total'] ?? 0, 0, ',', '.')); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold <?php echo e($grClass); ?>">
                                <?php echo e($gr >= 0 ? '+' : ''); ?><?php echo e(number_format($gr, 2)); ?>%
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($r['top_package'] ?? '-'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                <div class="inline-flex gap-1">
                                    <button wire:click="exportRowExcel('<?php echo e($r['period'] ?? ''); ?>')" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">
                                        Excel
                                    </button>
                                    <button wire:click="exportRowPdf('<?php echo e($r['period'] ?? ''); ?>')" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">
                                        PDF
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="7" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Tidak ada data pendapatan untuk periode ini.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\keuangan\income-periode\index.blade.php ENDPATH**/ ?>