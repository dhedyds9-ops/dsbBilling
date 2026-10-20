<div class="p-4 sm:p-4 min-h-[calc(100vh-4rem)] relative pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold mb-1 text-slate-800 dark:text-slate-200">Pengaturan Wi-Fi (ONU)</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Ganti nama Wi-Fi (SSID) dan password secara berkala agar lebih aman. Data diambil langsung dari perangkat (GenieACS).</p>
    </div>

    @if ($message)
    <div class="mb-5 p-4 rounded-xl flex items-start gap-3 {{ $messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400' : 'bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400' }}">
        <span class="material-symbols-outlined shrink-0">{{ $messageType === 'success' ? 'check_circle' : 'error' }}</span>
        <div class="text-sm">{{ $message }}</div>
    </div>
    @endif

    @if ($customerServices->isEmpty())
        <div class="text-center text-slate-500 dark:text-slate-400 py-10">Anda tidak memiliki perangkat ONU yang aktif.</div>
    @else
        <div class="space-y-5">
            {{-- Layanan ONU Selection --}}
            <div>
                <label for="onuId" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih Perangkat</label>
                <select id="onuId" wire:model.live="onuId" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer" required>
                    @foreach ($customerServices as $service)
                        <option value="{{ $service->onu_id }}">
                            {{ $service->onu->name ?? 'ONU ' . $service->onu_id }} 
                            ({{ $service->onu->serial_number ?? $service->onu->mac_address ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Loading State from GenieACS --}}
            <div wire:loading wire:target="fetchLiveCredentials, onuId" class="w-full">
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-xl p-4 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined animate-spin text-blue-500 notranslate" translate="no">sync</span>
                    <span class="text-sm text-blue-700 dark:text-blue-400 font-medium">Mengambil data dari perangkat...</span>
                </div>
            </div>

            {{-- Form Settings --}}
            <form wire:submit="savePassword" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 sm:p-5 shadow-sm" wire:loading.class="hidden" wire:target="fetchLiveCredentials, onuId">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700/60">Konfigurasi Saat Ini</h2>
                
                {{-- Current SSID / Password Display --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nama Wi-Fi (SSID)</p>
                        <p class="font-mono text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $liveSsid ?? '-' }}</p>
                    </div>
                    <div x-data="{ showLivePass: false }">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Password Saat Ini</p>
                            @if ($livePassword && !in_array($livePassword, ['-', 'Belum diatur', 'Tidak diketahui', 'Gagal memuat']))
                            <button type="button" @click="showLivePass = !showLivePass" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none flex items-center gap-1 text-[11px]">
                                <span class="material-symbols-outlined text-[15px] notranslate" translate="no" x-text="showLivePass ? 'visibility_off' : 'visibility'">visibility</span>
                                <span x-text="showLivePass ? 'Sembunyikan' : 'Lihat'">Lihat</span>
                            </button>
                            @endif
                        </div>
                        <p class="font-mono text-sm font-semibold text-slate-800 dark:text-slate-200">
                            @if ($livePassword && !in_array($livePassword, ['-', 'Belum diatur', 'Tidak diketahui', 'Gagal memuat']))
                                <span x-show="showLivePass">{{ $livePassword }}</span>
                                <span x-show="!showLivePass">••••••••</span>
                            @else
                                {{ $livePassword ?? '-' }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- Nama Wi-Fi Baru --}}
                    <div>
                        <label for="newSsid" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Ubah Nama Wi-Fi (SSID)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px] notranslate" translate="no">wifi</span>
                            </div>
                            <input type="text" id="newSsid" wire:model="newSsid" 
                                class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" 
                                required minlength="3" maxlength="32" placeholder="Masukkan nama Wi-Fi baru">
                        </div>
                        @error('newSsid') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Password Wi-Fi Baru --}}
                    <div x-data="{ show: false }">
                        <label for="newPassword" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Ubah Password Wi-Fi <span class="font-normal text-slate-400">(Opsional)</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px] notranslate" translate="no">lock</span>
                            </div>
                            <input :type="show ? 'text' : 'password'" id="newPassword" wire:model="newPassword" 
                                class="w-full pl-11 pr-12 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 font-mono" 
                                minlength="8" placeholder="Kosongkan jika tidak ingin diubah">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" @click="show = !show" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none p-1 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px] notranslate" translate="no" x-show="!show">visibility</span>
                                    <span class="material-symbols-outlined text-[20px] notranslate" translate="no" x-show="show" x-cloak>visibility_off</span>
                                </button>
                            </div>
                        </div>
                        @error('newPassword') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md shadow-teal-500/20 transition-all focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px;" wire:loading.remove wire:target="savePassword">save</span>
                            <span class="material-symbols-outlined animate-spin notranslate" translate="no" style="font-size: 20px;" wire:loading wire:target="savePassword">sync</span>
                            <span wire:loading.remove wire:target="savePassword">Simpan Perubahan</span>
                            <span wire:loading wire:target="savePassword">Menyimpan ke Perangkat...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
