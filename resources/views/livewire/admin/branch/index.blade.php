<div>
    <x-admin.page-header title="Manajemen Cabang / Area" subtitle="Kelola cabang dan area operasional ISP Anda">
        <x-slot name="actions">
            <x-base.button wire:click="openCreate" variant="primary" class="flex items-center gap-2 shadow-sm">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Cabang
            </button>
        </x-slot>
    </x-admin.page-header>

    <div class="max-w-7xl mx-auto mt-6 space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-lg text-emerald-700 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 rounded-lg text-red-700 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
                <div class="p-3 rounded-full bg-primary-100">
                    <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Cabang</p>
                    <p class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ $stats['total'] }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
                <div class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Aktif</p>
                    <p class="text-xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
                <div class="p-3 rounded-full bg-slate-100 dark:bg-slate-700">
                    <svg class="w-6 h-6 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Nonaktif</p>
                    <p class="text-xl font-bold text-slate-500 dark:text-slate-400">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            {{-- Search --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="font-bold text-slate-800 dark:text-slate-200">Daftar Cabang</h2>
                <div class="relative">
                    <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama- kode- kota..." 
                        class="pl-9 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 w-full sm:w-64 dark:bg-slate-900 dark:text-slate-100">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                        <tr class="text-slate-500 dark:text-slate-400">
                            <th class="p-3 font-semibold">Kode</th>
                            <th class="p-3 font-semibold">Nama Cabang</th>
                            <th class="p-3 font-semibold">Kota / Provinsi</th>
                            <th class="p-3 font-semibold">Kontak</th>
                            <th class="p-3 font-semibold text-center">Pelanggan</th>
                            <th class="p-3 font-semibold text-center">Status</th>
                            <th class="p-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($branches as $branch)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50 transition-colors">
                                <td class="p-3">
                                    <x-ui.badge variant="neutral">{{ $branch->code }}</x-ui.badge>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $branch->name }}</div>
                                    @if($branch->address)
                                        <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $branch->address }}</div>
                                    @endif
                                </td>
                                <td class="p-3  text-slate-600 dark:text-slate-400">
                                    {{ $branch->city ?? '-' }}
                                    @if($branch->province)
                                        <span class="text-slate-400">- {{ $branch->province }}</span>
                                    @endif
                                </td>
                                <td class="p-3  text-slate-600 dark:text-slate-400">
                                    @if($branch->phone)
                                        <div class="flex items-center gap-1 text-xs">
                                            <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            {{ $branch->phone }}
                                        </div>
                                    @endif
                                    @if($branch->email)
                                        <div class="flex items-center gap-1 text-xs text-slate-400 mt-0.5">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            {{ $branch->email }}
                                        </div>
                                    @endif
                                    @if(!$branch->phone && !$branch->email) <span class="text-slate-300">—</span> @endif
                                </td>
                                <td class="p-3  text-center">
                                    <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $branch->customers_count }}</span>
                                    <span class="text-slate-400 text-xs"> pelanggan</span>
                                </td>
                                <td class="p-3  text-center">
                                    <button wire:click="toggleActive({{ $branch->id }})" class="cursor-pointer">
                                        @if($branch->is_active)
                                            <x-ui.badge variant="success">AKTIF</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral">NONAKTIF</x-ui.badge>
                                        @endif
                                    </button>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openEdit({{ $branch->id }})" 
                                            class="p-1.5 text-primary-500 hover:bg-primary-50 rounded-md transition-colors" title="Edit">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </button>
                                        <button wire:click="delete({{ $branch->id }})"
                                            wire:confirm="Yakin ingin menghapus cabang '{{ $branch->name }}'-"
                                            class="p-1.5 text-red-400 hover:bg-red-50 dark:bg-red-900/30 rounded-md transition-colors" title="Hapus">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td colspan="7" class="p-3  text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400">
                                        <svg class="w-12 h-12 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <p class="font-medium">Belum ada data cabang</p>
                                        <p class="text-sm">Klik tombol <strong>Tambah Cabang</strong> untuk memulai</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($branches->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                    {{ $branches->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ============ MODAL TAMBAH / EDIT ============ --}}
    @if($showModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-xl flex flex-col max-h-[92vh]">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">
                    {{ $isEditing ?'Edit Cabang' : 'Tambah Cabang Baru' }}
                </h3>
                <button wire:click="$set('showModal'- false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 transition-colors">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 overflow-y-auto">
                <form wire:submit.prevent="save" id="branchForm">
                    <div class="space-y-4">

                        {{-- Kode & Nama --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Kode <span class="text-red-500">*</span>
                                    <span class="text-xs text-slate-400 font-normal">(unik)</span>
                                </label>
                                <input type="text" wire:model="form_code" placeholder="CBG-01"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 uppercase @error('form_code') border-red-400 @enderror dark:bg-slate-900 dark:text-slate-100">
                                @error('form_code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="form_name" placeholder="Cabang Sukabumi"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 @error('form_name') border-red-400 @enderror dark:bg-slate-900 dark:text-slate-100">
                                @error('form_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Kota & Provinsi --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kota</label>
                                <input type="text" wire:model="form_city" placeholder="Sukabumi"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Provinsi</label>
                                <input type="text" wire:model="form_province" placeholder="Jawa Barat"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                            <textarea wire:model="form_address" rows="2" placeholder="Jl. Raya Sukabumi No. 10..."
                                class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 resize-none dark:bg-slate-900 dark:text-slate-100"></textarea>
                        </div>

                        {{-- Telepon & Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">No. Telepon</label>
                                <input type="text" wire:model="form_phone" placeholder="0266xxxxxx"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                                <input type="email" wire:model="form_email" placeholder="cabang@isp.id"
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 @error('form_email') border-red-400 @enderror dark:bg-slate-900 dark:text-slate-100">
                                @error('form_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Status & Catatan --}}
                        <div class="flex items-center gap-3 pt-1">
                            <button type="button" wire:click="$toggle('form_is_active')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $form_is_active ?'bg-emerald-500' : 'bg-slate-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white dark:bg-slate-800 shadow transition-transform {{ $form_is_active ?'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ $form_is_active ?'Cabang Aktif' : 'Cabang Nonaktif' }}
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Catatan</label>
                            <textarea wire:model="form_notes" rows="2" placeholder="Catatan internal tentang cabang ini..."
                                class="w-full rounded-lg border-slate-300 dark:border-slate-600 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500 resize-none dark:bg-slate-900 dark:text-slate-100"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" wire:click="$set('showModal'- false)"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-800/50 transition-colors">
                    Batal
                </button>
                <button type="submit" form="branchForm" wire:loading.attr="disabled"
                    class="px-5 py-2 bg-primary-600 text-white rounded-lg text-sm font-bold hover:bg-primary-700 transition-colors shadow-sm flex items-center gap-2 disabled:opacity-60">
                    <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ $isEditing ?'Simpan Perubahan' : 'Tambah Cabang' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>






