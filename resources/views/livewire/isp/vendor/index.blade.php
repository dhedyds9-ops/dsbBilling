@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">business</span>
    <span class="text-lg">Data Vendor</span>
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
    @if(session('warning'))
        <div class="flex items-center gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl text-amber-700 dark:text-amber-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">warning</span>
            {{ session('warning') }}
        </div>
    @endif
    @if(session('info'))
        <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl text-blue-700 dark:text-blue-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
            {{ session('info') }}
        </div>
    @endif

    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto shrink-0">
            <a href="{{ route('isp.vendors.create') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                <span>Tambah Vendor</span>
            </a>
            <button type="button" wire:click="export" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span>
                <span>Export</span>
            </button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full lg:w-auto">
            <div class="relative w-full sm:w-60">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari kode, nama vendor..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                <select wire:model.live="filters.status"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
                <select wire:model.live="perPage"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                </select>
                <label class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-2 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 cursor-pointer whitespace-nowrap">
                    <input type="checkbox" wire:model.live="showTrashed" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-800" />
                    <span>Sampah</span>
                </label>
                @if($search || ($filters['status'] ?? '') || $showTrashed)
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

    {{-- BULK ACTION BANNER --}}
    @if(!empty($selectedVendors))
        <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-xl">
            <span class="text-sm text-indigo-800 dark:text-indigo-300 font-semibold">{{ count($selectedVendors) }} Vendor dipilih</span>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="bulkActivate" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm cursor-pointer">Aktifkan</button>
                <button type="button" wire:click="bulkDeactivate" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm cursor-pointer">Nonaktifkan</button>
                <button type="button" wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus Vendor terpilih?" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm cursor-pointer">Hapus Terpilih</button>
            </div>
        </div>
    @endif

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80">
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="px-4 py-3 text-left w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-900" />
                        </th>
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Kode & Nama Vendor</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Kontak</th>
                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    @forelse($vendors as $vendor)
                        <tr wire:key="vendor-{{ $vendor->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors @if($vendor->trashed()) bg-red-50/60 dark:bg-red-900/20 @endif">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedVendors" value="{{ $vendor->id }}" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-900" @if($vendor->trashed()) disabled @endif />
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if(!$vendor->trashed())
                                        <a href="{{ route('isp.vendors.show', $vendor->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 transition-colors" title="Detail">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                        </a>
                                        <a href="{{ route('isp.vendors.edit', $vendor->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 dark:text-emerald-400 transition-colors" title="Edit">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                        </a>
                                        <button type="button" wire:click="delete({{ $vendor->id }})" wire:confirm="Yakin ingin menghapus Vendor ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 transition-colors cursor-pointer" title="Hapus">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                        </button>
                                    @else
                                        <button type="button" wire:click="restore({{ $vendor->id }})" wire:confirm="Pulihkan Vendor ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 dark:text-emerald-400 transition-colors cursor-pointer" title="Pulihkan">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">restore</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($vendor->trashed())
                                    <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Dihapus
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full {{ $vendor->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $vendor->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $vendor->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <a href="{{ route('isp.vendors.show', $vendor->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $vendor->name }}
                                    </a>
                                </div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ $vendor->code }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($vendor->contact_person || $vendor->phone || $vendor->email)
                                    <div class="text-sm text-slate-700 dark:text-slate-200">{{ $vendor->contact_person ?: ($vendor->email ?: '-') }}</div>
                                    @if($vendor->phone)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $vendor->phone }}</div>
                                    @endif
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-sm text-slate-600 dark:text-slate-400 truncate max-w-[280px]" title="{{ $vendor->description }}">{{ $vendor->description ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700/60 rounded-full flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:28px">business</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">Belum ada data Vendor</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Silakan tambahkan vendor perangkat jaringan pertama Anda.</p>
                                    <a href="{{ route('isp.vendors.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                                        <span>Tambah Vendor</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($vendors, 'hasPages') && $vendors->hasPages())
            <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>
</div>
