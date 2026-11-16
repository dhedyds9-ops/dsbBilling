@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">handyman</span>
    <span class="text-lg">Aset & Alat Kerja (Tools & Equipment)</span>
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
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <button type="button" wire:click="$set('filters.type','all')" class="text-left bg-white dark:bg-slate-800 p-3.5 rounded-xl border {{ ($filters['type'] ?? 'all') === 'all' && ($filters['status'] ?? 'all') === 'all' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Aset</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ number_format($stats['total'] ?? 0) }}</span>
                    <span class="text-xs text-slate-400">Unit</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">inventory_2</span>
            </div>
        </button>

        <button type="button" wire:click="$set('filters.type','tool')" class="text-left bg-white dark:bg-slate-800 p-3.5 rounded-xl border {{ ($filters['type'] ?? 'all') === 'tool' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Alat Kerja</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ number_format($stats['tools'] ?? 0) }}</span>
                    <span class="text-xs text-slate-400">Tools</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">construction</span>
            </div>
        </button>

        <button type="button" wire:click="$set('filters.status','in_use')" class="text-left bg-white dark:bg-slate-800 p-3.5 rounded-xl border {{ ($filters['status'] ?? 'all') === 'in_use' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-300' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Di Lapangan</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($stats['in_use'] ?? 0) }}</span>
                    <span class="text-xs text-slate-400">In Use</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">engineering</span>
            </div>
        </button>

        <button type="button" wire:click="$set('filters.status','available')" class="text-left bg-white dark:bg-slate-800 p-3.5 rounded-xl border {{ ($filters['status'] ?? 'all') === 'available' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300' }} shadow-sm transition-all flex items-center justify-between cursor-pointer">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Di Gudang</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ number_format($stats['available'] ?? 0) }}</span>
                    <span class="text-xs text-slate-400">Ready</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">warehouse</span>
            </div>
        </button>

        <button type="button" wire:click="$set('filters.status','maintenance')" class="text-left bg-white dark:bg-slate-800 p-3.5 rounded-xl border {{ ($filters['status'] ?? 'all') === 'maintenance' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-rose-300' }} shadow-sm transition-all flex items-center justify-between cursor-pointer col-span-2 sm:col-span-1">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Perbaikan</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ number_format($stats['maintenance'] ?? 0) }}</span>
                    <span class="text-xs text-slate-400">Servis</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">build</span>
            </div>
        </button>
    </div>

    {{-- TOOLBAR & FILTER --}}
    <div class="space-y-3 bg-white dark:bg-slate-800 p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="create" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add_circle</span>
                    <span>Tambah Alat / Aset</span>
                </button>
                <button type="button" wire:click="syncInstalledOnus(true)" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg transition-all cursor-pointer" title="Sinkronkan data dari instalasi ONU">
                    <span class="material-symbols-outlined notranslate text-indigo-500" wire:loading.class="animate-spin" wire:target="syncInstalledOnus" translate="no" style="font-size:18px">sync</span>
                    <span wire:loading.remove wire:target="syncInstalledOnus">Sinkronkan ONU</span>
                    <span wire:loading wire:target="syncInstalledOnus">Sinkronisasi...</span>
                </button>
            </div>

            {{-- SEARCH --}}
            <div class="relative w-full sm:w-72">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="filters.search"
                       placeholder="Cari kode, nama, SN, atau teknisi..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
        </div>

        {{-- FILTER ROW --}}
        <div class="flex flex-wrap items-center gap-2 pt-0.5">
            {{-- Type Filter Pills --}}
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-900/60 p-1 rounded-lg text-xs font-medium">
                <button type="button" wire:click="$set('filters.type','all')" class="px-2.5 py-1 rounded-md transition-all cursor-pointer {{ ($filters['type'] ?? 'all') === 'all' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">Semua</button>
                <button type="button" wire:click="$set('filters.type','tool')" class="px-2.5 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1 {{ ($filters['type'] ?? 'all') === 'tool' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">construction</span>
                    <span>Alat Kerja</span>
                </button>
                <button type="button" wire:click="$set('filters.type','onu')" class="px-2.5 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1 {{ ($filters['type'] ?? 'all') === 'onu' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">router</span>
                    <span>Modem/ONU</span>
                </button>
                <button type="button" wire:click="$set('filters.type','network')" class="px-2.5 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1 {{ ($filters['type'] ?? 'all') === 'network' ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">dns</span>
                    <span>Jaringan/Server</span>
                </button>
            </div>

            {{-- Category Filter --}}
            <select wire:model.live="filters.category" class="pl-2.5 pr-7 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                <option value="all">Semua Kategori Aset</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            {{-- Status Filter --}}
            <select wire:model.live="filters.status" class="pl-2.5 pr-7 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                <option value="all">Semua Status</option>
                <option value="available">Tersedia di Gudang (Available)</option>
                <option value="in_use">Di Lapangan / Teknisi (In Use)</option>
                <option value="maintenance">Dalam Perbaikan (Maintenance)</option>
                <option value="retired">Afkir / Rusak Total (Retired)</option>
            </select>

            {{-- Per Page --}}
            <select wire:model.live="perPage" class="pl-2.5 pr-7 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer ml-auto">
                <option value="20">20 / Hal</option>
                <option value="50">50 / Hal</option>
                <option value="100">100 / Hal</option>
                <option value="all">Semua</option>
            </select>

            @if(($filters['search'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all' || ($filters['type'] ?? 'all') !== 'all' || ($filters['category'] ?? 'all') !== 'all')
                <button type="button" wire:click="resetFilters" title="Reset Filter" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 text-red-600 dark:text-red-400 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">close</span>
                    <span>Reset</span>
                </button>
            @endif
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-left">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80">
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Status</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Kode & Nama Alat / Aset</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Kategori & Kondisi</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Nomor Seri (SN)</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Penanggung Jawab (Staf)</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Pembelian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800 text-sm">
                    @forelse($assets as $asset)
                        @php
                            $badgeStatus = match($asset->status) {
                                'in_use'      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                                'available'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                'maintenance' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400',
                                default       => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                            };
                            $typeBadge = match($asset->type) {
                                'tool'    => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700',
                                'onu'     => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
                                'network' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-200 dark:border-purple-800',
                                default   => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                            };
                            $conditionBadge = match($asset->condition) {
                                'good'        => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200',
                                'fair'        => 'text-blue-600 bg-blue-50 dark:bg-blue-950/40 border-blue-200',
                                'damaged'     => 'text-amber-600 bg-amber-50 dark:bg-amber-950/40 border-amber-200',
                                'maintenance' => 'text-rose-600 bg-rose-50 dark:bg-rose-950/40 border-rose-200',
                                default       => 'text-slate-600 bg-slate-50 border-slate-200',
                            };
                        @endphp
                        <tr wire:key="asset-{{ $asset->id }}" class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                            {{-- ACTIONS --}}
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Quick Handover / Return Button --}}
                                    @if($asset->status === 'available')
                                        <button type="button" wire:click="openAssignModal('{{ $asset->id }}')" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400 transition-colors cursor-pointer" title="Serahkan ke Teknisi (Handover)">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">person_add</span>
                                        </button>
                                    @elseif($asset->status === 'in_use')
                                        <button type="button" wire:click="returnAsset('{{ $asset->id }}')" wire:confirm="Kembalikan alat ini ke gudang?" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 transition-colors cursor-pointer" title="Kembalikan ke Gudang (Return)">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">assignment_return</span>
                                        </button>
                                    @endif

                                    <button type="button" wire:click="edit('{{ $asset->id }}')" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 dark:text-emerald-400 transition-colors cursor-pointer" title="Edit Data">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">edit</span>
                                    </button>
                                    <button type="button" wire:click="delete('{{ $asset->id }}')" wire:confirm="Yakin ingin menghapus asset ini?" class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 transition-colors cursor-pointer" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full {{ $badgeStatus }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $asset->status === 'in_use' ? 'bg-emerald-500' : ($asset->status === 'available' ? 'bg-blue-500' : 'bg-rose-500') }}"></span>
                                    {{ $asset->status === 'in_use' ? 'Di Lapangan' : ($asset->status === 'available' ? 'Di Gudang' : ucfirst($asset->status)) }}
                                </span>
                            </td>

                            {{-- KODE & NAMA ASET --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $typeBadge }}">
                                        {{ $asset->type ?: 'ASET' }}
                                    </span>
                                    <div class="font-bold text-slate-800 dark:text-slate-100 hover:text-indigo-600 cursor-pointer" wire:click="edit('{{ $asset->id }}')">
                                        {{ $asset->name }}
                                    </div>
                                </div>
                                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                                    <span>Kode: {{ $asset->code }}</span>
                                    @if($asset->warehouse)
                                        <span class="text-slate-400">• {{ $asset->warehouse->name }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- KATEGORI & KONDISI --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ $asset->category?->name ?? 'Tanpa Kategori' }}
                                </div>
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $conditionBadge }}">
                                        {{ match($asset->condition) {
                                            'good' => 'Kondisi Baik',
                                            'fair' => 'Cukup',
                                            'damaged' => 'Rusak Ringan',
                                            'maintenance' => 'Perlu Servis',
                                            default => 'Baik'
                                        } }}
                                    </span>
                                </div>
                            </td>

                            {{-- SERIAL NUMBER --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-xs font-mono font-medium text-slate-700 dark:text-slate-200">
                                    {{ $asset->serial_number ?: '-' }}
                                </div>
                                @if($asset->mac_address)
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">MAC: {{ $asset->mac_address }}</div>
                                @endif
                            </td>

                            {{-- PENANGGUNG JAWAB (TEKNISI / STAF) --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($asset->assignedTo)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($asset->assignedTo->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $asset->assignedTo->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $asset->assigned_at ? 'Sejak ' . \Carbon\Carbon::parse($asset->assigned_at)->format('d M Y') : 'Teknisi' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic flex items-center gap-1">
                                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:14px">home_repair_service</span>
                                        <span>Gudang (Tersimpan)</span>
                                    </span>
                                @endif
                            </td>

                            {{-- PEMBELIAN --}}
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600 dark:text-slate-400">
                                <div>{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') : '-' }}</div>
                                @if($asset->purchase_price)
                                    <div class="font-mono text-slate-500 mt-0.5">Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700/60 rounded-full flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:28px">handyman</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100 mb-1">Belum ada data Aset / Alat Kerja</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Tambahkan alat kerja seperti Mesin Splicing, Tangga, Tang, atau sinkronkan ONU.</p>
                                    <button type="button" wire:click="create" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                                        <span>Tambah Alat Baru</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ $assets->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ $assets->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-slate-700 dark:text-slate-200">{{ $assets->total() }}</span> data
            </div>
            <div>
                {{ $assets->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL CREATE / EDIT ASET --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('isModalOpen', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="save">
                        <div class="p-5 sm:p-6 space-y-4">
                            <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700">
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2" id="modal-title">
                                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no" style="font-size:22px">{{ $assetId ? 'edit' : 'construction' }}</span>
                                    <span>{{ $assetId ? 'Edit Data Aset & Alat Kerja' : 'Registrasi Alat Kerja / Aset Baru' }}</span>
                                </h3>
                                <button type="button" wire:click="$set('isModalOpen', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">close</span>
                                </button>
                            </div>

                            @if($errors->any())
                                <div class="px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-xs">
                                    <p class="font-bold mb-1">Periksa isian berikut:</p>
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Kode Asset --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Kode Asset / Barcode <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="code" placeholder="Contoh: AST-SPL-001 / TOOL-01" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>

                                {{-- Nama Asset --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Alat / Aset <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="name" placeholder="Contoh: Mesin Splicing Ilsintech / Tangga 3.8m" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>

                                {{-- Tipe Asset --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tipe Aset <span class="text-red-500">*</span></label>
                                    <select wire:model="type" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="tool">Alat Kerja & Perkakas (Tools & Equipment)</option>
                                        <option value="onu">Modem Pelanggan (ONU/ONT)</option>
                                        <option value="network">Perangkat Jaringan (OLT / Router / Switch)</option>
                                        <option value="vehicle">Kendaraan Operasional</option>
                                        <option value="other">Lain-lain</option>
                                    </select>
                                </div>

                                {{-- Kategori --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Kategori</label>
                                    <select wire:model="category_id" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Kondisi Fisik --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Kondisi Fisik <span class="text-red-500">*</span></label>
                                    <select wire:model="condition" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="good">Kondisi Baik / Normal</option>
                                        <option value="fair">Kondisi Cukup (Ada Bekas Pakai)</option>
                                        <option value="damaged">Rusak Ringan (Perlu Dicek)</option>
                                        <option value="maintenance">Perlu Kalibrasi / Perbaikan</option>
                                    </select>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Status Ketersediaan <span class="text-red-500">*</span></label>
                                    <select wire:model="status" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="available">Tersedia di Gudang (Available)</option>
                                        <option value="in_use">Digunakan di Lapangan (In Use)</option>
                                        <option value="maintenance">Dalam Perbaikan (Maintenance)</option>
                                        <option value="retired">Afkir / Tidak Dipakai (Retired)</option>
                                    </select>
                                </div>

                                {{-- Serial Number --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Serial Number (SN)</label>
                                    <input type="text" wire:model="serial_number" placeholder="Nomor seri resmi pabrik" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>

                                {{-- Penugasan ke Teknisi --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Penanggung Jawab (Teknisi)</label>
                                    <select wire:model="assigned_to_id" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="">-- Tetap di Gudang (Tanpa Penugasan) --</option>
                                        @foreach($technicians as $tech)
                                            <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->phone ?: 'Teknisi' }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Harga Pembelian --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Harga Beli / Perolehan (Rp)</label>
                                    <input type="number" wire:model="purchase_price" placeholder="Contoh: 15000000" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>

                                {{-- Tanggal Beli --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tanggal Pembelian</label>
                                    <input type="date" wire:model="purchase_date" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                </div>

                                {{-- Gudang Penyimpanan --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Gudang Penyimpanan</label>
                                    <select wire:model="warehouse_id" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="">-- Pilih Lokasi Gudang --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Vendor / Suplier --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Vendor / Toko Pembelian</label>
                                    <select wire:model="vendor_id" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                        <option value="">-- Pilih Vendor --</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Deskripsi / Catatan Fisik --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Keterangan / Kelengkapan Alat</label>
                                <textarea wire:model="description" rows="2" placeholder="Catatan kelengkapan: Box case, charger splicer, adapter cleaver, tas, dll." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/80 px-5 py-3.5 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="$set('isModalOpen', false)" class="px-5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors inline-flex items-center gap-2 cursor-pointer">
                                <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                                <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                                <span>Simpan Alat / Aset</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL QUICK ASSIGNMENT / HANDOVER TO TECHNICIAN --}}
    @if($isAssignModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-assign" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('isAssignModalOpen', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="submitAssign">
                        <div class="p-5 space-y-4">
                            <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700">
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2" id="modal-assign">
                                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no" style="font-size:22px">person_add</span>
                                    <span>Serahkan Alat Kerja ke Teknisi</span>
                                </h3>
                                <button type="button" wire:click="$set('isAssignModalOpen', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">close</span>
                                </button>
                            </div>

                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Alat kerja yang diserahkan akan otomatis tercatat sebagai <strong>tanggung jawab fisik (Custody)</strong> teknisi dan berstatus <strong>Di Lapangan (In Use)</strong>.
                            </p>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Pilih Teknisi Penanggung Jawab <span class="text-red-500">*</span></label>
                                <select wire:model="assignTechnicianId" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                    <option value="">-- Pilih Teknisi Lapangan --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->phone ?: 'Teknisi' }})</option>
                                    @endforeach
                                </select>
                                @error('assignTechnicianId')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Catatan Serah Terima</label>
                                <textarea wire:model="assignNotes" rows="2" placeholder="Contoh: Dibawa untuk regu tim PSB 01, kondisi baterai full..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/80 px-5 py-3.5 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" wire:click="$set('isAssignModalOpen', false)" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="submitAssign" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
                                <span>Konfirmasi Serah Terima</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
