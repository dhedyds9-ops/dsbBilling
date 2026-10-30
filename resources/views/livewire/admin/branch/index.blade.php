@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">store</span>
        </div>
        <span class="text-lg">Manajemen Cabang / Wilayah Kantor</span>
    </div>
@endsection

<div class="space-y-6 pb-10">

    {{-- Header Actions & Stats --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Daftar Cabang & Wilayah Operasional</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola cabang kantor dan area operasional jaringan ISP dsBilling.</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">add</span>
                <span>Tambah Cabang</span>
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-300 text-sm shadow-sm">
            <span class="material-symbols-outlined text-[20px] text-emerald-600 shrink-0">check_circle</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-300 text-sm shadow-sm">
            <span class="material-symbols-outlined text-[20px] text-rose-600 shrink-0">error</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">store</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Cabang</p>
                <p class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">verified</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Cabang Aktif</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['active'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">block</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Nonaktif</p>
                <p class="text-2xl font-black text-slate-600 dark:text-slate-300">{{ $stats['inactive'] }}</p>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        {{-- Search Toolbar --}}
        <div class="p-4 border-b border-slate-100 dark:border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/30">
            <div class="relative w-full sm:w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px] pointer-events-none">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kode, kota..."
                    class="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Total: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $branches->total() }}</span> cabang
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="px-5 py-3.5">Nama Cabang</th>
                        <th class="px-5 py-3.5">Kota / Provinsi</th>
                        <th class="px-5 py-3.5">Kontak</th>
                        <th class="px-5 py-3.5 text-center">Pelanggan</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($branches as $branch)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-blue-600 dark:text-blue-400">
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800">
                                    {{ $branch->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $branch->name }}</div>
                                @if($branch->address)
                                    <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[240px]">{{ $branch->address }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">
                                <div>{{ $branch->city ?: '-' }}</div>
                                @if($branch->province)
                                    <div class="text-xs text-slate-400">{{ $branch->province }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">
                                @if($branch->phone)
                                    <div class="flex items-center gap-1 text-xs">
                                        <span class="material-symbols-outlined text-[14px] text-slate-400">call</span>
                                        <span>{{ $branch->phone }}</span>
                                    </div>
                                @endif
                                @if($branch->email)
                                    <div class="flex items-center gap-1 text-xs text-slate-400 mt-0.5">
                                        <span class="material-symbols-outlined text-[14px] text-slate-400">mail</span>
                                        <span>{{ $branch->email }}</span>
                                    </div>
                                @endif
                                @if(!$branch->phone && !$branch->email)
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-slate-900 dark:text-slate-100 font-bold">{{ $branch->customers_count }}</span>
                                <span class="text-slate-400 text-xs"> pelanggan</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <button type="button" wire:click="toggleActive({{ $branch->id }})" class="cursor-pointer transition-transform hover:scale-105">
                                    @if($branch->is_active)
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">AKTIF</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">NONAKTIF</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" wire:click="openEdit({{ $branch->id }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/50 rounded-lg transition-colors" title="Edit Cabang">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <button type="button" wire:click="delete({{ $branch->id }})"
                                        wire:confirm="Yakin ingin menghapus cabang '{{ $branch->name }}'?"
                                        class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-colors" title="Hapus Cabang">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-[48px] text-slate-300 dark:text-slate-600">store_mall_directory</span>
                                    <p class="font-bold text-slate-700 dark:text-slate-300 text-base">Belum ada data cabang</p>
                                    <p class="text-xs text-slate-400">Klik tombol <strong>Tambah Cabang</strong> di atas untuk membuat cabang baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($branches->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $branches->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah / Edit Cabang --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-200 dark:border-slate-700" @click.away="$wire.set('showModal', false)">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 text-[22px]">store</span>
                        <span>{{ $isEditing ? 'Edit Cabang' : 'Tambah Cabang Baru' }}</span>
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Form Body --}}
                <form wire:submit.prevent="save">
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Kode <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" wire:model="form_code" placeholder="CBG-01" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 uppercase bg-slate-50 dark:bg-slate-900 dark:text-slate-100 font-mono">
                                @error('form_code') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Nama Cabang <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" wire:model="form_name" placeholder="Contoh: Cabang Ciamis Kota" required
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50 dark:bg-slate-900 dark:text-slate-100">
                                @error('form_name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Kota / Kabupaten</label>
                                <input type="text" wire:model="form_city" placeholder="Ciamis"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Provinsi</label>
                                <input type="text" wire:model="form_province" placeholder="Jawa Barat"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Alamat Lengkap Kantor</label>
                            <textarea wire:model="form_address" rows="2" placeholder="Jl. Raya No. 123..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50 dark:bg-slate-900 dark:text-slate-100 resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">No. Telepon / WA</label>
                                <input type="text" wire:model="form_phone" placeholder="08xxxxxxxxxx"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Email Cabang</label>
                                <input type="email" wire:model="form_email" placeholder="cabang@mstore.id"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50 dark:bg-slate-900 dark:text-slate-100">
                                @error('form_email') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" wire:click="$toggle('form_is_active')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $form_is_active ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $form_is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                {{ $form_is_active ? 'Cabang Aktif' : 'Cabang Nonaktif' }}
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Catatan / Keterangan</label>
                            <textarea wire:model="form_notes" rows="2" placeholder="Catatan internal cabang..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50 dark:bg-slate-900 dark:text-slate-100 resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-sm transition-colors flex items-center gap-2 disabled:opacity-50">
                            <span wire:loading wire:target="save" class="material-symbols-outlined text-[18px] animate-spin">autorenew</span>
                            <span>{{ $isEditing ? 'Simpan Perubahan' : 'Tambah Cabang' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
