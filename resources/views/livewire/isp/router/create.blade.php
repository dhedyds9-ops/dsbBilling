<div class="max-w-4xl mx-auto pb-10">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tambah Router Baru</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tambahkan router MikroTik baru untuk mulai memanajemen bandwidth dan hotspot.</p>
        </div>
        <a href="{{ route('isp.routers.index') }}" wire:navigate class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined notranslate text-[18px]" translate="no">arrow_back</span>
            Kembali ke Daftar
        </a>
    </div>

    @include('components.alerts')

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative">
        <div wire:loading wire:target="save, generateCredentials, runDiagnosticTest" class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm z-50 flex flex-col items-center justify-center">
            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">Memproses...</p>
        </div>

        <form wire:submit.prevent="save" class="p-6 md:p-8">
            <div class="mb-8">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Versi RouterOS <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                        <select wire:model="routeros_version" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
                            <option value="">Pilih Versi (Otomatis jika kosong)</option>
                            <option value="v7">RouterOS v7 (Rekomendasi)</option>
                            <option value="v6">RouterOS v6</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-slate-200 dark:border-slate-700 mb-8">
            
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Pengaturan Koneksi API</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">IP Address <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="ip_address" placeholder="192.168.x.x atau IP Publik" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                        @error('ip_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Port <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="api_port" placeholder="8728" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono">
                        @error('api_port') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Username</label>
                        <input type="text" wire:model="username" placeholder="Kosongkan untuk auto-generate" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Password</label>
                        <input type="password" wire:model="password" placeholder="Kosongkan untuk auto-generate" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100">
                    </div>

                    <div class="md:col-span-2 pt-2">
                        <label class="flex items-center gap-3 cursor-pointer group w-max">
                            <input type="checkbox" wire:model="use_ssl" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900">
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">Gunakan SSL/TLS</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Aktifkan jika router mendukung API-SSL (Port 8729)</div>
                            </div>
                        </label>
                    </div>
                </div>
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
</div>