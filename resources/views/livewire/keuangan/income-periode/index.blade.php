<div wire:key="keuangan-income-periode-{{ now()->timestamp }}">
    @include('partials.enterprise.list-toolbar', [
        'title' => 'Income Periode',
        'primaryLabel' => null,
        'primaryAction' => null,
        'actions' => [
            ['label' => 'Export Excel', 'icon' => 'download', 'action' => 'exportExcelAction()'],
            ['label' => 'Export PDF', 'icon' => 'file-text', 'action' => 'exportPdfAction()'],
        ],
        'searchPlaceholder' => 'Cari...',
        'showFiltersToggle' => true,
    ])

    @php
        $pd = $periodData ?? [];
        $growth = $pd['growth_percent'] ?? 0;
        $growthClass = $growth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
        $growthIcon = $growth >= 0 ? 'trending-up' : 'trending-down';
    @endphp
    @include('partials.enterprise.summary-cards', [
        'items' => [
            ['label' => 'Periode Aktif', 'value' => $pd['period_label'] ?? '-', 'color' => 'blue', 'icon' => 'calendar'],
            ['label' => 'Total Pendapatan', 'value' => 'Rp ' . number_format($pd['current_total'] ?? 0, 0, ',', '.'), 'color' => 'green', 'icon' => 'dollar-sign'],
            ['label' => 'Growth (' . (($pd['type'] ?? 'monthly') === 'monthly' ? 'MoM' : 'YoY') . ')', 'value' => '<span class="' . $growthClass . ' font-semibold">' . ($growth >= 0 ? '+' : '') . number_format($growth, 2) . '%</span>', 'color' => $growth >= 0 ? 'green' : 'red', 'icon' => $growthIcon],
            ['label' => 'vs Periode Lalu', 'value' => 'Rp ' . number_format($pd['prev_total'] ?? 0, 0, ',', '.'), 'color' => 'slate', 'icon' => 'activity'],
            ['label' => 'Periode Lalu', 'value' => $pd['prev_period_label'] ?? '-', 'color' => 'purple', 'icon' => 'clock'],
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
                <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Komparasi: {{ $pd['period_label'] ?? '-' }} vs {{ $pd['prev_period_label'] ?? '-' }}
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#3b82f6"></span> Saat Ini</span>
                        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background:#cbd5e1"></span> Periode Lalu</span>
                    </div>
                </div>
                @php
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
                @endphp
                <div class="w-full overflow-hidden">
                    <svg viewBox="0 -2 {{ $cW + 2 }} {{ $cH + 9 }}" preserveAspectRatio="none" class="w-full h-40 lg:h-56">
                        @for ($g = 0; $g < 5; $g++)
                            @php $gy = $cH * ($g / 4); @endphp
                            <line x1="0" y1="{{ number_format($gy, 2) }}" x2="{{ $cW }}" y2="{{ number_format($gy, 2) }}" stroke="#e2e8f0" stroke-width="0.15" stroke-dasharray="0.5,0.5"/>
                        @endfor
                        @if ($n > 0)
                            @for ($i = 0; $i < $n; $i++)
                                @php
                                    $gx = $groupW * $i + $groupW * 0.15;
                                    $cv = $curr[$i] ?? 0;
                                    $pv = $prev[$i] ?? 0;
                                    $ch = $cH - $toYV($cv);
                                    $ph = $cH - $toYV($pv);
                                    $cy1 = $toYV($cv);
                                    $py1 = $toYV($pv);
                                @endphp
                                <rect x="{{ number_format($gx, 3) }}" y="{{ number_format($cy1, 3) }}" width="{{ number_format($barW, 3) }}" height="{{ number_format($ch, 3) }}" fill="#3b82f6" rx="0.4"/>
                                <rect x="{{ number_format($gx + $barW + 0.2, 3) }}" y="{{ number_format($py1, 3) }}" width="{{ number_format($barW, 3) }}" height="{{ number_format($ph, 3) }}" fill="#cbd5e1" rx="0.4"/>
                                @if ($i % max(1, (int) ceil($n/12)) === 0)
                                    <text x="{{ number_format($gx + $groupW * 0.3, 3) }}" y="{{ $cH + 5 }}" text-anchor="middle" font-size="2.2" fill="#64748b">{{ $labels[$i] ?? '' }}</text>
                                @endif
                            @endfor
                        @endif
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
                    @forelse ($rows as $row)
                        @php
                            $r = is_array($row) ? $row : $row->toArray();
                            $gr = $r['growth'] ?? 0;
                            $grClass = $gr >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-3 py-2 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">{{ $r['period'] ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300">{{ number_format($r['invoice_count'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300">{{ number_format($r['payment_count'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-emerald-700 dark:text-emerald-400">Rp {{ number_format($r['total'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold {{ $grClass }}">
                                {{ $gr >= 0 ? '+' : '' }}{{ number_format($gr, 2) }}%
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300">{{ $r['top_package'] ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                <div class="inline-flex gap-1">
                                    <button wire:click="exportRowExcel('{{ $r['period'] ?? '' }}')" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">
                                        Excel
                                    </button>
                                    <button wire:click="exportRowPdf('{{ $r['period'] ?? '' }}')" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">
                                        PDF
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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
