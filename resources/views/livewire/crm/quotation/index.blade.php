<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Quotation Management</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola semua quotation penawaran harga</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="export" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </button>
            <a href="{{ route('crm.quotations.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Quotation
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <x-base.card>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live="search" placeholder="Cari nama pelanggan..." class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
            </div>
            <button wire:click="$toggle('showFilters')" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
            <button wire:click="resetFilters" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900 dark:text-slate-100">
                Reset
            </button>
        </div>
        @if($showFilters)
            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select wire:model.live="filters.status" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Dikirim</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                </div>
            </div>
        @endif
    </x-base.card>

    {{-- Data Table --}}
    <x-base.card :padding="false">
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Nama Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($quotations as $quotation)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $quotation->customer_name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">Rp {{ number_format($quotation->total ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    @if($quotation->status === 'draft') bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @elseif($quotation->status === 'sent') bg-blue-100 dark:bg-blue-900/50 text-blue-600
                                    @elseif($quotation->status === 'accepted') bg-green-100 dark:bg-green-900/50 text-green-600
                                    @else bg-red-100 dark:bg-red-900/50 text-red-600
                                    @endif
                                ">
                                    {{ ucfirst($quotation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $quotation->created_at?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('crm.quotations.show', $quotation->id) }}" class="p-2 text-slate-500 dark:text-slate-400 hover:text-primary-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('crm.quotations.edit', $quotation->id) }}" class="p-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    <button wire:click="delete({{ $quotation->id }})" wire:confirm="Yakin ingin menghapus quotation ini?" class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $quotations->links() }}
            </div>
        @endif
    </x-base.card>
</div>
