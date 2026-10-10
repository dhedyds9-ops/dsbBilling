<div class="max-w-4xl mx-auto pb-10">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Router</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ubah konfigurasi dan akses API router MikroTik Anda.</p>
        </div>
        <a href="{{ route('isp.routers.show', $router->id) }}" wire:navigate class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined notranslate text-[18px]" translate="no">arrow_back</span>
            Batal & Kembali
        </a>
    </div>

    @include('components.alerts')

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative">
        <div wire:loading wire:target="save, testConnection" class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm z-50 flex flex-col items-center justify-center">
            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">Memproses...</p>
        </div>

        <form wire:submit.prevent="save" class="p-6 md:p-8">
            <div class="mb-8">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Router <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Misal: Router Utama Jakarta" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
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
                </div>
            </div>

            <hr class="border-slate-200 dark:border-slate-700 mb-8">
            
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Pengaturan Koneksi API</h3>
                
                @if($testConnectionStatus)
                    <div class="mb-6 p-4 rounded-xl border {{ $testConnectionStatus === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400' }} flex items-start gap-3">
                        <span class="material-symbols-outlined notranslate mt-0.5" translate="no">{{ $testConnectionStatus === 'success' ? 'check_circle' : 'error' }}</span>
                        <div>
                            <h4 class="font-bold text-sm">{{ $testConnectionStatus === 'success' ? 'Koneksi Berhasil' : 'Koneksi Gagal' }}</h4>
                            <p class="text-xs mt-1 opacity-90">{{ $testConnectionMessage }}</p>
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">IP Address <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="ip_address" placeholder="192.168.x.x atau IP Publik" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Port <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="api_port" placeholder="8728" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Username <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="username" placeholder="Tetap sama jika kosong" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Password <span class="text-red-500">*</span></label>
                        <input type="password" wire:model="password" placeholder="Biarkan kosong jika tidak diubah" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
                    </div>

                    <div class="md:col-span-2 pt-2 flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="use_ssl" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900">
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">Gunakan SSL/TLS</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Aktifkan jika router mendukung API-SSL (Port 8729)</div>
                            </div>
                        </label>
                        
                        <button type="button" wire:click="testConnection" class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">wifi_tethering</span>
                            Tes Koneksi
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-8 mt-8 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.routers.show', $router->id) }}" wire:navigate class="px-6 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>