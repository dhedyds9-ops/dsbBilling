@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">inventory_2</span>
    <span class="text-lg">Network Assets</span>
@endsection

<div class="space-y-5 pb-10">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-end gap-4">
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </button>
            <a href="#" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Asset
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Assets</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2m-2-4h-14"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">In Use</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-emerald-400 mt-1">{{ $stats['in_use'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Available</p>
                    <p class="text-2xl font-bold text-primary-600 mt-1">{{ $stats['available'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Maintenance</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['maintenance'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Retired</p>
                    <p class="text-2xl font-bold text-slate-600 dark:text-slate-400 mt-1">{{ $stats['retired'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
    </div>

    {{-- Search & Filters --}}
    <x-base.card>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live="filters.search" placeholder="Cari nama aset atau serial number..." class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
            </div>
            <button wire:click="$toggle('showFilters')" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
        </div>
        @if($showFilters ?? false)
            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                        <select wire:model.live="filters.category" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="all" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Semua Kategori</option>
                            <option value="OLT" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">OLT</option>
                            <option value="ONU" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">ONU</option>
                            <option value="Cables" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Kabel</option>
                            <option value="Splitter" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Splitter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select wire:model.live="filters.status" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="all" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Semua Status</option>
                            <option value="in_use" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">In Use</option>
                            <option value="available" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Available</option>
                            <option value="maintenance" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Maintenance</option>
                            <option value="retired" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Retired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gudang</label>
                        <select wire:model.live="filters.warehouse" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="all" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Semua Gudang</option>
                            <option value="Warehouse A" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Warehouse A</option>
                            <option value="Warehouse B" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Warehouse B</option>
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
                    <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Nama Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Serial Number</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Gudang</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tanggal Pembelian</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Garansi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $asset['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $asset['serial_number'] }}</td>
                            <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $asset['category'] }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    @if($asset['status'] === 'in_use') bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-emerald-400
                                    @elseif($asset['status'] === 'available') bg-primary-100 text-primary-600
                                    @elseif($asset['status'] === 'maintenance') bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @endif
                                ">
                                    {{ ucfirst($asset['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $asset['warehouse'] }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $asset['location'] }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $asset['purchase_date']->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $asset['warranty_expires']->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="#" class="p-2 text-slate-500 dark:text-slate-400 hover:text-primary-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="#" class="p-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2m-2-4h-14"/>
                                </svg>
                                <p class="text-lg">Belum ada data asset</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-base.card>
</div>


