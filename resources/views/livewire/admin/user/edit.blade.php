@section('page_title')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.users.index') }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
        </a>
        <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
        </div>
        <span class="text-lg">Edit User</span>
    </div>
@endsection

<div class="space-y-6 pb-12 max-w-4xl">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Informasi User</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Ubah detail profil dan hak akses untuk {{ $user->name }}.</p>
        </div>
        
        <form wire:submit.prevent="save" class="p-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap *</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">No. Identitas (KTP/SIM) *</label>
                    <input type="text" wire:model="identity_number" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('identity_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Posisi / Jabatan</label>
                    <select wire:model.live="job_title" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih Posisi / Jabatan --</option>
                        @foreach(\App\Enums\JobFunction::ispPositions() as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                        @if($job_title && !in_array($job_title, \App\Enums\JobFunction::ispPositions()))
                            <option value="{{ $job_title }}" selected>{{ $job_title }} (Kustom)</option>
                        @endif
                    </select>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Hak akses sistem (Role) dan matriks izin akan otomatis disesuaikan dengan jabatan yang dipilih.</p>
                    @error('job_title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email *</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nomor WhatsApp</label>
                    <input type="text" wire:model="whatsapp" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('whatsapp') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Lengkap</label>
                <textarea wire:model="address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100"></textarea>
                @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cabang / Wilayah Kantor</label>
                    <select wire:model="branch_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Semua / Kantor Pusat (Tanpa Cabang) --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Mengisolasi perangkat jaringan OLT/ODC/ODP per cabang kantor.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Wilayah Operasional / Area (Reseller/Teknisi)</label>
                    <input type="text" wire:model="wilayah" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400" placeholder="Contoh: Kawali, Panjalu, atau Ciamis Kota">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Menentukan ODP, ODC, dan pelanggan yang tampil di portal reseller.</p>
                </div>
            </div>
            
            <div class="border-t border-slate-200 dark:border-slate-700 pt-6 mt-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">key</span>
                    Kredensial Login
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username *</label>
                        <input type="text" wire:model="username" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400">
                        @error('username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password Baru</label>
                        <div class="relative">
                            <input x-ref="pwInput" :type="show ? 'text' : 'password'" wire:model="password" class="w-full px-4 py-2.5 pr-20 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400" placeholder="Kosongkan jika tidak diubah">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-1">
                                <button type="button" @click="show = !show" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors" title="Lihat Password">
                                    <span class="material-symbols-outlined notranslate text-[20px]" x-text="show ? 'visibility_off' : 'visibility'" translate="no">visibility</span>
                                </button>
                                <button type="button" @click="navigator.clipboard.writeText($refs.pwInput.value); window.toast ? window.toast('Password disalin') : alert('Password disalin!')" class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-colors" title="Salin Password">
                                    <span class="material-symbols-outlined notranslate text-[20px]" translate="no">content_copy</span>
                                </button>
                            </div>
                        </div>
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ showConfirm: false }">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Konfirmasi Password</label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" wire:model="password_confirmation" class="w-full px-4 py-2.5 pr-12 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400" placeholder="Ketik ulang password baru">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                <button type="button" @click="showConfirm = !showConfirm" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors" title="Lihat Password">
                                    <span class="material-symbols-outlined notranslate text-[20px]" x-text="showConfirm ? 'visibility_off' : 'visibility'" translate="no">visibility</span>
                                </button>
                            </div>
                        </div>
                        @error('password_confirmation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700 pt-6 mt-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">admin_panel_settings</span>
                    Hak Akses (Role)
                </h3>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Pilih Hak Akses Sistem (Role) *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($roles as $role)
                            <label class="relative flex cursor-pointer p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors {{ $selectedRoles == $role->id ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-500 ring-1 ring-indigo-500' : 'bg-white dark:bg-slate-800' }}">
                                <input type="radio" wire:model.live="selectedRoles" value="{{ $role->id }}" class="sr-only">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $role->display_name ?? ucfirst($role->name) }}
                                    </span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        @if($role->name == 'administrator') Akses penuh seluruh sistem (Owner/Superadmin)
                                        @elseif($role->name == 'admin_gudang') Manajemen persediaan, stok fisik, material & distribusi
                                        @elseif($role->name == 'kasir') Pembayaran tagihan, kasir billing & laporan harian
                                        @elseif($role->name == 'customer_service') Tiket komplain pelanggan & CRM helpdesk
                                        @elseif($role->name == 'noc') Monitoring router Mikrotik, OLT, GenieACS & jaringan
                                        @elseif($role->name == 'teknisi') Portal teknisi lapangan, instalasi & perbaikan
                                        @elseif($role->name == 'marketing') Manajemen prospek, registrasi & promosi penjualan
                                        @elseif($role->name == 'manager') Laporan eksekutif & monitoring operasional kantor
                                        @elseif($role->name == 'reseller') Portal mitra penjualan & voucher internet
                                        @else {{ $role->description ?: 'Hak akses sistem standar' }}
                                        @endif
                                    </span>
                                </div>
                                @if($selectedRoles == $role->id)
                                    <span class="absolute top-4 right-4 material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no" style="font-size:20px">check_circle</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    @error('selectedRoles') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
                </div>
                
                <div class="flex items-center gap-2 mt-6">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-600 dark:bg-slate-900 dark:text-slate-100">
                    <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Akun Aktif</label>
                </div>

                <!-- Matriks Kustomisasi Granular Hak Akses (Permissions) -->
                <div class="mt-6 p-6 bg-slate-50/80 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">tune</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    Kustomisasi Hak Akses Spesifik (Granular Permissions)
                                    <span class="text-[11px] font-normal px-2.5 py-0.5 rounded-full {{ $custom_permissions ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 font-semibold' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                        {{ $custom_permissions ? 'Mode Kustom Aktif' : 'Standar Role' }}
                                    </span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Sesuaikan hak akses per user secara mandiri (misal: Reseller A vs B atau Teknisi A vs B dengan batasan berbeda). Jika nonaktif, hak akses otomatis mengikuti role bawaan.
                                </p>
                            </div>
                        </div>
                        
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" wire:model.live="custom_permissions" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                            <span class="ms-3 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $custom_permissions ? 'Kustomisasi Aktif' : 'Gunakan Standar Role' }}
                            </span>
                        </label>
                    </div>

                    @if($custom_permissions)
                        <!-- Toolbar Aksi Cepat & Pencarian -->
                        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pt-1">
                            <div class="relative flex-1 max-w-md">
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                                <input type="text" wire:model.live.debounce.250ms="permissionSearch" placeholder="Cari hak akses (misal: customer, onu, voucher)..."
                                       class="w-full pl-9 pr-8 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @if($permissionSearch)
                                    <button type="button" wire:click="$set('permissionSearch', '')" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-0.5">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="selectAllRolePermissions" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">done_all</span>
                                    Full Akses
                                </button>
                                <button type="button" wire:click="resetToRoleDefault" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                                    Reset Standar Role
                                </button>
                                <button type="button" wire:click="clearAllPermissions" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">clear_all</span>
                                    Kosongkan
                                </button>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 ml-1">
                                    <strong class="text-indigo-600 dark:text-indigo-400 font-bold font-mono">{{ count($selectedPermissions) }}</strong> izin terpilih
                                </span>
                            </div>
                        </div>

                        <!-- Daftar Kategori Permission Accordion/Card -->
                        <div class="space-y-3.5 pt-2">
                            @forelse($permissionCategories as $catKey => $category)
                                @php
                                    $catPermIds = $category['permissions']->pluck('id')->map(fn($id) => (string)$id)->toArray();
                                    $selectedInCat = array_intersect($catPermIds, $selectedPermissions);
                                    $countSelected = count($selectedInCat);
                                    $totalInCat = count($catPermIds);
                                    $isAllCatSelected = ($totalInCat > 0 && $countSelected === $totalInCat);
                                @endphp
                                <div x-data="{ open: true }" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700/80 overflow-hidden shadow-xs">
                                    <!-- Category Header -->
                                    <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-800/60 flex items-center justify-between border-b border-slate-200/80 dark:border-slate-700/80">
                                        <div class="flex items-center gap-2.5 cursor-pointer select-none" @click="open = !open">
                                            <span class="material-symbols-outlined notranslate text-indigo-500 text-[20px]" translate="no">{{ $category['icon'] ?? 'folder' }}</span>
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                                    {{ $category['title'] }}
                                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $countSelected > 0 ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 font-semibold' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                                        {{ $countSelected }} / {{ $totalInCat }}
                                                    </span>
                                                </h4>
                                                <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $category['description'] }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="toggleCategory('{{ $catKey }}')" class="px-2.5 py-1 text-[11px] font-medium rounded-md {{ $isAllCatSelected ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/30' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/30' }} transition-colors">
                                                {{ $isAllCatSelected ? 'Batal Semua' : 'Pilih Semua' }}
                                            </button>
                                            <button type="button" @click="open = !open" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no" x-text="open ? 'expand_less' : 'expand_more'">expand_less</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Category Items Grid -->
                                    <div x-show="open" class="p-3.5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                        @foreach($category['permissions'] as $perm)
                                            @php $permIdStr = (string)$perm->id; @endphp
                                            <label class="relative flex items-start gap-2.5 p-2 rounded-lg border cursor-pointer select-none transition-colors {{ in_array($permIdStr, $selectedPermissions) ? 'bg-indigo-50/60 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-800' : 'bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700' }}">
                                                <input type="checkbox" wire:model.live="selectedPermissions" value="{{ $permIdStr }}" class="mt-0.5 w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-800">
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate" title="{{ $perm->display_name ?: $perm->name }}">
                                                        {{ $perm->display_name ?: $perm->name }}
                                                    </span>
                                                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 truncate" title="{{ $perm->name }}">
                                                        {{ $perm->name }}
                                                    </span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
                                    Tidak ada hak akses yang cocok dengan pencarian "{{ $permissionSearch }}".
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 rounded-xl text-xs text-indigo-700 dark:text-indigo-300 flex items-start gap-3">
                            <span class="material-symbols-outlined notranslate text-[20px] text-indigo-500 shrink-0 mt-0.5" translate="no">info</span>
                            <div>
                                <span class="font-bold">Mode Hak Akses Standar Aktif:</span> Pengguna ini akan secara otomatis memperoleh seluruh izin bawaan sesuai Role yang dipilih di atas (misal: Mitra Reseller mendapatkan akses CRM, Invoice, Voucher, dan ONU wilayahnya; Teknisi mendapatkan akses SPK, QC, Absensi, dan Diagnosa ONU).
                                <div class="mt-1 text-slate-500 dark:text-slate-400">
                                    Aktifkan switch <strong>"Kustomisasi Hak Akses Spesifik"</strong> di kanan atas jika ingin membedakan hak akses pengguna ini (misal mencabut izin cetak voucher atau konfigurasi WAN).
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-6 mt-2 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                    <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
