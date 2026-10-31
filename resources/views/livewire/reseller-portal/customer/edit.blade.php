@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">edit_note</span>
    <span class="text-lg">Edit Pelanggan & Layanan</span>
@endsection

<div class="space-y-6 pb-12 max-w-5xl mx-auto">
    {{-- Header & Back Button --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('reseller-portal.customers.index') }}" 
               class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm transition-all hover:scale-105">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    Edit Pelanggan: <span class="text-indigo-600 dark:text-indigo-400">{{ $name ?: 'Pelanggan' }}</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Perbarui profil pelanggan, kredensial PPPoE / Hotspot, router NAS, ODP, dan perangkat modem ONT / ONU.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($status === 'active')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Aktif
                </span>
            @elseif($status === 'suspended' || $status === 'suspend')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                    Suspend
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>
                    Nonaktif
                </span>
            @endif
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium shadow-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium shadow-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Main Form Container --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit.prevent="save">
            <div class="p-6 md:p-8 space-y-8">
                
                {{-- SECTION 1: Identitas Pelanggan --}}
                <div>
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700 text-indigo-600 dark:text-indigo-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">badge</span>
                        <h3 class="text-sm font-bold uppercase tracking-wider">1. Identitas Pelanggan</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="name" placeholder="Contoh: Budi Santoso" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Nomor Telepon / WhatsApp
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">call</span>
                                </span>
                                <input type="text" wire:model="phone" placeholder="081234567890" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            </div>
                            @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Email (Opsional)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">mail</span>
                                </span>
                                <input type="email" wire:model="email" placeholder="budi@example.com" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            </div>
                            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Alamat Pemasangan
                            </label>
                            <textarea wire:model="address" placeholder="Masukkan alamat lengkap rumah/lokasi instalasi" rows="2" 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
                            @error('address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <x-forms.coordinate-picker latModel="latitude" lngModel="longitude" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Catatan Khusus
                            </label>
                            <textarea wire:model="notes" placeholder="Catatan tambahan mengenai pelanggan atau instalasi..." rows="2" 
                                      class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
                            @error('notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Kredensial Layanan & Jaringan --}}
                <div>
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700 text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">router</span>
                        <h3 class="text-sm font-bold uppercase tracking-wider">2. Layanan & Kredensial Jaringan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Tipe Layanan --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Tipe Layanan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="service_type" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                <option value="pppoe">PPPoE (Dial-up Broadband)</option>
                                <option value="hotspot">Hotspot (Voucher / Login Page)</option>
                            </select>
                            @error('service_type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Metode Login --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Mode Kredensial
                            </label>
                            <select wire:model.live="login_method" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                <option value="username_and_password">Username & Password Terpisah</option>
                                <option value="username_only">Password Sama dengan Username</option>
                            </select>
                        </div>

                        {{-- Username --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Username {{ strtoupper($service_type) }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative" x-data="{ copyUsername() { window.copyToClipboard ? window.copyToClipboard($refs.uInput.value, 'Username disalin!') : navigator.clipboard.writeText($refs.uInput.value); } }">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">account_circle</span>
                                </span>
                                <input type="text" wire:model.live="username" x-ref="uInput" placeholder="Contoh: budi_net" 
                                       class="w-full pl-10 pr-10 py-2.5 font-mono bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <button type="button" @click="copyUsername" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Username">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                                </button>
                            </div>
                            @error('username') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Password --}}
                        @if($login_method !== 'username_only')
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Password
                            </label>
                            <div class="relative" x-data="{ copyPassword() { window.copyToClipboard ? window.copyToClipboard($refs.pInput.value, 'Password disalin!') : navigator.clipboard.writeText($refs.pInput.value); } }">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">key</span>
                                </span>
                                <input type="text" wire:model="password" x-ref="pInput" placeholder="Ketik kata sandi..." 
                                       class="w-full pl-10 pr-10 py-2.5 font-mono bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <button type="button" @click="copyPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Password">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                                </button>
                            </div>
                            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        {{-- Router NAS --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Router NAS Mikrotik
                            </label>
                            <select wire:model="router_id" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                <option value="">-- Pilih Router NAS --</option>
                                @foreach($routers as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->ip_address }})</option>
                                @endforeach
                            </select>
                            @error('router_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Profil / Paket Internet --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Profil / Paket Internet
                            </label>
                            <select wire:model="service_profile_id" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                <option value="">-- Pilih Paket Internet --</option>
                                @foreach($serviceProfiles as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->name }} ({{ $p->download_speed }}/{{ $p->upload_speed }} Mbps) - Rp {{ number_format($p->base_price ?: 0, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_profile_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Lanjutan Jaringan Header --}}
                        <div class="md:col-span-2 pt-2">
                            <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 pb-2">
                                Konfigurasi Jaringan & Perangkat (ODP & ONU)
                            </h4>
                        </div>

                        {{-- Static IP --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                IP Address Khusus (Static IP)
                            </label>
                            <input type="text" wire:model="static_ip" placeholder="Kosongkan untuk otomatis dari IP Pool" 
                                   class="w-full px-4 py-2.5 font-mono bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            @error('static_ip') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- MAC Address / Caller ID --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                MAC Address / Caller ID Lock
                            </label>
                            <input type="text" wire:model="mac_address" placeholder="Contoh: 00:11:22:33:44:55" 
                                   class="w-full px-4 py-2.5 font-mono bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            @error('mac_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- ODP & Port Number --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                ODP & Port
                            </label>
                            <div class="flex gap-2">
                                <select wire:model="odp_id" 
                                        class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                    <option value="">-- Pilih ODP --</option>
                                    @foreach($odps as $odp)
                                        <option value="{{ $odp->id }}">{{ $odp->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" wire:model="port_number" placeholder="Port" title="Port Number" 
                                       class="w-24 px-3 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            </div>
                            @error('odp_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            @error('port_number') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- ONU / Serial Number ONT --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Serial Number Modem (ONU / ONT)
                                </label>
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">GenieACS TR-069</span>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">developer_board</span>
                                </span>
                                <input type="text" 
                                       wire:model="onu_id" 
                                       list="available-onus-list"
                                       placeholder="Ketik SN contoh: ZTEGC1234567, FHTT980E4928" 
                                       class="w-full pl-10 pr-4 py-2.5 font-mono uppercase bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <datalist id="available-onus-list">
                                    @foreach($availableOnus as $onuItem)
                                        <option value="{{ $onuItem->serial_number }}">{{ $onuItem->name }} ({{ $onuItem->model ?: 'ONT' }})</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Masukkan Serial Number modem ONT untuk mengaktifkan pemantauan sinyal & tombol ONU GenieACS.
                            </p>
                            @error('onu_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Tagihan & Status --}}
                <div>
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700 text-amber-500 dark:text-amber-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">payments</span>
                        <h3 class="text-sm font-bold uppercase tracking-wider">3. Tagihan & Status Pelanggan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Status Pelanggan --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Status Pelanggan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="status" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                                <option value="suspended">Dibatalkan / Suspend</option>
                            </select>
                            @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tipe Penagihan --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Siklus Tagihan (Billing Cycle)
                            </label>
                            <select wire:model="billing_cycle" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all">
                                <option value="monthly">Bulanan (Standard)</option>
                                <option value="prepaid">Prabayar (Bayar di Depan)</option>
                                <option value="postpaid">Pascabayar (Pakai Baru Bayar)</option>
                            </select>
                            @error('billing_cycle') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Biaya Instalasi --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Biaya Instalasi / Setup Fee
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-semibold text-xs">Rp</span>
                                <input type="number" wire:model="setup_fee" placeholder="0" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            </div>
                            @error('setup_fee') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- Form Footer Actions --}}
            <div class="bg-slate-50 dark:bg-slate-900/60 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-4">
                <a href="{{ route('reseller-portal.customers.index') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">arrow_back</span>
                    Batal
                </a>

                <div class="flex items-center gap-3">
                    <button type="submit" 
                            wire:loading.attr="disabled" 
                            wire:target="save,update" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-200 dark:shadow-none transition-all cursor-pointer">
                        <span wire:loading.remove wire:target="save,update" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                        <span wire:loading wire:target="save,update" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                        <span wire:loading.remove wire:target="save,update">Simpan Perubahan</span>
                        <span wire:loading wire:target="save,update">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
