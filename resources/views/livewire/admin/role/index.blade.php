@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">shield_person</span>
        </div>
        <span class="text-lg">Role & Hak Akses</span>
    </div>
@endsection

<div class="space-y-4 pb-12">
    {{-- Flash Notifications --}}
    @if(session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-emerald-600" translate="no" style="font-size:20px">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">close</span>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-red-600" translate="no" style="font-size:20px">error</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">close</span>
            </button>
        </div>
    @endif

    {{-- Filter Tabs & Toolbar --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-1.5 overflow-x-auto">
        <div class="flex items-center gap-1.5 min-w-max">
            <button wire:click="$set('filterType', 'all')" 
                class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all {{ $filterType === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">admin_panel_settings</span>
                <span>Semua Role</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $filterType === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                    {{ $totalRolesCount }}
                </span>
            </button>

            <button wire:click="$set('filterType', 'system')" 
                class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all {{ $filterType === 'system' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">verified_user</span>
                <span>Role Sistem (Default)</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $filterType === 'system' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                    {{ $systemRolesCount }}
                </span>
            </button>

            <button wire:click="$set('filterType', 'custom')" 
                class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all {{ $filterType === 'custom' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">tune</span>
                <span>Role Kustom</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $filterType === 'custom' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                    {{ $customRolesCount }}
                </span>
            </button>
        </div>
    </div>

    {{-- Toolbar: Search & Action Button --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama role / deskripsi..." 
                class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400">
        </div>

        <button wire:click="openCreateModal" 
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center justify-center gap-2">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
            <span>Tambah Role Baru</span>
        </button>
    </div>

    {{-- Roles Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-900/40 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nama Role & Jabatan</th>
                        <th class="px-6 py-3.5">Tipe Role</th>
                        <th class="px-6 py-3.5">Jumlah User</th>
                        <th class="px-6 py-3.5">Izin Aktif</th>
                        <th class="px-6 py-3.5">Deskripsi</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($roles as $role)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            {{-- Role Name --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $role->is_system ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' }}">
                                        <span class="material-symbols-outlined notranslate text-[20px]" translate="no">
                                            @if($role->name === 'administrator') admin_panel_settings
                                            @elseif($role->name === 'manager') badge
                                            @elseif($role->name === 'reseller') storefront
                                            @elseif($role->name === 'customer') person
                                            @else shield_person
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <span>{{ $role->display_name }}</span>
                                            @if($role->name === 'administrator')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">Full Bypass</span>
                                            @endif
                                        </div>
                                        <code class="text-xs text-slate-400 dark:text-slate-500">{{ $role->name }}</code>
                                    </div>
                                </div>
                            </td>

                            {{-- Type --}}
                            <td class="px-6 py-4">
                                @if($role->is_system)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        Sistem (Default)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Kustom
                                    </span>
                                @endif
                            </td>

                            {{-- User Count --}}
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $role->users_count }}</span>
                                <span class="text-xs text-slate-400">pengguna</span>
                            </td>

                            {{-- Permissions Count --}}
                            <td class="px-6 py-4">
                                @if($role->name === 'administrator')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">all_inclusive</span>
                                        Semua Hak Akses
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">verified</span>
                                        {{ $role->permissions_count }} izin
                                    </span>
                                @endif
                            </td>

                            {{-- Description --}}
                            <td class="px-6 py-4 max-w-xs truncate text-xs text-slate-500 dark:text-slate-400">
                                {{ $role->description ?: '-' }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Atur Permissions Matrix Button --}}
                                    <a href="{{ route('admin.roles.permissions', $role->id) }}" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-semibold transition-colors shadow-sm"
                                        title="Atur Matriks Hak Akses">
                                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">rule</span>
                                        <span>Atur Izin</span>
                                    </a>

                                    {{-- Edit Role Metadata --}}
                                    <button wire:click="openEditModal({{ $role->id }})" 
                                        class="p-1.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60 rounded-lg transition-colors"
                                        title="Edit Informasi Role">
                                        <span class="material-symbols-outlined notranslate text-[18px]" translate="no">edit</span>
                                    </button>

                                    {{-- Delete Role (Custom only) --}}
                                    @if(!$role->is_system)
                                        <button wire:click="confirmDelete({{ $role->id }})" 
                                            class="p-1.5 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition-colors"
                                            title="Hapus Role">
                                            <span class="material-symbols-outlined notranslate text-[18px]" translate="no">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <span class="material-symbols-outlined notranslate text-[36px] block mb-2" translate="no">search_off</span>
                                Tidak ada role yang sesuai pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL CREATE / EDIT ROLE --}}
    @if($showRoleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/20">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-600" translate="no" style="font-size:20px">
                            {{ $editingRoleId ? 'edit_note' : 'add_moderator' }}
                        </span>
                        {{ $editingRoleId ? 'Edit Role' : 'Tambah Role Baru' }}
                    </h3>
                    <button wire:click="$set('showRoleModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">close</span>
                    </button>
                </div>

                <form wire:submit.prevent="saveRole" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Tampilan (Display Name) *
                        </label>
                        <input wire:model.live="roleDisplayName" type="text" placeholder="Contoh: Admin Billing, Helpdesk CS" 
                            class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100">
                        @error('roleDisplayName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kode Slug / Identifier (Unik) *
                        </label>
                        <input wire:model="roleName" type="text" placeholder="contoh: admin_billing" 
                            {{ $editingRoleId && \App\Models\Role::find($editingRoleId)?->is_system ? 'disabled' : '' }}
                            class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:cursor-not-allowed">
                        <p class="text-[11px] text-slate-400 mt-1">Hanya huruf kecil, angka, dan garis bawah/strip.</p>
                        @error('roleName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Deskripsi Tanggung Jawab
                        </label>
                        <textarea wire:model="roleDescription" rows="3" placeholder="Jelaskan cakupan dan tugas untuk pemegang role ini..." 
                            class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100"></textarea>
                        @error('roleDescription') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" wire:click="$set('showRoleModal', false)" 
                            class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveRole"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
                            <span wire:loading.remove wire:target="saveRole" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                            <span wire:loading wire:target="saveRole" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                            <span wire:loading.remove wire:target="saveRole">Simpan Role</span>
                            <span wire:loading wire:target="saveRole">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL DELETE CONFIRMATION --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-sm w-full shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined notranslate text-[24px]" translate="no">warning</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Hapus Role</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 mb-6">
                    Apakah Anda yakin ingin menghapus role <strong class="text-slate-900 dark:text-white">{{ $deletingRoleName }}</strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex items-center justify-center gap-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)" 
                        class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteRole" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                        <span>Ya, Hapus</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
