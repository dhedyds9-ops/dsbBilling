<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-3xl">warehouse</span>
                <span>Data Gudang & Lokasi Penyimpanan</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kelola gudang pusat, gudang cabang, dan titik transit material perusahaan.
            </p>
        </div>

        <button wire:click="openCreateModal" type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Tambah Gudang Baru</span>
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-fadeIn">
            <span class="material-symbols-outlined">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-4 shadow-xs">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative flex-1 min-w-0">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama gudang, kode, kota..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-slate-100 placeholder:text-slate-400">
            </div>

            <div class="w-full sm:w-auto sm:min-w-[220px]">
                <select wire:model.live="typeFilter"
                        class="w-full py-2.5 px-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-slate-100 cursor-pointer">
                    <option value="">Semua Tipe Gudang</option>
                    <option value="main">Gudang Utama (Pusat)</option>
                    <option value="branch">Gudang Cabang</option>
                    <option value="transit">Gudang Transit</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Warehouses Grid Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($warehouses as $wh)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-xs hover:border-emerald-500 dark:hover:border-emerald-500 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-xl">warehouse</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 leading-snug">{{ $wh->name }}</h3>
                                <span class="text-[10px] font-mono font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded">{{ $wh->code }}</span>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase
                            {{ $wh->type === 'main' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : ($wh->type === 'branch' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300') }}">
                            {{ strtoupper($wh->type) }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-2 text-xs text-slate-600 dark:text-slate-300">
                        @if($wh->address)
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-slate-400 shrink-0" style="font-size:16px">location_on</span>
                                <span class="line-clamp-2 text-[11px]">{{ $wh->address }}{{ $wh->city ? ', ' . $wh->city : '' }}</span>
                            </div>
                        @endif

                        @if($wh->manager)
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="material-symbols-outlined text-slate-400 shrink-0" style="font-size:16px">person</span>
                                <span>PIC: <strong class="text-slate-800 dark:text-slate-200">{{ $wh->manager->name }}</strong></span>
                            </div>
                        @endif

                        @if($wh->branch)
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="material-symbols-outlined text-slate-400 shrink-0" style="font-size:16px">domain</span>
                                <span>Cabang: <strong class="text-indigo-600 dark:text-indigo-400">{{ $wh->branch->name }}</strong></span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <div class="text-[11px] font-bold text-slate-500">
                        {{ $wh->stocks_count }} Jenis Barang
                    </div>

                    <div class="flex items-center gap-1">
                        <button wire:click="openEditModal({{ $wh->id }})" type="button" class="p-1.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-lg transition-colors" title="Edit Gudang">
                            <span class="material-symbols-outlined" style="font-size:18px">edit</span>
                        </button>
                        <button wire:click="deleteWarehouse({{ $wh->id }})" wire:confirm="Hapus gudang '{{ $wh->name }}'?" type="button" class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors" title="Hapus Gudang">
                            <span class="material-symbols-outlined" style="font-size:18px">delete</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                <span class="material-symbols-outlined text-4xl mb-2">warehouse</span>
                <p class="text-xs font-semibold">Belum ada gudang yang terdaftar.</p>
            </div>
        @endforelse
    </div>

    <div>{{ $warehouses->links() }}</div>

    {{-- MODAL TAMBAH / EDIT GUDANG --}}
    @if($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 max-w-lg w-full space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600">{{ $warehouseId ? 'edit' : 'add_box' }}</span>
                        <span>{{ $warehouseId ? 'Edit Gudang' : 'Tambah Gudang Baru' }}</span>
                    </h3>
                    <button wire:click="$set('showFormModal', false)" type="button" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Kode Gudang *</label>
                            <input type="text" wire:model="code" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono uppercase bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            @error('code') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Tipe Gudang *</label>
                            <select wire:model="type" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                <option value="main">Gudang Utama (Pusat)</option>
                                <option value="branch">Gudang Cabang</option>
                                <option value="transit">Gudang Transit</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Nama Gudang *</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Gudang Pusat Jakarta / Gudang Sukabumi"
                               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        @error('name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Cabang Organisasi</label>
                            <select wire:model="branch_id" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                <option value="">-- Semua / Pusat --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Penanggung Jawab (PIC)</label>
                            <select wire:model="manager_id" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih PIC --</option>
                                @foreach($managers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                        <textarea wire:model="address" rows="2" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Kota</label>
                            <input type="text" wire:model="city" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Nomor Telepon</label>
                            <input type="text" wire:model="phone" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl space-y-2 border border-slate-100 dark:border-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Gudang Aktif</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="allows_returns" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Menerima Pengembalian / Retur Material</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button wire:click="$set('showFormModal', false)" type="button"
                            class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button wire:click="saveWarehouse" type="button"
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition-colors">
                        Simpan Gudang
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
