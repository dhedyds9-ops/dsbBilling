<div class="space-y-6">
    <x-admin.page-header title="Aktivasi Lisensi dsBilling" subtitle="Verifikasi lisensi dsBilling Anda.">
    </x-admin.page-header>
    <x-admin.breadcrumbs :breadcrumbs="$breadcrumbs" />

    <div class="p-4 sm:p-6 pb-24 lg:pb-6">
        <div class="max-w-3xl mx-auto mt-4">
            <!-- Flash Messages -->
            @if(session()->has('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900/30 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            @if(session()->has('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-red-900/30 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Status Lisensi dsBilling</h3>
                    
                    @if($isValid)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                            Tidak Valid
                        </span>
                    @endif
                </div>
                
                <div class="p-6">
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                        Silakan masukkan <strong>License Key</strong> yang Anda dapatkan saat membeli dsBilling. Lisensi ini mengikat pada Hardware ID server Anda saat ini untuk mencegah pembajakan.
                    </p>

                    <form wire:submit.prevent="activate" class="space-y-5">
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Hardware ID (Otomatis)</label>
                            <input type="text" readonly value="{{ $hardwareId }}" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-md bg-slate-100 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 font-mono text-sm focus:outline-none cursor-not-allowed dark:bg-slate-900 dark:text-slate-100">
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Kirimkan ID ini kepada pengembang jika Anda perlu mereset lisensi akibat pindah server.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">License Key</label>
                            <input type="text" wire:model="licenseKey" placeholder="XXXXX-XXXXX-XXXXX-XXXXX" class="w-full px-3 py-2 border @error('licenseKey') border-red-500 @else border-slate-200 dark:border-slate-700 @enderror rounded-md bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono focus:ring-2 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                            @error('licenseKey') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                Validasi & Aktifkan Lisensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-6 text-center">
                <p class="text-xs text-slate-500 dark:text-slate-500 dark:text-slate-400">
                    Butuh bantuan- Silakan hubungi tim dukungan dsBilling.
                </p>
            </div>
        </div>
    </div>
</div>






