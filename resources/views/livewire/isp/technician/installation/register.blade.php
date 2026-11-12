<div class="space-y-3.5 pb-6">
    @section('header_title', 'Registrasi ONU ke OLT')
    <div class="bg-indigo-600 text-white p-3.5 sm:p-4 rounded-2xl shadow-md flex justify-between items-center gap-3">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-lg sm:text-xl">add_circle</span>
                <h2 class="text-base sm:text-lg font-black">Registrasi ONU ke OLT</h2>
            </div>
            <p class="text-xs text-indigo-100">Daftarkan modem/ONU baru ke sistem database dan perangkat OLT.</p>
        </div>
        <a href="{{ route('technician.installation.scan') }}" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-1 shrink-0">
            <span class="material-symbols-outlined text-sm" translate="no">arrow_back</span>
            <span>Scan</span>
        </a>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 bg-emerald-100 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-start gap-3">
            <span class="material-symbols-outlined notranslate" translate="no">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit="registerOnu" class="p-4">
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


