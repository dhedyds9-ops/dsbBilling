<div class="max-w-4xl mx-auto pb-10">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">router</span>
                Tambah Router Baru
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tambahkan router MikroTik baru untuk memanajemen bandwidth, hotspot voucher, dan PPPoE.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" wire:click="openScriptModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">terminal</span>
                Skrip Cepat MikroTik
            </button>
            <a href="{{ route('isp.routers.index') }}" wire:navigate class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">arrow_back</span>
                Kembali
            </a>
        </div>
    </div>

    @include('components.alerts')

    <!-- Hasil Tes Koneksi -->
    @if($testConnectionStatus)
        <div class="mb-6 p-4 rounded-xl border {{ $testConnectionStatus === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300' }} flex items-start gap-3 shadow-sm transition-all">
            <span class="material-symbols-outlined notranslate mt-0.5 text-[22px]" translate="no">{{ $testConnectionStatus === 'success' ? 'check_circle' : 'error' }}</span>
            <div class="flex-1">
                <h4 class="font-bold text-sm">{{ $testConnectionStatus === 'success' ? 'Uji Koneksi Berhasil!' : 'Uji Koneksi Gagal' }}</h4>
                <p class="text-xs mt-1 leading-relaxed">{{ $testConnectionMessage }}</p>
                @if($connectedDevice && $testConnectionStatus === 'success')
                    <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs font-mono">
                        @if(!empty($connectedDevice['identity']))
                            <span class="px-2.5 py-1 bg-white/70 dark:bg-slate-800/80 rounded-md border border-emerald-300 dark:border-emerald-700">Identity: {{ $connectedDevice['identity'] }}</span>
                        @endif
                        @if(!empty($connectedDevice['board']))
                            <span class="px-2.5 py-1 bg-white/70 dark:bg-slate-800/80 rounded-md border border-emerald-300 dark:border-emerald-700">Board: {{ $connectedDevice['board'] }}</span>
                        @endif
                        @if(!empty($connectedDevice['version']))
                            <span class="px-2.5 py-1 bg-white/70 dark:bg-slate-800/80 rounded-md border border-emerald-300 dark:border-emerald-700">RouterOS: v{{ $connectedDevice['version'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
            <button type="button" wire:click="$set('testConnectionStatus', null)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">close</span>
            </button>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative">
        <div wire:loading wire:target="save, testConnection" class="absolute inset-0 bg-white/60 dark:bg-slate-900/60 backdrop-blur-sm z-50 flex flex-col items-center justify-center">
            <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Memeriksa koneksi ke MikroTik...</p>
        </div>

        <form wire:submit.prevent="save" class="p-6 md:p-8">
            <!-- Informasi Dasar -->
            <div class="mb-8">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500 text-[18px]" translate="no">tune</span>
                    Informasi Dasar
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Router <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Misal: Router Utama" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select wire:model="status" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
                            <option value="active">Aktif beroperasi</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Versi RouterOS <span class="text-slate-400 font-normal text-xs ml-1">(Otomatis saat uji)</span></label>
                        <select wire:model="routeros_version" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-semibold">
                            <option value="">Pilih Versi (Otomatis)</option>
                            <option value="v7">RouterOS v7 (Rekomendasi)</option>
                            <option value="v6">RouterOS v6</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-slate-200 dark:border-slate-700 mb-8">
            
            <!-- Pengaturan Koneksi API -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500 text-[18px]" translate="no">cable</span>
                        Pengaturan Koneksi API & Kredensial
                    </h3>
                    <button type="button" wire:click="openScriptModal" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 font-semibold">
                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">help</span>
                        Belum punya akun API? Buat Skrip Otomatis
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">IP Address / Hostname <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="ip_address" placeholder="192.168.x.x, IP Publik, atau DNS" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                        @error('ip_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Port <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="api_port" placeholder="8728" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                        @error('api_port') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Username</label>
                        <input type="text" wire:model="username" placeholder="Kosongkan untuk auto-generate" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Password</label>
                        <input type="password" wire:model="password" placeholder="Kosongkan untuk auto-generate" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                    </div>

                    <div class="md:col-span-2 pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="use_ssl" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900">
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">Gunakan SSL/TLS</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Aktifkan jika router mendukung API-SSL (Port 8729)</div>
                            </div>
                        </label>

                        <button type="button" wire:click="testConnection" wire:loading.attr="disabled" class="px-5 py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                            <span class="material-symbols-outlined notranslate text-[18px]" wire:loading.class="animate-spin" wire:target="testConnection" translate="no">network_check</span>
                            Uji Koneksi Sekarang
                        </button>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Cepat Winbox -->
            <div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/30 border border-slate-200 dark:border-slate-700/80 text-xs text-slate-600 dark:text-slate-400">
                <span class="font-bold text-slate-800 dark:text-slate-200">Tips Pengaturan di MikroTik:</span>
                Pastikan service API aktif di Winbox menu <span class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">IP &gt; Services</span> (<code class="text-indigo-600 dark:text-indigo-400 font-semibold">/ip service enable api</code>) dan port {{ $api_port ?: 8728 }} dapat dijangkau dari server ini.
            </div>

            <div class="flex items-center justify-end gap-3 pt-8 mt-8 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.routers.index') }}" wire:navigate class="px-6 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">save</span>
                    Simpan Router
                </button>
            </div>
        </form>
    </div>

    <!-- Modal Skrip Otomatis MikroTik (Quick Setup) -->
    @if($showScriptModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/80 transition-opacity backdrop-blur-sm" wire:click="closeScriptModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                            Skrip Cepat MikroTik (Quick Onboarding)
                        </h3>
                        <button wire:click="closeScriptModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <span class="material-symbols-outlined notranslate text-[20px]" translate="no">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Mirip dengan fitur di MixRadius/MSRadius, skrip ini akan otomatis mengaktifkan API, membuat user khusus dsBilling, mengatur RADIUS Client (PPPoE &amp; Hotspot), serta membuka port firewall MikroTik.
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
                                <span>Perintah CLI MikroTik:</span>
                                <button type="button" wire:click="regenerateCredentials" class="text-indigo-400 hover:text-indigo-300 flex items-center gap-1 font-semibold">
                                    <span class="material-symbols-outlined notranslate text-[14px]" translate="no">refresh</span>
                                    Acak Password Baru
                                </button>
                            </div>
                            <pre class="text-emerald-400 text-xs font-mono whitespace-pre-wrap select-all overflow-x-auto max-h-56 leading-relaxed">{{ $generatedScript }}</pre>
                            
                            <div class="absolute top-3 right-3 opacity-90 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="navigator.clipboard.writeText(`{{ $generatedScript }}`); alert('Skrip berhasil disalin ke clipboard!');" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined notranslate text-[16px]" translate="no">content_copy</span>
                                    Salin Skrip
                                </button>
                            </div>
                        </div>

                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 flex items-start gap-2 text-xs text-amber-800 dark:text-amber-300">
                            <span class="material-symbols-outlined notranslate mt-0.5 text-[18px]" translate="no">info</span>
                            <span><strong>Langkah selanjutnya:</strong> Buka WinBox &gt; <strong>New Terminal</strong> &gt; Paste skrip di atas. Setelah selesai, klik tombol <strong>"Terapkan ke Form"</strong> di bawah lalu klik <strong>"Uji Koneksi"</strong>.</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <button type="button" wire:click="closeScriptModal" class="px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition-colors">
                            Tutup
                        </button>
                        <button type="button" wire:click="applyScriptCredentials" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-1.5 transition-colors">
                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">check</span>
                            Terapkan ke Form &amp; Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>