@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">edit</span>
    <span class="text-lg">Edit Hotspot: {{ $username ?? 'User' }}</span>
@endsection

<div class="space-y-5 pb-10 max-w-4xl">
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
    @if(session('info'))
        <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl text-blue-700 dark:text-blue-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
            {{ session('info') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit.prevent="save">
            <div class="p-6 space-y-8">
                <!-- Section 1: Identitas Pelanggan -->
                <div>
                    <div class="flex items-center gap-2 mb-4 text-indigo-600 dark:text-indigo-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">badge</span>
                        <h3 class="text-base font-bold uppercase tracking-wider">Identitas Pelanggan</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap</label>
                            <input type="text" wire:model="name" placeholder="Contoh: Budi Santoso" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nomor HP</label>
                            <input type="text" wire:model="phone" placeholder="081234567890" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email (Opsional)</label>
                            <input type="email" wire:model="email" placeholder="budi@example.com" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Owner / Reseller Mitra</label>
                            <select wire:model="reseller_id" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                <option value="">Kantor Pusat (HQ)</option>
                                @foreach($resellers as $reseller)
                                    <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                                @endforeach
                            </select>
                            @error('reseller_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Alamat Pemasangan</label>
                            <textarea wire:model="address" placeholder="Masukkan alamat lengkap" rows="2" 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100"></textarea>
                            @error('address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Catatan Khusus</label>
                            <textarea wire:model="notes" placeholder="Catatan tambahan (opsional)" rows="2" 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100"></textarea>
                            @error('notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-slate-700/50">

                <!-- Section 2: Login Hotspot -->
                <div>
                    <div class="flex items-center gap-2 mb-4 text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">router</span>
                        <h3 class="text-base font-bold uppercase tracking-wider">Kredensial Radius</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Username Hotspot</label>
                            <div class="relative">
                                <input type="text" wire:model.live="username" id="edit-username" placeholder="Contoh: budi_net" 
                                       class="w-full pl-4 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('edit-username').value); alert('Username dicopy!')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-500">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                                </button>
                            </div>
                            @error('username') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                            <div class="relative">
                                <input type="text" wire:model="password" id="edit-password" placeholder="Minimal 6 karakter" 
                                       class="w-full pl-4 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('edit-password').value); alert('Password dicopy!')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-500">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                                </button>
                            </div>
                            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Router NAS</label>
                            <select wire:model="router_id" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Router --</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}">{{ $router->name }}</option>
                                @endforeach
                            </select>
                            @error('router_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Profil / Paket Internet</label>
                            <select wire:model="service_profile_id" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Profil Paket --</option>
                                @foreach($serviceProfiles as $profile)
                                    <option value="{{ $profile->id }}">{{ $profile->name }} ({{ $profile->download_speed }}/{{ $profile->upload_speed }} Mbps) - {{ $profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : 'Gratis' }}</option>
                                @endforeach
                            </select>
                            @error('service_profile_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2 mt-2">
                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 pb-2 mb-4">Pengaturan Lanjutan Jaringan (Opsional)</h4>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">IP Address Khusus (Static IP)</label>
                                <button type="button" wire:click="showUsedIps" class="inline-flex items-center px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-medium rounded-md hover:bg-indigo-100 dark:bg-indigo-900/50 dark:hover:bg-indigo-900/50 transition-colors">
                                    <span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size:14px">search</span>
                                    Cek IP Terpakai
                                </button>
                            </div>
                            <input type="text" wire:model="static_ip" placeholder="Kosongkan untuk otomatis dari Pool" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            @error('static_ip') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">MAC Address / Caller ID</label>
                            <input type="text" wire:model="mac_address" placeholder="Contoh: 00:11:22:33:44:55" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            @error('mac_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Data ODP (Kotak Tiang)</label>
                            <div class="flex gap-2">
                                <select wire:model="odp_id" 
                                        class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Pilih ODP --</option>
                                    @foreach($odps as $odp)
                                        <option value="{{ $odp->id }}">{{ $odp->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" wire:model="port_number" placeholder="Port" title="Port Number" 
                                       class="w-24 px-3 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            @error('odp_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            @error('port_number') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">ONU / Serial Number Modem</label>
                            <input type="text" wire:model="onu_id" placeholder="Contoh: ZTEG12345678" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            @error('onu_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-slate-700/50">

                <!-- Section 3: Tagihan & Aktivasi -->
                <div>
                    <div class="flex items-center gap-2 mb-4 text-amber-500 dark:text-amber-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">payments</span>
                        <h3 class="text-base font-bold uppercase tracking-wider">Tagihan & Aktivasi</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipe Penagihan (Billing Cycle)</label>
                            <select wire:model="billing_cycle" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                <option value="monthly">Bulanan (Standard)</option>
                                <option value="prepaid">Prabayar (Bayar di Depan)</option>
                                <option value="postpaid">Pascabayar (Pakai Baru Bayar)</option>
                            </select>
                            @error('billing_cycle') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Biaya Instalasi (Setup Fee)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 dark:text-slate-400">Rp</span>
                                <input type="number" wire:model="setup_fee" placeholder="0" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            @error('setup_fee') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Aktivasi</label>
                            <input type="date" wire:model="activation_date" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                            @error('activation_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status Awal</label>
                            <select wire:model="status" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                            @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/50 p-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                <a href="{{ route('isp.hotspot-users.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors cursor-pointer">
                    <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">arrow_back</span>
                    Kembali
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-colors cursor-pointer">
                    <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
