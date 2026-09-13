<div class="space-y-0">
    @include('partials.enterprise.list-toolbar', [
        'title' => 'Laporan Pendapatan',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari tagihan/payment...',
        'showFiltersToggle' => true,
    ])

    @if($showFilters)
        @include('partials.enterprise.filters', [
            'filters' => [
                ['key'=>'tahun','label'=>'Tahun','type'=>'select','options'=>collect(range(now()->year-3, now()->year+1))->flip()->map(fn($v,$k)=>$k)->all()],
                ['key'=>'bulan','label'=>'Bulan','type'=>'select','options'=>['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember']],
                ['key'=>'start_date','label'=>'Tgl Mulai','type'=>'date'],
                ['key'=>'end_date','label'=>'Tgl Selesai','type'=>'date'],
                ['key'=>'router_id','label'=>'Router','type'=>'select','options'=>$filterOptions['routers']],
                ['key'=>'paket_id','label'=>'Paket','type'=>'select','options'=>$filterOptions['pakets']],
                ['key'=>'sales_id','label'=>'Sales','type'=>'select','options'=>$filterOptions['sales']],
                ['key'=>'reseller_id','label'=>'Reseller','type'=>'select','options'=>$filterOptions['resellers']],
            ]
        ])
    @endif

    @php
        $s = $summary;
        $vsClass = ($s['vs_lalu_bulan_pct'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
        $vsIcon = ($s['vs_lalu_bulan_pct'] ?? 0) >= 0 ? 'trending-up' : 'trending-down';
        $vsLabel = ($s['vs_lalu_bulan_pct'] ?? 0) >= 0 ? '+' : '';
    @endphp
    @include('partials.enterprise.summary-cards', [
        'items' => [
            ['label'=>'Pendapatan Bulan Ini','value'=>'Rp '.number_format($s['pendapatan_bulan_ini'] ?? 0,0,',','.'),'color'=>'blue','icon'=>'dollar-sign'],
            ['label'=>'vs Lalu Bulan','value'=>$vsLabel.number_format($s['vs_lalu_bulan_pct'] ?? 0,2,',','.').' %','color'=>(($s['vs_lalu_bulan_pct'] ?? 0)>=0?'green':'red'),'icon'=>$vsIcon],
            ['label'=>'YTD','value'=>'Rp '.number_format($s['ytd'] ?? 0,0,',','.'),'color'=>'purple','icon'=>'activity'],
            ['label'=>'Avg per Hari','value'=>'Rp '.number_format($s['avg_per_hari'] ?? 0,0,',','.'),'color'=>'cyan','icon'=>'trending-up'],
        ]
    ])

    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
        <div class="px-3 py-2 flex items-center gap-2 overflow-x-auto whitespace-nowrap">
            @foreach($tabs as $k => $label)
                <button wire:click="setActiveTab('{{ $k }}')" class="px-3 py-1.5 text-xs lg:text-sm rounded-md border transition-colors
                    {{ $activeTab === $k ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if($activeTab === 'chart')
        <div class="p-3 space-y-3">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Pendapatan per Hari (Stacked Bar + Total Line)</h3>
                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-blue-500"></span>PPPoE</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-emerald-500"></span>Hotspot</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-amber-500"></span>Voucher</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-purple-600"></span>Total</span>
                    </div>
                </div>
                @php
                    $cd = $chartDaily;
                    $w = 900; $h = 320; $padL = 50; $padR = 20; $padT = 20; $padB = 40;
                    $chartW = $w - $padL - $padR; $chartH = $h - $padT - $padB;
                    $n = count($cd['labels']); $barW = $n > 0 ? $chartW / $n : 1;
                    $maxV = (float) ($cd['max_val'] ?? 1); $barInner = max(2, $barW * 0.75);
                    function hBar($v, $max, $hgt) { return $hgt - ($max>0 ? (($v/$max)*$hgt) : 0); }
                @endphp
                <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full h-auto">
                    <g stroke="#e2e8f0" class="dark:stroke-slate-700">
                        @for($i=0;$i<=4;$i++)
                            @php $y = $padT + ($chartH/4)*$i; $val = $maxV - ($maxV/4)*$i; @endphp
                            <line x1="{{ $padL }}" y1="{{ $y }}" x2="{{ $w-$padR }}" y2="{{ $y }}" stroke-dasharray="3 3"/>
                            <text x="{{ $padL-6 }}" y="{{ $y+3 }}" text-anchor="end" class="fill-slate-400 text-[9px]">Rp {{ number_format($val,0,',','') }}</text>
                        @endfor
                    </g>
                    @for($i=0;$i<$n;$i++)
                        @php
                            $x = $padL + $i*$barW + ($barW - $barInner)/2;
                            $p = hBar($cd['pppoe'][$i] ?? 0, $maxV, $chartH) + $padT;
                            $p2 = hBar(($cd['pppoe'][$i] ?? 0) + ($cd['hotspot'][$i] ?? 0), $maxV, $chartH) + $padT;
                            $p3 = hBar(($cd['pppoe'][$i] ?? 0) + ($cd['hotspot'][$i] ?? 0) + ($cd['voucher'][$i] ?? 0), $maxV, $chartH) + $padT;
                            $topTot = hBar($cd['total'][$i] ?? 0, $maxV, $chartH) + $padT;
                            $hTot = $padT + $chartH - $topTot;
                            $h1 = $padT + $chartH - $p;
                            $h2 = $p - $p2;
                            $h3 = $p2 - $p3;
                        @endphp
                        <rect x="{{ $x }}" y="{{ $p }}" width="{{ $barInner }}" height="{{ $h1 }}" fill="#3b82f6"/>
                        <rect x="{{ $x }}" y="{{ $p2 }}" width="{{ $barInner }}" height="{{ $h2 }}" fill="#10b981"/>
                        <rect x="{{ $x }}" y="{{ $p3 }}" width="{{ $barInner }}" height="{{ $h3 }}" fill="#f59e0b"/>
                        <circle cx="{{ $x + $barInner/2 }}" cy="{{ $topTot }}" r="2.5" fill="#9333ea"/>
                        @if($i % max(1, (int)($n/10)) === 0)
                            <text x="{{ $x + $barInner/2 }}" y="{{ $h - 22 }}" text-anchor="middle" class="fill-slate-500 text-[9px]">{{ $cd['labels'][$i] }}</text>
                        @endif
                    @endfor
                    @php
                        $linePts = [];
                        for($i=0;$i<$n;$i++){
                            $x = $padL + $i*$barW + ($barW - $barInner)/2 + $barInner/2;
                            $y = hBar($cd['total'][$i] ?? 0, $maxV, $chartH) + $padT;
                            $linePts[] = "$x,$y";
                        }
                    @endphp
                    <polyline points="{{ implode(' ', $linePts) }}" fill="none" stroke="#9333ea" stroke-width="2"/>
                </svg>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                    <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-2">Breakdown Bulan Ini</h4>
                    @php
                        $total = array_sum($cd['pppoe'] ?? []) + array_sum($cd['hotspot'] ?? []) + array_sum($cd['voucher'] ?? []);
                        $t_pppoe = array_sum($cd['pppoe'] ?? []);
                        $t_hotspot = array_sum($cd['hotspot'] ?? []);
                        $t_voucher = array_sum($cd['voucher'] ?? []);
                        $pcts = [
                            ['PPPoE', $t_pppoe, 'bg-blue-500'],
                            ['Hotspot', $t_hotspot, 'bg-emerald-500'],
                            ['Voucher', $t_voucher, 'bg-amber-500'],
                        ];
                    @endphp
                    <div class="space-y-2">
                        @foreach($pcts as $p)
                            @php $pct = $total > 0 ? round($p[1]/$total*100,1) : 0; @endphp
                            <div>
                                <div class="flex justify-between text-[11px] mb-1">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $p[0] }}</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $pct }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                    <div class="h-full {{ $p[2] }}" style="width:{{ $pct }}%"></div>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Rp {{ number_format($p[1],0,',','.') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3 lg:col-span-2">
                    <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-2">Detail Ringkasan</h4>
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="text-left py-1.5 px-2 text-slate-600 dark:text-slate-300 font-medium">Periode</th>
                                <th class="text-right py-1.5 px-2 text-slate-600 dark:text-slate-300 font-medium">PPPoE</th>
                                <th class="text-right py-1.5 px-2 text-slate-600 dark:text-slate-300 font-medium">Hotspot</th>
                                <th class="text-right py-1.5 px-2 text-slate-600 dark:text-slate-300 font-medium">Voucher</th>
                                <th class="text-right py-1.5 px-2 text-slate-600 dark:text-slate-300 font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @php
                                $lastN = count($cd['labels']) - 1;
                                $details = [
                                    ['Hari Ini', $lastN >= 0 ? $lastN : 0],
                                    ['7 Hari Terakhir', max(0, $lastN - 6), $lastN],
                                    ['Range Full', 0, $lastN],
                                ];
                            @endphp
                            @foreach($details as $d)
                                @php
                                    $from = $d[1]; $to = $d[2] ?? $d[1];
                                    $pp = $hs = $vc = 0;
                                    for($i=$from;$i<=$to && $i<=$lastN;$i++){
                                        $pp += $cd['pppoe'][$i] ?? 0;
                                        $hs += $cd['hotspot'][$i] ?? 0;
                                        $vc += $cd['voucher'][$i] ?? 0;
                                    }
                                    $tot = $pp + $hs + $vc;
                                @endphp
                                <tr>
                                    <td class="py-1.5 px-2 text-slate-700 dark:text-slate-200">{{ $d[0] }}</td>
                                    <td class="text-right py-1.5 px-2 text-blue-600 dark:text-blue-400 font-medium">Rp {{ number_format($pp,0,',','.') }}</td>
                                    <td class="text-right py-1.5 px-2 text-emerald-600 dark:text-emerald-400 font-medium">Rp {{ number_format($hs,0,',','.') }}</td>
                                    <td class="text-right py-1.5 px-2 text-amber-600 dark:text-amber-400 font-medium">Rp {{ number_format($vc,0,',','.') }}</td>
                                    <td class="text-right py-1.5 px-2 font-semibold text-slate-900 dark:text-slate-50">Rp {{ number_format($tot,0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($activeTab === 'comparison')
        <div class="p-3 space-y-3">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Comparison Periodik</h3>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-medium text-purple-700 dark:text-purple-400">Periode N:</span> {{ $comparison['period_label_n'] }} &bull;
                        <span class="font-medium text-slate-600 dark:text-slate-300 ml-2">Periode N-1:</span> {{ $comparison['period_label_n1'] }}
                    </div>
                </div>
                @php
                    $rows = $comparison['rows'];
                    $maxVal = 1;
                    foreach($rows as $r){ if(is_numeric($r['periode_n'])) $maxVal = max($maxVal, (float)$r['periode_n'], (float)$r['periode_n1']); }
                @endphp
                <div class="space-y-3">
                    @foreach($rows as $idx => $r)
                        @php
                            $vn = (float) (is_numeric($r['periode_n']) ? $r['periode_n'] : 0);
                            $vn1 = (float) (is_numeric($r['periode_n1']) ? $r['periode_n1'] : 0);
                            $wN = $maxVal > 0 ? ($vn/$maxVal*100) : 0;
                            $wN1 = $maxVal > 0 ? ($vn1/$maxVal*100) : 0;
                            $diffPct = $r['pct'] ?? 0;
                            $diffColor = $diffPct >= 0 ? 'text-emerald-600' : 'text-red-600';
                            $diffSign = $diffPct >= 0 ? '+' : '';
                            $formatter = fn($v, $m) => str_contains($m, '%') ? number_format($v,2).'%' : (str_contains($m, 'Pendapatan') ? 'Rp '.number_format($v,0,',','.') : number_format($v));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $r['metric'] }}</span>
                                <span class="{{ $diffColor }} font-medium">{{ $diffSign.number_format($diffPct,2,',','.') }}%</span>
                            </div>
                            <div class="grid grid-cols-12 gap-2 items-center">
                                <div class="col-span-12 lg:col-span-5 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-20 text-[10px] text-purple-700 dark:text-purple-400">Periode N</span>
                                        <div class="flex-1 h-4 rounded bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                            <div class="h-full bg-purple-500" style="width:{{ $wN }}%"></div>
                                        </div>
                                        <span class="w-28 text-right text-[11px] font-semibold text-slate-700 dark:text-slate-200">{{ $formatter($vn, $r['metric']) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-20 text-[10px] text-slate-500 dark:text-slate-400">Periode N-1</span>
                                        <div class="flex-1 h-4 rounded bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                            <div class="h-full bg-slate-400" style="width:{{ $wN1 }}%"></div>
                                        </div>
                                        <span class="w-28 text-right text-[11px] text-slate-500 dark:text-slate-400">{{ $formatter($vn1, $r['metric']) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Tabel Perbandingan Lengkap</h3>
                </div>
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Metric</th>
                            <th class="text-right px-3 py-2 font-medium text-purple-700 dark:text-purple-400">Periode N</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-500 dark:text-slate-400">Periode N-1</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Selisih</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Growth</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @foreach($rows as $r)
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50/70 dark:hover:bg-slate-700/40">
                                <td class="px-3 py-2 text-slate-700 dark:text-slate-200 font-medium">{{ $r['metric'] }}</td>
                                <td class="text-right px-3 py-2 text-purple-700 dark:text-purple-400 font-semibold">{{ is_numeric($r['periode_n']) && !str_contains($r['metric'], '%') ? (str_contains($r['metric'],'Pendapatan') ? 'Rp '.number_format($r['periode_n'],0,',','.') : number_format($r['periode_n'])) : (str_contains($r['metric'], '%') ? number_format($r['periode_n'],2,',','.').'%' : $r['periode_n']) }}</td>
                                <td class="text-right px-3 py-2 text-slate-500 dark:text-slate-400">{{ is_numeric($r['periode_n1']) && !str_contains($r['metric'], '%') ? (str_contains($r['metric'],'Pendapatan') ? 'Rp '.number_format($r['periode_n1'],0,',','.') : number_format($r['periode_n1'])) : (str_contains($r['metric'], '%') ? number_format($r['periode_n1'],2,',','.').'%' : $r['periode_n1']) }}</td>
                                <td class="text-right px-3 py-2 text-slate-700 dark:text-slate-200">
                                    @if(is_numeric($r['diff']))
                                        {{ ($r['diff'] >= 0 ? '+' : '') . (str_contains($r['metric'],'Pendapatan') ? 'Rp '.number_format($r['diff'],0,',','.') : (str_contains($r['metric'],'%') ? number_format($r['diff'],2,',','.').' %' : number_format($r['diff']))) }}
                                    @else - @endif
                                </td>
                                <td class="text-right px-3 py-2 font-semibold {{ $r['pct'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ ($r['pct'] >= 0 ? '+' : '') . number_format($r['pct'],2,',','.') }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($activeTab === 'top_packages')
        <div class="p-3">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Top 10 Paket Berdasarkan Pendapatan</h3>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ count($topPackages) }} paket</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300 w-12">Rank</th>
                                <th class="text-left px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Nama Paket</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Jml Pelanggan</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300">Total Pendapatan</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300 w-40">Kontribusi</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 dark:text-slate-300 w-28">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @foreach($topPackages as $p)
                                @php
                                    $trendCls = ($p['trend_pct'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600';
                                    $trendSign = ($p['trend_pct'] ?? 0) >= 0 ? '+' : '';
                                    $maxContrib = collect($topPackages)->max('total_pendapatan') ?: 1;
                                    $barW = $maxContrib > 0 ? ($p['total_pendapatan']/$maxContrib*100) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50/70 dark:hover:bg-slate-700/40">
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold
                                            {{ $p['rank'] == 1 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
                                             : ($p['rank'] == 2 ? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
                                             : ($p['rank'] == 3 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300'
                                             : 'bg-slate-50 text-slate-500 dark:bg-slate-700/50 dark:text-slate-400')) }}">
                                            {{ $p['rank'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-800 dark:text-slate-100">{{ $p['nama_paket'] }}</td>
                                    <td class="text-right px-3 py-2 text-slate-700 dark:text-slate-200 font-medium">{{ number_format($p['jumlah_pelanggan'] ?? 0) }}</td>
                                    <td class="text-right px-3 py-2 font-semibold text-slate-900 dark:text-slate-50">Rp {{ number_format($p['total_pendapatan'],0,',','.') }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500" style="width:{{ min(100, $barW) }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-200 w-12 text-right">{{ number_format($p['kontribusi_pct'],1,',','.') }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-right px-3 py-2">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold {{ $trendCls }}">
                                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if(($p['trend_pct'] ?? 0) >= 0)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                @endif
                                            </svg>
                                            {{ $trendSign.number_format($p['trend_pct'],1,',','.') }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($topPackages) === 0)
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-slate-400 text-xs">Belum ada data paket pada periode yang dipilih.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @include('partials.enterprise.confirm-modal')
</div>
