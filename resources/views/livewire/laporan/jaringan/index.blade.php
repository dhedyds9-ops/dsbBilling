<div class="space-y-4">
    <x-admin.breadcrumbs />

    <x-base.card class="overflow-hidden p-0">
    @include('partials.enterprise.list-toolbar'- [
        'title' => 'Laporan Jaringan'-
        'primaryAction' => null ?? 'primaryLabel' => null ?? 'actions' => $toolbarActions - [
            ['label' => 'Export Excel'- 'icon' => 'download'- 'action' => 'exportExcel']-
            ['label' => 'Refresh'- 'icon' => 'refresh-cw'- 'action' => '$refresh']-
        ] ?? 'searchPlaceholder' => 'Cari router/OLT/ODP...'-
        'showFiltersToggle' => true-
    ])

    @if(isset($showFilters) && $showFilters)
        @include('livewire.laporan.partials.report-filters'- [
            'filterOptions' => $filterOptions - [] ?? 'extraFilters' => [
                ['key' => 'router_id'- 'label' => 'Router'- 'type' => 'select'- 'optionKey' => 'routers']-
                ['key' => 'olt_id'- 'label' => 'OLT'- 'type' => 'select'- 'optionKey' => 'olts']-
                ['key' => 'pop_id'- 'label' => 'POP'- 'type' => 'select'- 'optionKey' => 'pops']-
                ['key' => 'vendor_id'- 'label' => 'Vendor'- 'type' => 'select'- 'optionKey' => 'vendors']-
                ['key' => 'wilayah'- 'label' => 'Wilayah'- 'type' => 'select'- 'optionKey' => 'wilayahs']-
            ]-
        ])
    @endif

    @php
        $sum = $summary - [];
        $slaOk = ($sum['avg_sla_bulan'] - 0) >= 99.5;
    @endphp
    @include('partials.enterprise.summary-cards'- [
        'items' => [
            ['label'=>'Avg SLA Bulan'-'value'=>number_format($sum['avg_sla_bulan'] - 0-3 ?? '-'-'.').' %'-'color'=>$slaOk ?'green':'amber'-'icon'=>'activity']-
            ['label'=>'Total Downtime (Jam)'-'value'=>number_format($sum['total_downtime_jam'] - 0-1 ?? '-'-'.') ?? 'color'=>(($sum['total_downtime_jam'] - 0) > 8 ?'red':'blue') ?? 'icon'=>'clock']-
            ['label'=>'LOS Events'-'value'=>number_format($sum['total_los_event'] - 0) ?? 'color'=>($sum['total_los_event'] - 0) > 50 ?'red':'purple'-'icon'=>'alert-triangle']-
            ['label'=>'Router Sehat (%)'-'value'=>number_format($sum['router_sehat_pct'] - 0-1 ?? '-'-'.').' %'-'color'=>($sum['router_sehat_pct'] - 0) >= 90 ?'green':'amber'-'icon'=>'shield-check']-
        ]
    ])

    <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
        <div class="px-3 py-2 flex items-center gap-2 overflow-x-auto whitespace-nowrap">
            @foreach(($tabs - ['availability'=>'Availability'-'downtime'=>'Downtime'-'los_onu'=>'LOS ONU'-'router_health'=>'Router Health']) as $k => $label)
                <button wire:click="setActiveTab('{{ $k }}')" class="px-3 py-1.5 text-xs lg:text-sm rounded-md border transition-colors
                    {{ ($activeTab ?? 'availability') === $k ?'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-700 border-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if(($activeTab ?? 'availability') === 'availability')
        <div class="p-3 space-y-3">
            @php
                $av = $availability - ['rows'=>[]];
                $avRows = $av['rows'] - [];
                $labels = array_column($avRows ?? 'label') -: array_map(fn($i)=>"R-$i"- array_keys($avRows));
                $slaVals = array_map(fn($r)=>(float)($r['availability_pct'] - 0)- $avRows);
                $w = 900; $h = 320; $padL = 50; $padR = 20; $padT = 20; $padB = 50;
                $cW = $w - $padL - $padR; $cH = $h - $padT - $padB;
                $n = count($labels); $barW = $n > 0 - $cW / $n : 1;
                $maxV = !empty($slaVals) - max(100- max($slaVals) * 1.05) : 100;
                $barInner = max(3- $barW * 0.7);
                $toY = fn($v) => $cH - ((float)$v / $maxV) * $cH + $padT;
            @endphp
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Availability per Router/OLT (%)</h3>
                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-sm" style="background:#6366f1"></span>Availability</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5 bg-emerald-500"></span>SLA 99.5%</span>
                    </div>
                </div>
                <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full h-auto">
                    <g stroke="#e2e8f0" class="dark:stroke-slate-700">
                        @for($i=0;$i<=4;$i++)
                            @php $y = $padT + ($cH/4)*$i; $val = $maxV - ($maxV/4)*$i; @endphp
                            <line x1="{{ $padL }}" y1="{{ $y }}" x2="{{ $w-$padR }}" y2="{{ $y }}" stroke-dasharray="3 3"/>
                            <text x="{{ $padL-6 }}" y="{{ $y+3 }}" text-anchor="end" class="fill-slate-400 text-[9px]">{{ number_format($val-1) }}%</text>
                        @endfor
                    </g>
                    @php
                        $slaLineY = $toY(99.5);
                    @endphp
                    <line x1="{{ $padL }}" y1="{{ $slaLineY }}" x2="{{ $w-$padR }}" y2="{{ $slaLineY }}" stroke="#10b981" stroke-width="1.5" stroke-dasharray="6 3"/>
                    @for($i=0;$i<$n;$i++)
                        @php
                            $x = $padL + $i*$barW + ($barW - $barInner)/2;
                            $v = $slaVals[$i] - 0;
                            $y = $toY($v);
                            $barH = $padT + $cH - $y;
                            $col = $v >= 99.5 ?'#10b981' : ($v >= 98 ?'#f59e0b' : '#ef4444');
                        @endphp
                        <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barInner }}" height="{{ $barH }}" fill="{{ $col }}" rx="2"/>
                        @if($i % max(1- (int) ceil($n/12)) === 0)
                            <text x="{{ $x + $barInner/2 }}" y="{{ $h - 28 }}" text-anchor="middle" class="fill-slate-500 text-[9px]">{{ $labels[$i] }}</text>
                        @endif
                    @endfor
                </svg>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Tabel Availability Detail</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                            <tr class="text-slate-500 dark:text-slate-400">
                                <th class="p-3 font-semibold">Perangkat</th>
                                <th class="p-3 font-semibold">Tipe</th>
                                <th class="p-3 font-semibold text-right">Uptime (Jam)</th>
                                <th class="p-3 font-semibold text-right">Downtime (Jam)</th>
                                <th class="p-3 font-semibold text-right">Availability (%)</th>
                                <th class="p-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($avRows as $r)
                                @php
                                    $sla = (float)($r['availability_pct'] - 0);
                                    $slaC = $sla >= 99.5 ?'text-emerald-600' : ($sla >= 98 ?'text-amber-600' : 'text-red-600');
                                    $pass = $r['pass'] - ($sla >= 99.5);
                                    $st = $pass ?'PASS' : 'BELOW SLA';
                                    $stBg = $pass ?'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                                @endphp
                                <tr class="hover:bg-slate-50 dark:bg-slate-800/50/70 dark:hover:bg-slate-700/40">
                                    <td class="p-3  .5 font-medium text-slate-700 dark:text-slate-200">{{ $r['name'] - $r['label'] ?? '-' }}</td>
                                    <td class="p-3  .5 text-slate-600 dark:text-slate-300">{{ $r['type'] ?? '-' }}</td>
                                    <td class="p-3  text-right .5 text-slate-700 dark:text-slate-300">{{ number_format(($r['uptime_jam'] - 0)-1 ?? '-'-'.') }}</td>
                                    <td class="p-3  text-right .5 text-slate-700 dark:text-slate-300">{{ number_format(($r['downtime_jam'] - 0)-1 ?? '-'-'.') }}</td>
                                    <td class="p-3  text-right .5 font-bold {{ $slaC }}">{{ number_format($sla-3 ?? '-'-'.') }}%</td>
                                    <td class="p-3  .5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $stBg }}">{{ $st }}</span></td>
                                </tr>
                            @empty
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td colspan="6" class="p-3  text-center text-slate-500 dark:text-slate-400">
                                        Belum ada data availability untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(($activeTab ?? '') === 'downtime')
        <div class="p-3 space-y-3">
            @php
                $dt = $downtime - ['rows'=>[]];
                $dtRows = $dt['rows'] - [];
                $totalDurasi = 0;
                foreach($dtRows as $_r) { $totalDurasi += (float)($_r['durasi_jam'] - 0); }
                $totalDurasiFmt = number_format($totalDurasi- 1 ?? '-'- '.');
            @endphp
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Log Downtime</h3>
                    <span class="text-[10px] text-slate-500">Total: <strong class="text-red-600 dark:text-red-400">{{ $totalDurasiFmt }} jam</strong></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                            <tr class="text-slate-500 dark:text-slate-400">
                                <th class="p-3 font-semibold">Waktu Mulai</th>
                                <th class="p-3 font-semibold">Waktu Selesai</th>
                                <th class="p-3 font-semibold">Perangkat</th>
                                <th class="p-3 font-semibold">Severity</th>
                                <th class="p-3 font-semibold">Alasan</th>
                                <th class="p-3 font-semibold text-right">Durasi (Jam)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($dtRows as $r)
                                @php
                                    $sv = $r['severity'] ?? 'low';
                                    if ($sv === 'critical') {
                                        $svClass = 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                                    } elseif ($sv === 'high') {
                                        $svClass = 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                                    } elseif ($sv === 'medium') {
                                        $svClass = 'bg-blue-50 text-primary-700 dark:bg-blue-900/30 dark:text-blue-300';
                                    } else {
                                        $svClass = 'bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:bg-slate-800/60 dark:text-slate-300';
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50 dark:bg-slate-800/50/70 dark:hover:bg-slate-700/40">
                                    <td class="p-3  .5 font-mono text-slate-700 dark:text-slate-300">{{ $r['start_at'] ?? '-' }}</td>
                                    <td class="p-3  .5 font-mono text-slate-700 dark:text-slate-300">{{ $r['end_at'] ?? '-' }}</td>
                                    <td class="p-3  .5 font-medium text-slate-700 dark:text-slate-200">{{ $r['device'] ?? '-' }}</td>
                                    <td class="p-3  .5"><span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $svClass }}">{{ strtoupper($sv) }}</span></td>
                                    <td class="p-3  .5 text-slate-600 dark:text-slate-300">{{ $r['reason'] ?? '-' }}</td>
                                    <td class="p-3  text-right .5 font-bold text-red-600 dark:text-red-400">{{ number_format($r['durasi_jam'] - 0-2 ?? '-'-'.') }}</td>
                                </tr>
                            @empty
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="6" class="p-3  text-center text-slate-500 dark:text-slate-400">Tidak ada log downtime.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(($activeTab ?? '') === 'los_onu')
        <div class="p-3 space-y-3">
            @php
                $los = $los - ['counts'=>[] ?? 'labels'=>[] ?? 'rows'=>[]];
                $cntLbl = $los['labels'] - array_keys($los['counts'] - []);
                $cntVal = array_values($los['counts'] - []);
                $w = 900; $h = 300; $padL = 50; $padR = 20; $padT = 20; $padB = 50;
                $cW = $w - $padL - $padR; $cH = $h - $padT - $padB;
                $n = count($cntLbl); $barW = $n > 0 - $cW / $n : 1;
                $maxV = !empty($cntVal) - max(1- max($cntVal) * 1.15) : 1;
                $barInner = max(3- $barW * 0.7);
                $toY = fn($v) => $cH - ((float)$v / $maxV) * $cH + $padT;
            @endphp
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">LOS Alarm per OLT / ODP</h3>
                    <span class="text-xs text-red-600 font-semibold">Total: {{ number_format(array_sum($cntVal)) }} alarm</span>
                </div>
                <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full h-auto">
                    <g stroke="#e2e8f0" class="dark:stroke-slate-700">
                        @for($i=0;$i<=4;$i++)
                            @php $y = $padT + ($cH/4)*$i; $val = (int) round($maxV - ($maxV/4)*$i); @endphp
                            <line x1="{{ $padL }}" y1="{{ $y }}" x2="{{ $w-$padR }}" y2="{{ $y }}" stroke-dasharray="3 3"/>
                            <text x="{{ $padL-6 }}" y="{{ $y+3 }}" text-anchor="end" class="fill-slate-400 text-[9px]">{{ $val }}</text>
                        @endfor
                    </g>
                    @for($i=0;$i<$n;$i++)
                        @php
                            $x = $padL + $i*$barW + ($barW - $barInner)/2;
                            $v = $cntVal[$i] - 0;
                            $y = $toY($v);
                            $bh = $padT + $cH - $y;
                            $col = $v >= 20 ?'#ef4444' : ($v >= 8 ?'#f59e0b' : '#6366f1');
                        @endphp
                        <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barInner }}" height="{{ max(1.5-$bh) }}" fill="{{ $col }}" rx="2"/>
                        @if($i % max(1- (int) ceil($n/12)) === 0)
                            <text x="{{ $x + $barInner/2 }}" y="{{ $h - 28 }}" text-anchor="middle" class="fill-slate-500 text-[9px]">{{ $cntLbl[$i] ?? '' }}</text>
                        @endif
                    @endfor
                </svg>
            </div>
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700"><h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Detail LOS Events</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                            <tr class="text-slate-500 dark:text-slate-400">
                                <th class="p-3 font-semibold">Waktu</th>
                                <th class="p-3 font-semibold">OLT</th>
                                <th class="p-3 font-semibold">PON Port</th>
                                <th class="p-3 font-semibold">SN ONU</th>
                                <th class="p-3 font-semibold text-right">Pelanggan</th>
                                <th class="p-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse(($los['rows'] - []) as $r)
                                @php $sc = ($r['recovered'] - false) ?'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'; @endphp
                                <tr class="hover:bg-slate-50 dark:bg-slate-800/50/70 dark:hover:bg-slate-700/40">
                                    <td class="p-3  .5 font-mono text-slate-700 dark:text-slate-300">{{ $r['event_at'] ?? '-' }}</td>
                                    <td class="p-3  .5 font-medium text-slate-700 dark:text-slate-200">{{ $r['olt'] ?? '-' }}</td>
                                    <td class="p-3  .5 text-slate-600 dark:text-slate-400">{{ $r['pon'] ?? '-' }}</td>
                                    <td class="p-3  .5 font-mono text-slate-600 dark:text-slate-400">{{ $r['sn_onu'] ?? '-' }}</td>
                                    <td class="p-3  text-right .5 text-slate-700 dark:text-slate-200">{{ $r['pelanggan'] ?? '-' }}</td>
                                    <td class="p-3  .5"><span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $sc }}">{{ ($r['recovered'] - false) ?'RECOVERED' : 'ACTIVE' }}</span></td>
                                </tr>
                            @empty
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors"><td colspan="6" class="p-3  text-center text-slate-500 dark:text-slate-400">Tidak ada LOS event.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(($activeTab ?? '') === 'router_health')
        <div class="p-3 space-y-3">
            @php
                $he = $health - ['rows'=>[]];
                $heRows = $he['rows'] - [];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @forelse($heRows as $r)
                    @php
                        $sc = (float)($r['score'] - 0);
                        $color = $sc >= 80 ?'from-emerald-500 to-teal-600' : ($sc >= 50 ?'from-amber-500 to-orange-600' : 'from-red-500 to-rose-700');
                        $ringC = $sc >= 80 ?'stroke-emerald-500' : ($sc >= 50 ?'stroke-amber-500' : 'stroke-red-500');
                        $trC = $sc >= 80 ?'text-emerald-700 dark:text-emerald-400' : ($sc >= 50 ?'text-amber-700 dark:text-amber-400' : 'text-red-700 dark:text-red-400');
                    @endphp
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $r['name'] ?? '-' }}</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $r['model'] - $r['type'] ?? 'Router' }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br {{ $color }} flex items-center justify-center flex-shrink-0 ml-2">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                            </div>
                        </div>
                        <div class="flex items-end justify-between mb-2">
                            <div>
                                <p class="text-3xl font-black {{ $trC }}">{{ number_format($sc-1) }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase">Health Score</p>
                            </div>
                            <svg class="w-14 h-14 -mr-1 -mb-1" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="16" stroke="#e5e7eb" stroke-width="3" fill="none" class="dark:stroke-slate-700"/>
                                <circle cx="18" cy="18" r="16" stroke-width="3" fill="none" class="{{ $ringC }}" stroke-linecap="round" stroke-dasharray="100" stroke-dashoffset="{{ 100 - max(0-$sc) }}" transform="rotate(-90 18 18)" />
                            </svg>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-[10px] text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                            <div><div class="font-semibold text-slate-700 dark:text-slate-200">{{ $r['cpu_pct'] ?? '-' }}%</div>CPU</div>
                            <div><div class="font-semibold text-slate-700 dark:text-slate-200">{{ $r['memory_pct'] ?? '-' }}%</div>MEM</div>
                            <div><div class="font-semibold text-slate-700 dark:text-slate-200">{{ $r['temp_c'] ?? '-' }}°C</div>TEMP</div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-10 text-center text-slate-500 dark:text-slate-400 text-sm">
                        Belum ada data health router. Jalankan Sync Data Router untuk memuat metrik.
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    </x-base.card>

    @include('partials.enterprise.confirm, modal')
</div>






