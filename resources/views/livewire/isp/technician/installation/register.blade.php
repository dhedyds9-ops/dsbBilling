<div class="p-4 pb-24">
    <div class="mb-6 flex flex-col gap-3 justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">add_circle</span>
                Registrasi ONU ke OLT
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftarkan modem/ONU baru ke sistem database dan perangkat OLT.</p>
        </div>
        <div>
            <a href="{{ route('technician.installation.scan') }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">arrow_back</span>
                Kembali ke Scan
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 bg-emerald-100 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-start gap-3">
            <span class="material-symbols-outlined notranslate" translate="no">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit="registerOnu" class="p-6">
            <div class="flex flex-col gap-6">
                <!-- Data Perangkat -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">router</span>
                        Identitas Perangkat (ONU)
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Serial Number (SN) *</label>
                            <input wire:model="serialNumber" type="text" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white uppercase font-mono">
                            @error('serialNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">MAC Address (Opsional)</label>
                            <input wire:model="macAddress" type="text" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white uppercase font-mono" placeholder="XX:XX:XX:XX:XX:XX">
                            @error('macAddress') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Jaringan & Profil -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">settings_ethernet</span>
                        Konfigurasi Jaringan
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih OLT Utama *</label>
                            <select wire:model="selectedOlt" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                                <option value="">-- Pilih Perangkat OLT --</option>
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}">{{ $olt->name }} ({{ $olt->ip_address }})</option>
                                @endforeach
                            </select>
                            @error('selectedOlt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Titik ODP *</label>
                            <select wire:model="selectedOdp" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                                <option value="">-- Pilih ODP --</option>
                                @foreach($odps as $odp)
                                    <option value="{{ $odp->id }}">{{ $odp->code }} - {{ $odp->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedOdp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Line Profile (Bandwidth) *</label>
                            <select wire:model="profileName" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                                <option value="">-- Pilih Line Profile OLT --</option>
                                <option value="10M">10 Mbps Profile</option>
                                <option value="20M">20 Mbps Profile</option>
                                <option value="30M">30 Mbps Profile</option>
                                <option value="50M">50 Mbps Profile</option>
                                <option value="100M">100 Mbps Profile</option>
                            </select>
                            @error('profileName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="registerOnu" class="material-symbols-outlined notranslate text-sm" translate="no">save</span>
                    <span wire:loading wire:target="registerOnu" class="material-symbols-outlined notranslate animate-spin text-sm" translate="no">autorenew</span>
                    Simpan & Registrasi ONU
                </button>
            </div>
        </form>
    </div>
</div>


