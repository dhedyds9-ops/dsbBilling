@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">inventory_2</span>
    <span class="text-lg">Network Assets</span>
@endsection

<div class="space-y-5 pb-10">

    {{-- SESSION FLASH --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <button type="button" wire:click="$set('filters.status','all')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ ($filters['status'] ?? 'all') === 'all' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Asset</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ number_format($stats['total'] ?? 0) }}</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Semua</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">inventory_2</span>
            </div>
        </button>

        <button type="button" wire:click="$set('filters.status','in_use')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ ($filters['status'] ?? 'all') === 'in_use' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-300 dark:hover:border-emerald-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">In Use</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($stats['in_use'] ?? 0) }}</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Digunakan</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">check_circle</span>
            </div>
        </button>

        <button type="button" wire:click="$set('filters.status','available')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ ($filters['status'] ?? 'all') === 'available' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Available</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ number_format($stats['available'] ?? 0) }}</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Tersedia</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">inbox</span>
            </div>
        </button>

        <button type="button" wire:click="$set('filters.status','maintenance')" class="text-left bg-white dark:bg-slate-800 p-4 rounded-xl border {{ ($filters['status'] ?? 'all') === 'maintenance' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300 dark:hover:border-amber-600' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Maintenance</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ number_format($stats['maintenance'] ?? 0) }}</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Perbaikan</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">build</span>
            </div>
        </button>
    </div>

    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto shrink-0">
            <button type="button" wire:click="create" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all whitespace-nowrap cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                <span>Tambah Asset</span>
            </button>
            <button type="button" wire:click="syncInstalledOnus(true)" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap cursor-pointer" title="Sinkronkan data dari instalasi ONU">
                <span class="material-symbols-outlined notranslate text-indigo-500" wire:loading.class="animate-spin" wire:target="syncInstalledOnus" translate="no" style="font-size:18px">sync</span>
                <span wire:loading.remove wire:target="syncInstalledOnus">Sinkronkan ONU</span>
                <span wire:loading wire:target="syncInstalledOnus">Sinkronisasi...</span>
            </button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full lg:w-auto">
            <div class="relative w-full sm:w-60">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="filters.search"
                       placeholder="Cari kode, nama, SN..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                <select wire:model.live="filters.status"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="in_use">In Use</option>
                    <option value="available">Available</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="retired">Retired</option>
                </select>
                <select wire:model.live="perPage"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="15">15 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                </select>
                @if(($filters['search'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
                    <button type="button" wire:click="resetFilters"
                            title="Reset Filter"
                            class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer whitespace-nowrap">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                        <span>Reset</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80">
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Kode & Nama Asset</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">SN / MAC Address</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Vendor</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Tgl Pembelian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    @forelse($assets as $asset)
                        @php
                            $badgeClass = match($asset->status) {
                                'in_use'      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                                'available'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                'maintenance' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                                default       => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                            };
                            $dotClass = match($asset->status) {
                                'in_use'      => 'bg-emerald-500',
                                'available'   => 'bg-blue-500',
                                'maintenance' => 'bg-amber-500',
                                default       => 'bg-slate-400',
                            };
                        @endphp
                        <tr wire:key="asset-{{ $asset->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="edit('{{ $asset->id }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors cursor-pointer" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </button>
                                    <button type="button" wire:click="delete('{{ $asset->id }}')" wire:confirm="Yakin ingin menghapus asset ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 transition-colors cursor-pointer" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $asset->name }}</div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $asset->code }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-mono text-slate-700 dark:text-slate-200">{{ $asset->serial_number ?: '-' }}</div>
                                @if($asset->mac_address)
                                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ $asset->mac_address }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm text-slate-700 dark:text-slate-200">{{ $asset->vendor?->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                {{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700/60 rounded-full flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:28px">inventory_2</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">Belum ada data Asset</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Tambahkan asset baru atau sinkronkan dari perangkat ONU terpasang.</p>
                                    <button type="button" wire:click="create" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                                        <span>Tambah Asset</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($assets, 'hasPages') && $assets->hasPages())
            <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $assets->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL CREATE / EDIT --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('isModalOpen', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="save">
                        <div class="p-5 space-y-5">
                            <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700">
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2" id="modal-title">
                                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no" style="font-size:20px">{{ $assetId ? 'edit' : 'add_circle' }}</span>
                                    {{ $assetId ? 'Edit Network Asset' : 'Tambah Network Asset Baru' }}
                                </h3>
                                <button type="button" wire:click="$set('isModalOpen', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">close</span>
                                </button>
                            </div>

                            @if($errors->any())
                                <div class="flex items-start gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm">
                                    <span class="material-symbols-outlined notranslate mt-0.5" translate="no" style="font-size:18px">error</span>
                                    <div>
                                        <p class="font-bold mb-1">Peringatan Validasi:</p>
                                        <ul class="list-disc pl-4 space-y-0.5 text-red-600 dark:text-red-400">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Asset <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="code" placeholder="Contoh: AST-ONU-001" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Asset <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="name" placeholder="Contoh: Modem ZTE F670L" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-red-500">*</span></label>
                                    <select wire:model="status" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="available">Tersedia (Available)</option>
                                        <option value="in_use">Digunakan (In Use)</option>
                                        <option value="maintenance">Perbaikan (Maintenance)</option>
                                        <option value="retired">Afkir (Retired)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Serial Number <span class="text-slate-400 font-normal text-xs">(Opsional)</span></label>
                                    <input type="text" wire:model="serial_number" placeholder="Masukkan serial number" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor Merek <span class="text-slate-400 font-normal text-xs">(Opsional)</span></label>
                                    <select wire:model="vendor_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="">-- Pilih Vendor --</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Pembelian <span class="text-slate-400 font-normal text-xs">(Opsional)</span></label>
                                    <input type="date" wire:model="purchase_date" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/80 px-5 py-3.5 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="$set('isModalOpen', false)" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors inline-flex items-center justify-center gap-2 cursor-pointer">
                                <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                                <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                                <span wire:loading.remove wire:target="save">Simpan Data</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
