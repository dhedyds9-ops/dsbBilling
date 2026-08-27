<div wire:key="keuangan-topup-reseller-{{ now()->timestamp }}">
    @include('partials.enterprise.list-toolbar', [
        'title' => 'Topup Reseller',
        'primaryLabel' => null,
        'primaryAction' => null,
        'actions' => [
            ['label' => 'Export CSV', 'icon' => 'download', 'action' => 'exportCsv()'],
        ],
        'searchPlaceholder' => 'Cari reseller / kode referensi...',
        'showFiltersToggle' => true,
    ])

    @include('partials.enterprise.summary-cards', [
        'items' => [
            ['label' => 'Total Topup Bulan Ini', 'value' => 'Rp ' . number_format($summary['monthly_total'] ?? 0, 0, ',', '.'), 'color' => 'blue', 'icon' => 'dollar-sign'],
            ['label' => 'Pending Approval', 'value' => number_format($summary['pending_count'] ?? 0, 0, ',', '.'), 'color' => 'amber', 'icon' => 'clock'],
            ['label' => 'Disetujui', 'value' => number_format($summary['approved_count'] ?? 0, 0, ',', '.'), 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'Ditolak', 'value' => number_format($summary['rejected_count'] ?? 0, 0, ',', '.'), 'color' => 'red', 'icon' => 'alert-triangle'],
            ['label' => 'Saldo Aktif Reseller', 'value' => 'Rp ' . number_format($summary['total_reseller_balance'] ?? 0, 0, ',', '.'), 'color' => 'purple', 'icon' => 'credit-card'],
        ],
    ])

    @if ($showFilters)
        @include('partials.enterprise.filters', ['filters' => $filterConfig])
    @endif

    @include('partials.enterprise.bulk-bar', ['bulkActions' => $bulkActions])

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
        <div class="bg-white dark:bg-slate-800 overflow-x-auto border-b border-slate-200 dark:border-slate-700">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/40 border-y border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-3 py-2 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600">
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('created_at')">
                            Tgl
                            @if ($sortField === 'created_at')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Reseller</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('reference_code')">
                            Kode Referensi
                            @if ($sortField === 'reference_code')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                        </th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('amount')">
                            Jumlah
                            @if ($sortField === 'amount')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Metode</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Bukti TF</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Diajukan Oleh</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Diverifikasi Oleh</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($rows as $row)
                        @php
                            $statusClass = match(strtolower($row->status ?? '')) {
                                'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                'approved', 'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'rejected', 'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                            };
                            $statusLabel = match(strtolower($row->status ?? '')) {
                                'pending' => 'Pending Approval',
                                'approved', 'success' => 'Disetujui',
                                'rejected', 'failed' => 'Ditolak',
                                default => ucfirst($row->status ?? '-'),
                            };
                            $resellerName = $row->reseller->name ?? $row->reseller_name ?? '-';
                            $submitterName = $row->submittedBy?->name ?? $row->submitted_by_name ?? '-';
                            $verifierName = $row->verifiedBy?->name ?? '-';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-3 py-2">
                                <input type="checkbox" wire:model.live="selected" value="{{ (string) $row->id }}" class="rounded border-slate-300 dark:border-slate-600">
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300">{{ $row->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-100">{{ $resellerName }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $row->reference_code ?? $row->reference_number ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format((float) ($row->amount ?? 0), 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $row->method ?? '-')) }}</td>
                            <td class="px-3 py-2">
                                @if (!empty($row->proof_file))
                                    <a href="{{ $row->proof_file }}" target="_blank" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 underline text-xs">Preview</a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 text-xs">{{ $submitterName }}</td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 text-xs">{{ $verifierName }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                <div class="inline-flex gap-1">
                                    @if (strtolower($row->status ?? '') === 'pending')
                                        <button wire:click="approve({{ $row->id }})" class="px-2 py-1 text-[11px] rounded bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Setujui</button>
                                        <button wire:click="confirmReject({{ $row->id }})" class="px-2 py-1 text-[11px] rounded bg-red-600 hover:bg-red-700 text-white font-medium">Tolak</button>
                                    @endif
                                    <button wire:click="previewProof({{ $row->id }})" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Bukti</button>
                                    <button wire:click="viewDetail({{ $row->id }})" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Detail</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                Tidak ada data topup reseller.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rows->hasPages())
            <div class="px-3 py-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-between gap-2 flex-wrap">
                <div class="text-xs text-slate-600 dark:text-slate-400">
                    Menampilkan {{ $rows->firstItem() }} - {{ $rows->lastItem() }} dari {{ $rows->total() }} data
                </div>
                <div>{{ $rows->links() }}</div>
                <div class="flex items-center gap-1">
                    <select wire:model.live="perPage" class="text-xs rounded-md border border-slate-200 dark:border-slate-700 py-1 px-2 bg-white dark:bg-slate-700 dark:text-slate-200">
                        <option value="10">10 / hal</option>
                        <option value="25">25 / hal</option>
                        <option value="50">50 / hal</option>
                        <option value="100">100 / hal</option>
                    </select>
                </div>
            </div>
        @endif
    @endif

    @include('partials.enterprise.confirm-modal')
</div>
