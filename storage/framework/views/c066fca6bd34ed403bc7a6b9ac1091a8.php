<div class="space-y-0">
    <?php echo $__env->make('partials.enterprise.list-toolbar', [
        'title' => 'Laporan Pelanggan',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari nama/no pelanggan...',
        'showFiltersToggle' => true,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilters): ?>
        <?php echo $__env->make('partials.enterprise.filters', [
            'filters' => [
                ['key'=>'tahun','label'=>'Tahun','type'=>'select','options'=>collect(range(now()->year-3, now()->year+1))->flip()->map(fn($v,$k)=>$k)->all()],
                ['key'=>'bulan','label'=>'Bulan','type'=>'select','options'=>['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember']],
                ['key'=>'start_date','label'=>'Tgl Mulai','type'=>'date'],
                ['key'=>'end_date','label'=>'Tgl Selesai','type'=>'date'],
                ['key'=>'router_id','label'=>'Router','type'=>'select','options'=>$filterOptions['routers']],
                ['key'=>'paket_id','label'=>'Paket','type'=>'select','options'=>$filterOptions['pakets']],
                ['key'=>'wilayah','label'=>'Wilayah','type'=>'select','options'=>$filterOptions['wilayahs']],
                ['key'=>'sales_id','label'=>'Sales','type'=>'select','options'=>$filterOptions['sales']],
                ['key'=>'reseller_id','label'=>'Reseller','type'=>'select','options'=>$filterOptions['resellers']],
            ]
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php
        $s = $summary;
        $churnBad = ($s['churn_rate_pct'] ?? 0) > 3;
    ?>
    <?php echo $__env->make('partials.enterprise.summary-cards', [
        'items' => [
            ['label'=>'Total Pelanggan Aktif','value'=>number_format($s['total_aktif'] ?? 0),'color'=>'blue','icon'=>'users'],
            ['label'=>'Baru Bulan Ini','value'=>number_format($s['baru_bulan_ini'] ?? 0),'color'=>'green','icon'=>'trending-up'],
            ['label'=>'Suspend Bulan Ini','value'=>number_format($s['suspend_bulan_ini'] ?? 0),'color'=>'amber','icon'=>'alert-triangle'],
            ['label'=>'Churn Rate (%)','value'=>number_format($s['churn_rate_pct'] ?? 0,2,',','.').' %','color'=>$churnBad?'red':'cyan','icon'=>'activity'],
        ]
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
        <div class="px-3 py-2 flex items-center gap-2 overflow-x-auto whitespace-nowrap">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <button wire:click="setActiveTab('<?php echo e($k); ?>')" class="px-3 py-1.5 text-xs lg:text-sm rounded-md border transition-colors
                    <?php echo e($activeTab === $k ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700'); ?>">
                    <?php echo e($label); ?>

                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'growth'): ?>
        <div class="p-3 space-y-3">
            <?php
                $g = $growth;
                $w = 900; $h = 340; $padL = 50; $padR = 140; $padT = 20; $padB = 50;
                $cW = $w - $padL - $padR; $cH = $h - $padT - $padB;
                $n = count($g['labels'] ?? []); $barW = $n > 0 ? $cW / $n : 1; $bI = max(3, $barW * 0.7);
                $mB = $g['max_bar'] ?? 1; $mL = $g['max_line'] ?? 1;
                function yB($v, $mx, $h, $t){ return $h - ($mx>0?($v/$mx*$h):0) + $t; }
                function yL($v, $mx, $h, $t){ return $h - ($mx>0?($v/$mx*$h):0) + $t; }
            ?>
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Customer Growth (12 Bulan Terakhir)</h3>
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-emerald-500"></span>New</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-500"></span>Suspend</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-blue-600"></span>Total</span>
                    </div>
                </div>
                <svg viewBox="0 0 <?php echo e($w); ?> <?php echo e($h); ?>" class="w-full h-auto">
                    <g stroke="#e2e8f0" class="dark:stroke-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<=4;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $y = $padT + ($cH/4)*$i; ?>
                            <line x1="<?php echo e($padL); ?>" y1="<?php echo e($y); ?>" x2="<?php echo e($w-$padR); ?>" y2="<?php echo e($y); ?>" stroke-dasharray="3 3"/>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </g>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<$n;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $x = $padL + $i*$barW + ($barW - $bI)/2;
                            $aV = $g['add'][$i] ?? 0; $sV = $g['suspend'][$i] ?? 0;
                            $tA = yB($aV + $sV, $mB, $cH, $padT);
                            $tS = yB($sV, $mB, $cH, $padT);
                            $bT = $padT + $cH;
                            $h1 = $bT - $tS; $h2 = $tS - $tA;
                            $xC = $x + $bI/2;
                        ?>
                        <rect x="<?php echo e($x); ?>" y="<?php echo e($tS); ?>" width="<?php echo e($bI/2 - 1); ?>" height="<?php echo e($h1); ?>" fill="#10b981"/>
                        <rect x="<?php echo e($x + $bI/2); ?>" y="<?php echo e($tA); ?>" width="<?php echo e($bI/2 - 1); ?>" height="<?php echo e($h2); ?>" fill="#ef4444"/>
                        <rect x="<?php echo e($x + $bI/2); ?>" y="<?php echo e($tS); ?>" width="<?php echo e($bI/2 - 1); ?>" height="<?php echo e($h1 - $h2); ?>" fill="#ef4444" style="display: none"/>
                        <?php
                            $totH = $h1;
                            $hS2 = min($h1, $h2);
                            $hA2 = max(0, $h1 - $hS2);
                        ?>
                        <rect x="<?php echo e($x); ?>" y="<?php echo e($bT - $hA2); ?>" width="<?php echo e($bI/2 - 1); ?>" height="<?php echo e($hA2); ?>" fill="#10b981"/>
                        <text x="<?php echo e($xC); ?>" y="<?php echo e($h - 25); ?>" text-anchor="middle" class="fill-slate-500 text-[9px]"><?php echo e($g['labels'][$i]); ?></text>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php
                        $linePts = [];
                        for($i=0;$i<$n;$i++){
                            $x = $padL + $i*$barW + ($barW - $bI)/2 + $bI/2;
                            $y = yL($g['line_total'][$i] ?? 0, $mL, $cH, $padT);
                            $linePts[] = "$x,$y";
                        }
                    ?>
                    <polyline points="<?php echo e(implode(' ', $linePts)); ?>" fill="none" stroke="#2563eb" stroke-width="2.5"/>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<$n;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $x = $padL + $i*$barW + ($barW - $bI)/2 + $bI/2;
                            $y = yL($g['line_total'][$i] ?? 0, $mL, $cH, $padT);
                        ?>
                        <circle cx="<?php echo e($x); ?>" cy="<?php echo e($y); ?>" r="3" fill="#2563eb"/>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<=4;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $y = $padT + ($cH/4)*$i; $val = round($mL - ($mL/4)*$i); ?>
                        <text x="<?php echo e($padL-6); ?>" y="<?php echo e($y+3); ?>" text-anchor="end" class="fill-slate-400 text-[9px]"><?php echo e(number_format($val)); ?></text>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </svg>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Tabel Pertumbuhan Bulanan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Bulan</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Awal</th>
                                <th class="text-right px-3 py-2 font-medium text-emerald-700 dark:text-emerald-400">+ Add</th>
                                <th class="text-right px-3 py-2 font-medium text-red-700 dark:text-red-400">- Suspend</th>
                                <th class="text-right px-3 py-2 font-medium text-blue-700 dark:text-blue-400">Akhir</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Growth %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $g['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $gp = $r['growth_pct'] ?? 0;
                                    $gc = $gp >= 0 ? 'text-emerald-600' : 'text-red-600';
                                    $gs = $gp >= 0 ? '+' : '';
                                ?>
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="px-3 py-1.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($r['bulan']); ?></td>
                                    <td class="text-right px-3 py-1.5 text-slate-500"><?php echo e(number_format($r['awal'])); ?></td>
                                    <td class="text-right px-3 py-1.5 text-emerald-600 font-semibold">+<?php echo e(number_format($r['add'])); ?></td>
                                    <td class="text-right px-3 py-1.5 text-red-600 font-semibold">-<?php echo e(number_format($r['suspend'])); ?></td>
                                    <td class="text-right px-3 py-1.5 text-blue-700 dark:text-blue-400 font-bold"><?php echo e(number_format($r['akhir'])); ?></td>
                                    <td class="text-right px-3 py-1.5 font-semibold <?php echo e($gc); ?>"><?php echo e($gs.number_format($gp,2,',','.')); ?>%</td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif($activeTab === 'activations'): ?>
        <div class="p-3 space-y-3">
            <?php
                $a = $activations;
                $w = 900; $h = 260; $padL = 50; $padR = 20; $padT = 20; $padB = 40;
                $cW = $w - $padL - $padR; $cH = $h - $padT - $padB;
                $n = count($a['labels']); $barW = $n>0?$cW/$n:1; $bI = max(2, $barW*0.75);
                $mV = $a['max_val'] ?? 1;
            ?>
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Aktivasi per Hari (30 Hari)</h3>
                    <span class="text-[10px] text-slate-500">Total: <strong class="text-slate-700 dark:text-slate-200"><?php echo e(array_sum($a['counts'] ?? [])); ?></strong></span>
                </div>
                <svg viewBox="0 0 <?php echo e($w); ?> <?php echo e($h); ?>" class="w-full h-auto">
                    <g stroke="#e2e8f0" class="dark:stroke-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<=4;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $y = $padT + ($cH/4)*$i; $val = $mV - ($mV/4)*$i; ?>
                            <line x1="<?php echo e($padL); ?>" y1="<?php echo e($y); ?>" x2="<?php echo e($w-$padR); ?>" y2="<?php echo e($y); ?>" stroke-dasharray="3 3"/>
                            <text x="<?php echo e($padL-6); ?>" y="<?php echo e($y+3); ?>" text-anchor="end" class="fill-slate-400 text-[9px]"><?php echo e(number_format($val)); ?></text>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </g>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0;$i<$n;$i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $v = $a['counts'][$i] ?? 0;
                            $x = $padL + $i*$barW + ($barW - $bI)/2;
                            $tY = $cH - ($mV>0?($v/$mV*$cH):0) + $padT;
                            $hi = $padT + $cH - $tY;
                        ?>
                        <rect x="<?php echo e($x); ?>" y="<?php echo e($tY); ?>" width="<?php echo e($bI); ?>" height="<?php echo e($hi); ?>" fill="#6366f1" rx="2"/>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i % max(1, (int)($n/10)) === 0): ?>
                            <text x="<?php echo e($x + $bI/2); ?>" y="<?php echo e($h - 22); ?>" text-anchor="middle" class="fill-slate-500 text-[9px]"><?php echo e($a['labels'][$i]); ?></text>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </svg>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Detail Aktivasi Harian</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Tanggal</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Jml Aktivasi</th>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Top 3 Paket</th>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Top 3 Sales</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Avg Kontrak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $a['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="px-3 py-1.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($r['tanggal']); ?></td>
                                    <td class="text-right px-3 py-1.5 font-bold text-blue-600 dark:text-blue-400"><?php echo e($r['jumlah_aktivasi']); ?></td>
                                    <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $r['paket_top']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-[10px] mr-1"><?php echo e($p); ?> (<?php echo e($c); ?>)</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($r['paket_top']) === 0): ?> <span class="text-slate-400 text-[10px]">-</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $r['sales_top']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-[10px] mr-1"><?php echo e($p); ?> (<?php echo e($c); ?>)</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($r['sales_top']) === 0): ?> <span class="text-slate-400 text-[10px]">-</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="text-right px-3 py-1.5 font-semibold text-slate-700 dark:text-slate-200">Rp <?php echo e(number_format($r['avg_nilai_kontrak'] ?? 0, 0, ',', '.')); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif($activeTab === 'suspensions'): ?>
        <div class="p-3 space-y-3">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-2">Distribusi Alasan Suspend</h3>
                    <?php
                        $pie = $suspensions;
                        $lbl = $pie['pie_labels'] ?? ['Tidak Ada Data'];
                        $vals = $pie['pie_pct'] ?? [100];
                        $colors = ['#6366f1','#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899'];
                        $cx = 130; $cy = 130; $rad = 100;
                        $cumulative = 0;
                    ?>
                    <svg viewBox="0 0 260 260" class="w-full h-auto max-h-56">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lbl; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $pct = $vals[$i] ?? 0;
                                $startAngle = ($cumulative/100) * 2 * pi() - pi()/2;
                                $cumulative += $pct;
                                $endAngle = ($cumulative/100) * 2 * pi() - pi()/2;
                                $x1 = $cx + $rad * cos($startAngle);
                                $y1 = $cy + $rad * sin($startAngle);
                                $x2 = $cx + $rad * cos($endAngle);
                                $y2 = $cy + $rad * sin($endAngle);
                                $large = ($endAngle - $startAngle) > pi() ? 1 : 0;
                                $c = $colors[$i % count($colors)];
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pct > 0): ?>
                                <path d="M <?php echo e($cx); ?> <?php echo e($cy); ?> L <?php echo e($x1); ?> <?php echo e($y1); ?> A <?php echo e($rad); ?> <?php echo e($rad); ?> 0 <?php echo e($large); ?> 1 <?php echo e($x2); ?> <?php echo e($y2); ?> Z" fill="<?php echo e($c); ?>" stroke="white" stroke-width="2" class="dark:stroke-slate-800"/>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </svg>
                    <div class="mt-2 space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lbl; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex items-center justify-between text-[11px]">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background:<?php echo e($colors[$i % count($colors)]); ?>"></span>
                                    <span class="text-slate-700 dark:text-slate-200 truncate"><?php echo e($l); ?></span>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-100 ml-2"><?php echo e($vals[$i] ?? 0); ?>%</span>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                    <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Suspend Harian</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Tanggal</th>
                                    <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Jml Suspend</th>
                                    <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Alasan Top</th>
                                    <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">% dari Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suspensions['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                        <td class="px-3 py-1.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($r['tanggal']); ?></td>
                                        <td class="text-right px-3 py-1.5 font-bold text-red-600"><?php echo e($r['jumlah_suspend']); ?></td>
                                        <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $r['alasan_top']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-[10px] mr-1"><?php echo e($p); ?> (<?php echo e($c); ?>)</span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($r['alasan_top']) === 0): ?> <span class="text-slate-400 text-[10px]">-</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="text-right px-3 py-1.5 font-semibold <?php echo e(($r['pct_aktif_suspend'] ?? 0) > 1 ? 'text-red-600' : 'text-slate-600 dark:text-slate-200'); ?>"><?php echo e(number_format($r['pct_aktif_suspend'] ?? 0,3,',','.')); ?>%</td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif($activeTab === 'terminations'): ?>
        <div class="p-3 space-y-3">
            <?php $sm = $terminations['summary'] ?? []; ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2">
                    <div class="text-[10px] text-slate-500">Total Terminasi 30h</div>
                    <div class="text-base font-bold text-red-600"><?php echo e(number_format($sm['total_terminasi'] ?? 0)); ?></div>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2">
                    <div class="text-[10px] text-slate-500">Recovery Berhasil</div>
                    <div class="text-base font-bold text-emerald-600"><?php echo e(number_format($sm['total_recovery'] ?? 0)); ?></div>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2">
                    <div class="text-[10px] text-slate-500">Recovery Rate</div>
                    <div class="text-base font-bold text-blue-600"><?php echo e(number_format($sm['recovery_rate_pct'] ?? 0,2,',','.')); ?>%</div>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2">
                    <div class="text-[10px] text-slate-500">Alasan Terbanyak</div>
                    <?php $ar = $sm['top_alasan'] ?? []; $firstKey = array_key_first($ar); ?>
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate"><?php echo e($firstKey ? "$firstKey (".$ar[$firstKey].")" : '-'); ?></div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Terminasi & Recovery Harian</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Tanggal</th>
                                <th class="text-right px-3 py-2 font-medium text-red-700 dark:text-red-400">Nonaktif Permanen</th>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Alasan Top</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">% Churn</th>
                                <th class="text-right px-3 py-2 font-medium text-emerald-700 dark:text-emerald-400">Recovery</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $terminations['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $cc = ($r['churn_pct'] ?? 0) > 2 ? 'text-red-600 font-bold' : 'text-slate-600 dark:text-slate-300'; ?>
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="px-3 py-1.5 font-medium text-slate-700 dark:text-slate-200"><?php echo e($r['tanggal']); ?></td>
                                    <td class="text-right px-3 py-1.5 font-bold text-red-600"><?php echo e($r['jumlah_nonaktif']); ?></td>
                                    <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $r['alasan_top']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-[10px] mr-1"><?php echo e($p); ?> (<?php echo e($c); ?>)</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($r['alasan_top']) === 0): ?> <span class="text-slate-400 text-[10px]">-</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="text-right px-3 py-1.5 font-semibold <?php echo e($cc); ?>"><?php echo e(number_format($r['churn_pct'] ?? 0,2,',','.')); ?>%</td>
                                    <td class="text-right px-3 py-1.5 font-bold text-emerald-600">+<?php echo e($r['recovery']); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\laporan\pelanggan\index.blade.php ENDPATH**/ ?>