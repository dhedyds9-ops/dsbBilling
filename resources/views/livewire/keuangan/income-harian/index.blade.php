<div wire:key="keuangan-income-harian-{{ now()->timestamp }}">
    @include('partials.enterprise.list-toolbar', [
        'title' => 'Income Harian',
        'primaryLabel' => null,
        'primaryAction' => null,
        'actions' => [
            ['label' => 'Export CSV', 'icon' => 'download', 'action' => 'exportCsv()'],
        ],
        'searchPlaceholder' => 'Cari...',
        'showFiltersToggle' => true,
    ])

    @include('partials.enterprise.summary-cards', [
        'items' => [
            ['label' => 'Pendapatan Hari Ini', 'value' => 'Rp ' . number_format($summary['today_total'] ?? 0, 0, ',', '.'), 'color' => 'green', 'icon' => 'dollar-sign'],
            ['label' => 'Jumlah Payment Hari Ini', 'value' => number_format($summary['today_count'] ?? 0, 0, ',', '.'), 'color' => 'blue', 'icon' => 'credit-card'],
            ['label' => 'Pelanggan Bayar Hari Ini', 'value' => number_format($summary['today_customers'] ?? 0, 0, ',', '.'), 'color' => 'purple', 'icon' => 'users'],
            ['label' => 'Avg Payment', 'value' => 'Rp ' . number_format($summary['today_avg'] ?? 0, 0, ',', '.'), 'color' => 'cyan', 'icon' => 'activity'],
            ['label' => 'YTD Pendapatan', 'value' => 'Rp ' . number_format($summary['ytd'] ?? 0, 0, ',', '.'), 'color' => 'amber', 'icon' => 'trending-up'],
        ],
    ])

    @if ($showFilters)
        @include('partials.enterprise.filters', ['filters' => $filterConfig])
    @endif

    @if ($errorMessage)
        <div class="px-3 py-2 bg-red-50 dark:bg-red-900/30 border-b border-red-100 dark:border-red-800 text-sm text-red-700 dark:text-red-200">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($loading)
        <div class="p-8 flex items-center justify-center text-slate-500 dark:text-slate-400">
            <svg class="w-6 h-6 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Memuat data...
        </div>
    @else
        <div class="p-3 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Tren Pendapatan 30 Hari Terakhir</div>
                    <div class="flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#3b82f6"></span> PPPoE</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#10b981"></span> Hotspot</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#f59e0b"></span> Voucher</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#8b5cf6"></span> Lainnya</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-0.5" style="background:#ef4444"></span> Total</span>
                    </div>
                </div>
                @php
                    $labels = $chartData['labels'] ?? [];
                    $seriesPppoe = $chartData['pppoe'] ?? [];
                    $seriesHotspot = $chartData['hotspot'] ?? [];
                    $seriesVoucher = $chartData['voucher'] ?? [];
                    $seriesOther = $chartData['other'] ?? [];
                    $seriesTotal = $chartData['total'] ?? [];
                    $maxVal = max(1, ...array_merge($seriesPppoe, $seriesHotspot, $seriesVoucher, $seriesOther, $seriesTotal));
                    $chartW = 100;
                    $chartH = 40;
                    $count = max(1, count($labels));
                    $stepX = $count > 1 ? ($chartW / ($count - 1)) : 0;
                    $toY = fn($v) => $chartH - ($v / $maxVal) * $chartH;
                    $toX = fn($i) => $stepX * $i;
                    $buildLine = function ($arr) use ($toX, $toY, $count, $stepX) {
                        $pts = [];
                        for ($i = 0; $i < $count; $i++) {
                            $val = $arr[$i] ?? 0;
                            $pts[] = number_format($toX($i), 2) . ',' . number_format($toY($val), 2);
                        }
                        return implode(' ', $pts);
                    };
                    $buildArea = function ($arr) use ($toX, $toY, $count, $chartH, $stepX) {
                        if ($count === 0) return '';
                        $pts = [];
                        $pts[] = number_format($toX(0), 2) . ',' . $chartH;
                        for ($i = 0; $i < $count; $i++) {
                            $val = $arr[$i] ?? 0;
                            $pts[] = number_format($toX($i), 2) . ',' . number_format($toY($val), 2);
                        }
                        $pts[] = number_format($toX($count - 1), 2) . ',' . $chartH;
                        return implode(' ', $pts);
                    };
                @endphp
                <div class="w-full overflow-hidden">
                    <svg viewBox="0 -2 {{ $chartW + 2 }} {{ $chartH + 8 }}" preserveAspectRatio="none" class="w-full h-40 lg:h-56">
                        <defs>
                            <linearGradient id="gradT" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#ef4444" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#ef4444" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        @for ($g = 0; $g < 5; $g++)
                            @php $gy = $chartH * ($g / 4); @endphp
                            <line x1="0" y1="{{ number_format($gy, 2) }}" x2="{{ $chartW }}" y2="{{ number_format($gy, 2) }}" stroke="#e2e8f0" stroke-width="0.15" stroke-dasharray="0.5,0.5"/>
                        @endfor
                        <polygon points="{{ $buildArea($seriesPppoe) }}" fill="#3b82f6" fill-opacity="0.08"/>
                        <polygon points="{{ $buildArea($seriesHotspot) }}" fill="#10b981" fill-opacity="0.08"/>
                        <polygon points="{{ $buildArea($seriesVoucher) }}" fill="#f59e0b" fill-opacity="0.08"/>
                        <polygon points="{{ $buildArea($seriesOther) }}" fill="#8b5cf6" fill-opacity="0.08"/>
                        <polygon points="{{ $buildArea($seriesTotal) }}" fill="url(#gradT)"/>
                        <polyline points="{{ $buildLine($seriesPppoe) }}" fill="none" stroke="#3b82f6" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="{{ $buildLine($seriesHotspot) }}" fill="none" stroke="#10b981" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="{{ $buildLine($seriesVoucher) }}" fill="none" stroke="#f59e0b" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="{{ $buildLine($seriesOther) }}" fill="none" stroke="#8b5cf6" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="{{ $buildLine($seriesTotal) }}" fill="none" stroke="#ef4444" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
                        @if ($count > 0)
                            @for ($i = 0; $i < $count; $i += max(1, (int) ceil($count / 10)))
                                <text x="{{ number_format($toX($i), 2) }}" y="{{ $chartH + 5 }}" text-anchor="middle" font-size="2.2" fill="#64748b">{{ $labels[$i] ?? '' }}</text>
                            @endfor
                        @endif
                    </svg>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-900/30">
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Top 10 Customer</div>
                        <div class="space-y-1.5 max-h-52 overflow-y-auto">
                            @forelse ($topCustomers as $i => $c)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-slate-400 text-[10px] w-4">{{ $i+1 }}.</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ $c['name'] }}</span>
                                    </div>
                                    <span class="text-slate-600 dark:text-slate-400 whitespace-nowrap">Rp {{ number_format($c['total_spent'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="text-[11px] text-slate-400 italic">Belum ada data</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-900/30">
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Top 10 Sales</div>
                        <div class="space-y-1.5 max-h-52 overflow-y-auto">
                            @forelse ($topSales as $i => $s)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-slate-400 text-[10px] w-4">{{ $i+1 }}.</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ $s['name'] }}</span>
                                    </div>
                                    <span class="text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $s['customer_count'] }} customer</span>
                                </div>
                            @empty
                                <div class="text-[11px] text-slate-400 italic">Belum ada data</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-900/30">
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Payment Method</div>
                        <div class="space-y-2">
                            @php
                                $pmColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#64748b'];
                                $pmTotal = array_sum(array_column($paymentMethods, 'total'));
                            @endphp
                            @if (count($paymentMethods) > 0)
                                <div class="flex h-3 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700">
                                    @foreach ($paymentMethods as $idx => $pm)
                                        @php $w = $pmTotal > 0 ? (($pm['total'] / $pmTotal) * 100) : 0; @endphp
                                        @if ($w > 0)
                                            <div style="width:{{ $w }}%;background:{{ $pmColors[$idx % count($pmColors)] }}"></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                @forelse ($paymentMethods as $idx => $pm)
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-sm" style="background:{{ $pmColors[$idx % count($pmColors)] }}"></span>
                                            <span class="text-slate-700 dark:text-slate-300">{{ $pm['label'] }}</span>
                                        </div>
                                        <span class="text-slate-600 dark:text-slate-400">{{ $pm['percent'] }}%</span>
                                    </div>
                                @empty
                                    <div class="text-[11px] text-slate-400 italic">Belum ada data</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-900/30">
                        <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Cash Flow Mini</div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 dark:text-slate-400">Income</span>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">Rp {{ number_format($cashFlow['income'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 dark:text-slate-400">Expense</span>
                                <span class="font-semibold text-red-700 dark:text-red-400">Rp {{ number_format($cashFlow['expense'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-px bg-slate-200 dark:bg-slate-700 my-1"></div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-700 dark:text-slate-300">NET</span>
                                <span class="font-bold {{ ($cashFlow['net'] ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                    Rp {{ number_format($cashFlow['net'] ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="mt-3">
                                @php
                                    $cfIncome = $cashFlow['income'] ?? 0;
                                    $cfExpense = $cashFlow['expense'] ?? 0;
                                    $cfMax = max(1, $cfIncome, $cfExpense);
                                    $hI = ($cfIncome / $cfMax) * 100;
                                    $hE = ($cfExpense / $cfMax) * 100;
                                @endphp
                                <div class="flex items-end gap-3 h-16 justify-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-8 bg-emerald-500 rounded-t" style="height:{{ $hI }}%"></div>
                                        <span class="text-[10px] text-slate-500">IN</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-8 bg-red-500 rounded-t" style="height:{{ $hE }}%"></div>
                                        <span class="text-[10px] text-slate-500">OUT</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 overflow-x-auto border-b border-slate-200 dark:border-slate-700">
            <div class="px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-700/40 border-y border-slate-200 dark:border-slate-700">Ringkasan Harian</div>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/30 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Jumlah Payment</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Total IDR</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Avg IDR</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Jumlah Customer</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Method Top</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($rows as $row)
                        @php
                            $r = is_array($row) ? $row : $row->toArray();
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-3 py-2 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">{{ $r['date_label'] ?? $r['date'] ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300">{{ number_format($r['payment_count'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-emerald-700 dark:text-emerald-400">Rp {{ number_format($r['total_idr'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300">Rp {{ number_format($r['avg_idr'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300">{{ number_format($r['customer_count'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300">{{ $r['method_top'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Tidak ada data pendapatan untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @include('partials.enterprise.confirm-modal')
</div>
