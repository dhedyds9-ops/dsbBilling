<div wire:key="keuangan-pengeluaran-{{ now()->timestamp }}">
    @include('partials.enterprise.list-toolbar', [
        'title' => 'Pengeluaran',
        'primaryLabel' => null,
        'primaryAction' => null,
        'actions' => [
            ['label' => 'Export CSV', 'icon' => 'download', 'action' => 'exportCsv()'],
        ],
        'searchPlaceholder' => 'Cari kode / deskripsi...',
        'showFiltersToggle' => true,
    ])

    @include('partials.enterprise.summary-cards', [
        'items' => [
            ['label' => 'Total Pengeluaran Bulan Ini', 'value' => 'Rp ' . number_format($summary['monthly_total'] ?? 0, 0, ',', '.'), 'color' => 'red', 'icon' => 'dollar-sign'],
            ['label' => 'Butuh Approval', 'value' => number_format($summary['needs_approval'] ?? 0, 0, ',', '.'), 'color' => 'amber', 'icon' => 'clock'],
            ['label' => 'Disetujui', 'value' => number_format($summary['approved'] ?? 0, 0, ',', '.'), 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'Anggaran Sisa', 'value' => 'Rp ' . number_format($summary['budget_remaining'] ?? 0, 0, ',', '.'), 'color' => 'blue', 'icon' => 'credit-card'],
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
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('expense_date')">
                            Tgl
                            @if ($sortField === 'expense_date')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('code')">
                            Kode
                            @if ($sortField === 'code')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Deskripsi</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Kategori</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('amount')">
                            Jumlah
                            @if ($sortField === 'amount')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Attachment</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Pemohon</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Penyetuju</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($rows as $row)
                        @php
                            $statusLabel = match(strtolower($row->status ?? '')) {
                                'draft' => 'Draft',
                                'pending_approval' => 'Butuh Approval',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => ucfirst($row->status ?? '-'),
                            };
                            $statusClass = match(strtolower($row->status ?? '')) {
                                'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                'pending_approval' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                            };
                            $catLabels = [
                                'operasional' => 'Operasional',
                                'pegawai' => 'Pegawai',
                                'isp_tools' => 'ISP/Tools',
                                'marketing' => 'Marketing',
                                'lain' => 'Lainnya',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-3 py-2">
                                <input type="checkbox" wire:model.live="selected" value="{{ (string) $row->id }}" class="rounded border-slate-300 dark:border-slate-600">
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300">{{ $row->expense_date?->format('d/m/Y') ?? $row->created_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-800 dark:text-slate-200">{{ $row->code ?? '-' }}</td>
                            <td class="px-3 py-2 max-w-xs truncate text-slate-700 dark:text-slate-300" title="{{ $row->description ?? '' }}">{{ $row->description ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300">{{ $catLabels[$row->category] ?? ucfirst($row->category ?? '-') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-red-700 dark:text-red-400">Rp {{ number_format((float) ($row->amount ?? 0), 0, ',', '.') }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-3 py-2">
                                @if (!empty($row->attachment_file))
                                    <a href="{{ $row->attachment_file }}" target="_blank" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 underline text-xs">Lihat</a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300 text-xs">{{ $row->requestedBy?->name ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300 text-xs">{{ $row->approvedBy?->name ?? '-' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                <div class="inline-flex gap-1 flex-wrap justify-end">
                                    <button wire:click="edit({{ $row->id }})" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Edit</button>
                                    @if (strtolower($row->status ?? '') === 'pending_approval')
                                        <button wire:click="approve({{ $row->id }})" class="px-2 py-1 text-[11px] rounded bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Setujui</button>
                                        <button wire:click="confirmReject({{ $row->id }})" class="px-2 py-1 text-[11px] rounded bg-red-600 hover:bg-red-700 text-white font-medium">Tolak</button>
                                    @endif
                                    <button wire:click="viewAttachment({{ $row->id }})" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">File</button>
                                    <button wire:click="confirmDelete({{ $row->id }})" class="px-2 py-1 text-[11px] rounded border border-red-200 hover:bg-red-50 text-red-700 dark:border-red-900 dark:hover:bg-red-900/30 dark:text-red-300">Hapus</button>
                                    <button wire:click="printReceipt({{ $row->id }})" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Kwitansi</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Tidak ada data pengeluaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (is_object($rows) && method_exists($rows, 'hasPages') && $rows->hasPages())
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
