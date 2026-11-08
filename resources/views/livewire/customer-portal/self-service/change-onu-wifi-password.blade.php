<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] relative pb-24 max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-2xl">wifi_settings</span>
                <span>Pusat Kendali & Pengaturan Wi-Fi</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Atur nama WiFi, sandi keamanan, frekuensi dual-band (2.4G & 5G), serta sembunyikan SSID langsung ke modem Anda via TR-069.
            </p>
        </div>

        @if ($customerServices->count() > 1)
        <div class="w-full sm:w-auto">
            <select id="onuId" wire:model.live="onuId" class="w-full sm:w-64 px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-teal-500 cursor-pointer shadow-sm">
                @foreach ($customerServices as $service)
                    <option value="{{ $service->onu_id }}">
                        {{ $service->onu?->name ?? 'Modem #' . $service->onu_id }} ({{ $service->onu?->serial_number ?? 'ONT' }})
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <!-- Alert Notifikasi -->
    @if ($message)
    <div class="p-4 rounded-2xl flex items-start gap-3 shadow-sm transition-all {{ $messageType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300' : 'bg-red-50 text-red-800 border border-red-200 dark:bg-red-950/40 dark:border-red-800 dark:text-red-300' }}">
        <span class="material-symbols-outlined shrink-0 text-xl">{{ $messageType === 'success' ? 'check_circle' : 'error' }}</span>
        <div class="text-xs sm:text-sm font-medium leading-relaxed">{{ $message }}</div>
    </div>
    @endif

    @if ($customerServices->isEmpty())
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl p-10 text-center text-slate-500 dark:text-slate-400 shadow-sm">
            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 mb-2">wifi_off</span>
            <p class="font-bold text-slate-700 dark:text-slate-300">Tidak Ada Perangkat Modem Aktif</p>
            <p class="text-xs mt-1">Layanan internet Anda belum memiliki perangkat ONT yang terhubung.</p>
        </div>
    @else
        <!-- Loading Indicator -->
        <div wire:loading wire:target="fetchLiveCredentials, onuId, switchWlan" class="w-full">
            <div class="bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 rounded-2xl p-4 flex items-center justify-center gap-3 shadow-sm">
                <span class="material-symbols-outlined animate-spin text-teal-600 dark:text-teal-400 text-xl notranslate" translate="no">sync</span>
                <span class="text-xs sm:text-sm text-teal-800 dark:text-teal-300 font-semibold">Sinkronisasi data langsung dengan modem pelanggan via GenieACS...</span>
            </div>
        </div>

        <div wire:loading.class="opacity-50 pointer-events-none" wire:target="fetchLiveCredentials, onuId, switchWlan" class="space-y-6">

            <!-- Tab Pilihan Frekuensi Band (Dual-Band) persis genieacs-mobile -->
            <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800/80 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700/60 overflow-x-auto">
                <button type="button" wire:click="switchWlan(1)" 
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 {{ $activeWlanIndex === 1 ? 'bg-white dark:bg-slate-900 text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="material-symbols-outlined text-lg">wifi</span>
                    <span>Wi-Fi 2.4 GHz (Utama)</span>
                </button>
                <button type="button" wire:click="switchWlan(5)" 
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 {{ $activeWlanIndex === 5 ? 'bg-white dark:bg-slate-900 text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="material-symbols-outlined text-lg">speed</span>
                    <span>Wi-Fi 5 GHz (High Speed)</span>
                </button>
            </div>

            <!-- Status Aktif Saat Ini Card -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-5 sm:p-6 shadow-xl relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 opacity-10 pointer-events-none">
                    <span class="material-symbols-outlined text-[160px]">router</span>
                </div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-400/30 text-teal-200 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                            <span>Frekuensi {{ $activeWlanIndex === 1 ? '2.4 GHz' : '5 GHz' }} Aktif</span>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">Index WLAN: {{ $activeWlanIndex }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-wider mb-1">Nama Wi-Fi (SSID)</p>
                            <p class="font-mono text-base font-bold text-white tracking-wide truncate">{{ $liveSsid ?? '-' }}</p>
                        </div>
                        <div x-data="{ showLivePass: false }" class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Sandi Saat Ini</p>
                                @if ($livePassword && !in_array($livePassword, ['-', 'Belum diatur', 'Tidak diketahui', 'Gagal memuat']))
                                <button type="button" @click="showLivePass = !showLivePass" class="text-teal-300 hover:text-teal-200 text-[11px] font-semibold flex items-center gap-1 focus:outline-none">
                                    <span class="material-symbols-outlined text-[14px]" x-text="showLivePass ? 'visibility_off' : 'visibility'">visibility</span>
                                    <span x-text="showLivePass ? 'Tutup' : 'Lihat'">Lihat</span>
                                </button>
                                @endif
                            </div>
                            <p class="font-mono text-base font-bold text-white tracking-wide">
                                @if ($livePassword && !in_array($livePassword, ['-', 'Belum diatur', 'Tidak diketahui', 'Gagal memuat']))
                                    <span x-show="showLivePass">{{ $livePassword }}</span>
                                    <span x-show="!showLivePass">••••••••</span>
                                @else
                                    {{ $livePassword ?? '-' }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Pengaturan Lengkap -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-3xl p-5 sm:p-7 shadow-sm space-y-6">

                <!-- 1. Sembunyikan SSID Toggle Switch -->
                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                    <div class="space-y-0.5 pr-4">
                        <label for="switchHidden" class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-lg">visibility_off</span>
                            <span>Sembunyikan Nama Wi-Fi (Hidden SSID)</span>
                        </label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Jika diaktifkan, orang lain tidak bisa melihat nama WiFi Anda di daftar WiFi HP kecuali memasukkan nama secara manual.
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" id="switchHidden" wire:model.live="isHidden" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-teal-600"></div>
                    </label>
                </div>

                <!-- 2. Ubah Nama Wi-Fi (SSID) -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <label for="newSsid" class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-lg">edit</span>
                            <span>Nama Wi-Fi Baru (SSID)</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Minimal 3, Maksimal 32 Karakter</span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[18px]">wifi</span>
                            </div>
                            <input type="text" id="newSsid" wire:model="newSsid" 
                                class="w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 font-medium" 
                                placeholder="Contoh: WiFi-Keluarga-5G" required>
                        </div>
                        <button type="button" wire:click="saveSsidOnly" wire:loading.attr="disabled"
                            class="px-5 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-2 shrink-0">
                            <span wire:loading.remove wire:target="saveSsidOnly" class="material-symbols-outlined text-base">save</span>
                            <span wire:loading wire:target="saveSsidOnly" class="material-symbols-outlined text-base animate-spin">sync</span>
                            <span>Simpan Nama Saja</span>
                        </button>
                    </div>
                    @error('newSsid') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 my-4"></div>

                <!-- 3. Mode Keamanan & Sandi (Security Mode Dropdown) -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Mode Keamanan Dropdown persis genieacs-mobile -->
                        <div>
                            <label for="securityMode" class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-lg">shield</span>
                                <span>Mode Keamanan Wi-Fi</span>
                            </label>
                            <select id="securityMode" wire:model.live="securityMode" 
                                class="w-full px-3.5 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer font-medium">
                                <option value="WPA_WPA2">🛡️ WPA/WPA2-PSK Mixed (Sangat Direkomendasikan)</option>
                                <option value="WPA2">🛡️ WPA2-PSK (AES) - Standar Aman</option>
                                <option value="WPA">🔑 WPA-PSK (TKIP) - Kompatibilitas Perangkat Lawas</option>
                                <option value="OPEN">🔓 Open System (Tanpa Sandi / Terbuka)</option>
                            </select>
                        </div>

                        <!-- Input Password Baru -->
                        <div x-data="{ show: false }">
                            <label for="newPassword" class="block text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-lg">lock</span>
                                    <span>Sandi Wi-Fi Baru</span>
                                </span>
                                @if ($securityMode !== 'OPEN')
                                <span class="text-[11px] font-normal text-slate-400">Min. 8 Karakter</span>
                                @endif
                            </label>

                            @if ($securityMode === 'OPEN')
                            <div class="px-4 py-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs text-amber-800 dark:text-amber-300 font-medium flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-amber-600">lock_open</span>
                                <span>Mode Tanpa Sandi aktif. Siapa pun dapat terhubung tanpa password.</span>
                            </div>
                            @else
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <span class="material-symbols-outlined text-[18px]">key</span>
                                </div>
                                <input :type="show ? 'text' : 'password'" id="newPassword" wire:model="newPassword" 
                                    class="w-full pl-10 pr-11 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 font-mono" 
                                    minlength="8" placeholder="Masukkan minimal 8 karakter sandi baru">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" @click="show = !show" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none p-1">
                                        <span class="material-symbols-outlined text-[18px]" x-show="!show">visibility</span>
                                        <span class="material-symbols-outlined text-[18px]" x-show="show" x-cloak>visibility_off</span>
                                    </button>
                                </div>
                            </div>
                            @endif
                            @error('newPassword') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" wire:click="saveSecurityOnly" wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="saveSecurityOnly" class="material-symbols-outlined text-base">security</span>
                            <span wire:loading wire:target="saveSecurityOnly" class="material-symbols-outlined text-base animate-spin">sync</span>
                            <span>Simpan Sandi & Keamanan Saja</span>
                        </button>
                    </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Setelah disimpan, modem akan otomatis merestart modul WiFi dalam 3-5 detik.
                    </p>
                    <button type="button" wire:click="savePassword" wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-extrabold text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="savePassword" class="material-symbols-outlined text-lg">check_circle</span>
                        <span wire:loading wire:target="savePassword" class="material-symbols-outlined text-lg animate-spin">sync</span>
                        <span>Simpan Semua Konfigurasi</span>
                    </button>
                </div>
            </div>

            <!-- 4. Daftar Perangkat yang Sedang Terhubung (Connected Devices / LAN Hosts) -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-xl">devices</span>
                            <span>Perangkat Terhubung ke Wi-Fi ({{ count($connectedDevices) }})</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar smartphone, laptop, dan smart TV yang sedang aktif tersambung ke modem Anda.</p>
                    </div>
                    <button type="button" wire:click="fetchLiveCredentials" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition text-xs font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">refresh</span>
                        <span class="hidden sm:inline">Segarkan</span>
                    </button>
                </div>

                @if (!empty($connectedDevices))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    @foreach ($connectedDevices as $dev)
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 truncate">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200/60 dark:border-teal-800/40 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-xl">smartphone</span>
                            </div>
                            <div class="truncate">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $dev['hostname'] ?? $dev['name'] ?? 'Perangkat Nirkabel' }}</p>
                                <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400">{{ $dev['ip'] ?? $dev['ip_address'] ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                Aktif
                            </span>
                            <p class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $dev['mac'] ?? $dev['mac_address'] ?? '' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-1">wifi_off</span>
                    <p>Belum ada data perangkat yang terhubung atau modem sedang offline.</p>
                </div>
                @endif
            </div>

        </div>
    @endif
</div>
