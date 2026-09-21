<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Network Assets</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Manajemen inventaris perangkat jaringan dsBilling</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="$toggle('showFilters')" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors flex items-center gap-2 text-slate-900 dark:text-slate-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
            <button wire:click="create" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Asset
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <x-base.card class="bg-gradient-to-br from-indigo-500 to-indigo-600 border-none text-white">
            <div class="text-indigo-100 text-sm font-medium">Total Asset</div>
            <div class="mt-2 flex items-baseline gap-2">
                <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
            </div>
        </x-base.card>
        <x-base.card class="bg-white dark:bg-slate-800">
            <div class="text-slate-500 dark:text-slate-400 text-sm font-medium">In Use</div>
            <div class="mt-2 flex items-baseline gap-2">
                <div class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['in_use'] }}</div>
            </div>
        </x-base.card>
        <x-base.card class="bg-white dark:bg-slate-800">
            <div class="text-slate-500 dark:text-slate-400 text-sm font-medium">Available</div>
            <div class="mt-2 flex items-baseline gap-2">
                <div class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['available'] }}</div>
            </div>
        </x-base.card>
        <x-base.card class="bg-white dark:bg-slate-800">
            <div class="text-slate-500 dark:text-slate-400 text-sm font-medium">Maintenance</div>
            <div class="mt-2 flex items-baseline gap-2">
                <div class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['maintenance'] }}</div>
            </div>
        </x-base.card>
        <x-base.card class="bg-white dark:bg-slate-800">
            <div class="text-slate-500 dark:text-slate-400 text-sm font-medium">Retired</div>
            <div class="mt-2 flex items-baseline gap-2">
                <div class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['retired'] }}</div>
            </div>
        </x-base.card>
    </div>

    {{-- Filters --}}
    @if(isset($showFilters) && $showFilters)
        <x-base.card>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cari</label>
                        <input type="text" wire:model.live.debounce.300ms="filters.search" placeholder="Nama, Kode, atau SN..." class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select wire:model.live="filters.status" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                            <option value="all">Semua Status</option>
                            <option value="in_use">In Use</option>
                            <option value="available">Available</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="retired">Retired</option>
                        </select>
                    </div>
                </div>
            </div>
        </x-base.card>
    @endif

    {{-- Data Table --}}
    <x-base.card :padding="false">
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kode & Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">SN / Mac</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor / Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tanggal Pembelian</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-300">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $asset->name }}</div>
                                <div class="text-xs text-slate-500">{{ $asset->code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-mono">{{ $asset->serial_number ?: '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $asset->mac_address }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">{{ $asset->vendor ? $asset->vendor->name : '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $asset->category_id ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    @if($asset->status === 'in_use') bg-green-100 text-green-600 dark:bg-green-900/50 dark:text-emerald-400
                                    @elseif($asset->status === 'available') bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400
                                    @elseif($asset->status === 'maintenance') bg-yellow-100 text-yellow-600 dark:bg-yellow-900/50 dark:text-yellow-400
                                    @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') : '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="edit('{{ $asset->id }}')" class="p-2 text-slate-500 dark:text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </button>
                                    <button wire:click="delete('{{ $asset->id }}')" wire:confirm="Yakin ingin menghapus aset ini?" class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 mb-2" translate="no" style="font-size:48px">inventory_2</span>
                                <p class="text-lg">Belum ada data asset</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($assets->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                {{ $assets->links() }}
            </div>
        @endif
    </x-base.card>

    {{-- MODAL CREATE / EDIT --}}
    @if($isModalOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('isModalOpen', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <form wire:submit.prevent="save">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100" id="modal-title">
                                {{ $assetId ? 'Edit Aset' : 'Tambah Aset' }}
                            </h3>
                            <button type="button" wire:click="$set('isModalOpen', false)" class="text-slate-400 hover:text-slate-500">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">close</span>
                            </button>
                        </div>
                        
                        @if ($errors->any())
                            <div class="mb-4 flex items-start gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm">
                                <span class="material-symbols-outlined notranslate mt-0.5" translate="no" style="font-size:18px">error</span>
                                <div>
                                    <p class="font-bold mb-1">Peringatan Validasi:</p>
                                    <ul class="list-disc pl-4 space-y-0.5 text-red-600 dark:text-red-400">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Barang <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="code" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-red-500">*</span></label>
                                <select wire:model="status" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                                    <option value="available">Tersedia (Available)</option>
                                    <option value="in_use">Digunakan (In Use)</option>
                                    <option value="maintenance">Perbaikan (Maintenance)</option>
                                    <option value="retired">Afkir (Retired)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Serial Number <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                                <input type="text" wire:model="serial_number" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor Merek <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                                <select wire:model="vendor_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                                    <option value="">-- Pilih Vendor --</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Beli <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                                <input type="date" wire:model="purchase_date" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-100">
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200 dark:border-slate-700">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Data
                        </button>
                        <button type="button" wire:click="$set('isModalOpen', false)" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
