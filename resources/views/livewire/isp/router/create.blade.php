@section('page_title')
    <a href="{{ route('isp.routers.index') }}" class="p-1.5 mr-2 text-indigo-100 hover:text-white rounded-lg hover:bg-indigo-700/50 transition-colors" title="Kembali">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
    </a>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
    <span class="text-lg">Tambah Router MikroTik Baru</span>
@endsection

<div class="space-y-5 pb-10 max-w-4xl">
    {{-- SESSION FLASH & VALIDATION ALERTS --}}
    @include('components.alerts')

    {{-- CARD FORM UTAMA --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            {{-- BAGIAN 1: INFORMASI UTAMA ROUTER --}}
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-slate-700 gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" style="font-size:20px">router</span>
                            Informasi Perangkat &amp; Koneksi MikroTik
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Isi data pokok berikut untuk menghubungkan router MikroTik ke billing via API.</p>
                    </div>
                    <button type="button" wire:click="openScriptModal" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/60 rounded-lg text-xs font-semibold transition-colors inline-flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">terminal</span>
                        Skrip Cepat MikroTik
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Nama Router --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Router <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live.debounce.400ms="name" placeholder="Contoh: Router Utama Core" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Kode Router --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Kode Router <span class="text-red-500">*</span></label>
                            <button type="button" wire:click="generateCode" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined notranslate" style="font-size:14px">refresh</span> Auto-generate
                            </button>
                        </div>
                        <input type="text" wire:model="code" placeholder="Contoh: RTR-CORE-01" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- IP Address --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">IP Address / Hostname <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="ip_address" placeholder="Contoh: 192.168.10.1 atau vpn.domain.com" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('ip_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- API Port --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">API Port <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="api_port" placeholder="8728" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        <span class="text-xs text-slate-400 mt-1 block">Default standar: <code>8728</code> (atau <code>8729</code> untuk API-SSL)</span>
                        @error('api_port') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- API Username --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">API Username</label>
                        <input type="text" wire:model="username" placeholder="Contoh: api.dsb.core (kosongkan untuk auto)" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('username') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- API Password --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">API Password</label>
                        <input type="password" wire:model="password" placeholder="•••••••• (kosongkan untuk auto)" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Versi RouterOS --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Versi RouterOS <span class="text-slate-400 font-normal text-xs ml-1">(Terdeteksi saat uji)</span></label>
                        <select wire:model="routeros_version" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Pilih Versi (Otomatis jika kosong) --</option>
                            <option value="v7">RouterOS v7 (Rekomendasi)</option>
                            <option value="v6">RouterOS v6</option>
                        </select>
                    </div>

                    {{-- POP / Wilayah --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">POP / Wilayah <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                        <select wire:model="pop_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Tanpa POP / Belum Ditentukan --</option>
                            @foreach($pops as $pop)
                                <option value="{{ $pop->id }}">{{ $pop->name }} ({{ $pop->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vendor Perangkat --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor Perangkat <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                        <select wire:model="vendor_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Default (MikroTik) --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Router --}}
                    <div class="flex items-center gap-4 pt-1">
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Status Router:</label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" wire:model="status" value="active" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-emerald-700 dark:text-emerald-400 font-medium">Aktif</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" wire:model="status" value="inactive" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-slate-500 font-medium">Nonaktif</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- LIVE FEEDBACK HASIL TEST KONEKSI --}}
            @if($testConnectionStatus === 'success' && $connectedDevice)
                <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined notranslate text-emerald-600 dark:text-emerald-400 mt-0.5" style="font-size:22px">check_circle</span>
                    <div class="flex-1">
                        <h4 class="font-bold text-emerald-800 dark:text-emerald-300 text-sm">MikroTik API Terhubung Berhasil!</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2 text-xs text-emerald-700 dark:text-emerald-300">
                            <div><span class="text-emerald-600 dark:text-emerald-400 font-medium">Identity:</span> {{ $connectedDevice['identity'] ?: '-' }}</div>
                            <div><span class="text-emerald-600 dark:text-emerald-400 font-medium">Board:</span> {{ $connectedDevice['board'] ?: '-' }}</div>
                            <div><span class="text-emerald-600 dark:text-emerald-400 font-medium">Versi:</span> RouterOS v{{ $connectedDevice['version'] ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            @elseif($testConnectionStatus === 'error')
                <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined notranslate text-red-600 dark:text-red-400 mt-0.5" style="font-size:22px">error</span>
                    <div class="flex-1">
                        <h4 class="font-bold text-red-800 dark:text-red-300 text-sm">Uji Koneksi Gagal</h4>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-1 leading-relaxed">{{ $testConnectionMessage }}</p>
                    </div>
                </div>
            @endif

            {{-- ACCORDION TOGGLE: PENGATURAN LANJUTAN (OPSIONAL) --}}
            <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-all">
                <button type="button" wire:click="toggleAdvanced" class="w-full flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-left transition-colors cursor-pointer">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined notranslate text-slate-500" style="font-size:20px">tune</span>
                        <div>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Pengaturan Lanjutan &amp; Kredensial (Opsional)</span>
                            <p class="text-xs text-slate-400 dark:text-slate-500">RADIUS Secret, Enkripsi SSL/TLS, Timeout, Model Perangkat, dan Catatan.</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined notranslate text-slate-400 transition-transform duration-200 {{ $showAdvanced ? 'rotate-180' : '' }}" style="font-size:20px">
                        expand_more
                    </span>
                </button>

                @if($showAdvanced)
                <div class="p-5 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 space-y-5">
                    {{-- Section A: Enkripsi & Timeout --}}
                    <div>
                        <h4 class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate" style="font-size:16px">security</span>
                            Keamanan &amp; Timeout API
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" wire:model="use_ssl" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">Gunakan SSL / TLS</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Aktifkan enkripsi jika router mendukung API-SSL (Port 8729)</div>
                                    </div>
                                </label>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Timeout Koneksi (detik)</label>
                                <input type="number" wire:model="timeout" placeholder="30" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                        </div>
                    </div>

                    {{-- Section B: RADIUS Secret --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60">
                        <h4 class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate" style="font-size:16px">vpn_key</span>
                            Kredensial RADIUS Server
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">RADIUS Secret</label>
                                <input type="text" wire:model="radius_secret" placeholder="Kosongkan untuk auto-generate acak" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                <span class="text-[11px] text-slate-400 mt-1 block">Digunakan oleh FreeRADIUS untuk autentikasi user PPPoE &amp; Hotspot.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Section C: Detail Hardware & Lokasi Fisik --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60">
                        <h4 class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate" style="font-size:16px">info</span>
                            Detail Hardware &amp; Lokasi Fisik
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Model Perangkat</label>
                                <input type="text" wire:model="model" placeholder="Misal: RB4011iGS+, CCR2004" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Serial Number</label>
                                <input type="text" wire:model="serial_number" placeholder="Contoh: B9A10E12345" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Lokasi Rak</label>
                                <textarea wire:model="description" rows="2" placeholder="Catatan lokasi penempatan, server room, atau informasi tambahan..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- TOMBOL AKSI BAWAH --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.routers.index') }}" wire:navigate class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                    Batal
                </a>
                <button type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection" class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/60 rounded-xl text-sm font-semibold transition-colors inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="testConnection" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">network_check</span>
                    <span wire:loading wire:target="testConnection" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                    <span wire:loading.remove wire:target="testConnection">Test Koneksi</span>
                    <span wire:loading wire:target="testConnection">Memeriksa...</span>
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2 min-w-[130px]">
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- MODAL SKRIP CEPAT MIKROTIK (QUICK ONBOARDING) --}}
    @if($showScriptModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/80 transition-opacity backdrop-blur-sm" wire:click="closeScriptModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-between items-center">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">terminal</span>
                            Skrip Cepat MikroTik (Quick Setup)
                        </h3>
                        <button type="button" wire:click="closeScriptModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <span class="material-symbols-outlined notranslate text-[20px]" translate="no">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Mirip dengan fitur di MixRadius/MSRadius, skrip ini otomatis mengaktifkan service API, membuat user API aman, mengonfigurasi RADIUS Client (PPPoE &amp; Hotspot), serta membuka port firewall MikroTik.
                        </p>

                        <!-- Pilihan Versi RouterOS & IP RADIUS -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Versi RouterOS MikroTik</label>
                                <select wire:model.live="scriptVersion" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-900 dark:text-slate-100">
                                    <option value="v7">RouterOS v7 (Default)</option>
                                    <option value="v6">RouterOS v6</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">IP Server dsBilling / RADIUS</label>
                                <input type="text" wire:model.live="customRadiusIp" placeholder="192.168.x.x atau IP Server" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-900 dark:text-slate-100">
                            </div>
                        </div>

                        <!-- Code Block -->
                        <div class="relative bg-slate-950 rounded-xl p-4 border border-slate-800 shadow-inner group">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 border-b border-slate-800 pb-2 mb-2">
                                <span>Perintah Terminal WinBox:</span>
                                <button type="button" wire:click="regenerateCredentials" class="text-indigo-400 hover:text-indigo-300 flex items-center gap-1 font-semibold cursor-pointer">
                                    <span class="material-symbols-outlined notranslate text-[14px]" translate="no">refresh</span>
                                    Acak Kredensial Baru
                                </button>
                            </div>
                            <pre class="text-emerald-400 text-xs font-mono whitespace-pre-wrap select-all overflow-x-auto max-h-56 leading-relaxed">{{ $generatedScript }}</pre>
                            
                            <div class="absolute top-3 right-3 opacity-90 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="navigator.clipboard.writeText(`{{ $generatedScript }}`); alert('Skrip berhasil disalin ke clipboard!');" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm cursor-pointer">
                                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">content_copy</span>
                                    Salin Skrip
                                </button>
                            </div>
                        </div>

                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 flex items-start gap-2 text-xs text-amber-800 dark:text-amber-300">
                            <span class="material-symbols-outlined notranslate mt-0.5 text-[18px]" translate="no">info</span>
                            <span><strong>Langkah selanjutnya:</strong> Buka WinBox &gt; <strong>New Terminal</strong> &gt; Paste skrip di atas. Setelah selesai, klik tombol <strong>"Terapkan ke Form"</strong> di bawah lalu klik <strong>"Test Koneksi"</strong>.</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <button type="button" wire:click="closeScriptModal" class="px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition-colors cursor-pointer">
                            Tutup
                        </button>
                        <button type="button" wire:click="applyScriptCredentials" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">check</span>
                            Terapkan ke Form &amp; Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>