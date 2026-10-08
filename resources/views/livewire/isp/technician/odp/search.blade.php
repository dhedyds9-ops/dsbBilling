<div>
    <div class="mb-6 flex flex-col gap-3 justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">search</span>
                Pencarian ODP
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Cari dan periksa kapasitas jaringan Optical Distribution Point (ODP).</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6">
        <div class="flex flex-col gap-4">
            <div class="w-full">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Cari Kode / Nama ODP</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no">search</span>
                    </div>
                    <input wire:model.live.debounce.500ms="search" type="text" class="bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5" placeholder="Masukkan kata kunci...">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status Ketersediaan</label>
                <select wire:model.live="availability" class="bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    <option value="">Semua Kapasitas</option>
                    <option value="available">Port Tersedia</option>
                    <option value="full">Penuh (Full)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status Perangkat</label>
                <select wire:model.live="status" class="bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    <option value="">Semua Status</option>
                    <option value="active">Active / Normal</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Grid -->
    <div class="flex flex-col gap-4 relative">
        <div wire:loading class="absolute inset-0 z-10 bg-slate-50/50 dark:bg-slate-900/50 backdrop-blur-sm rounded-xl flex items-center justify-center">
            <span class="material-symbols-outlined notranslate animate-spin text-4xl text-indigo-600" translate="no">autorenew</span>
        </div>
        
        @forelse($odps as $odp)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $odp->code }}</h3>
                                @if($odp->status === 'active')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded dark:bg-emerald-900 dark:text-emerald-300">ACTIVE</span>
                                @else
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded dark:bg-amber-900 dark:text-amber-300">{{ strtoupper($odp->status) }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $odp->name }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full {{ $odp->availablePortCount > 0 ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'bg-red-50 dark:bg-red-900/30 text-red-600' }} flex items-center justify-center font-bold text-xl ring-4 ring-white dark:ring-slate-800 shadow-inner">
                            {{ $odp->availablePortCount }}
                        </div>
                    </div>
                    
                    <div class="space-y-3 mt-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[16px]" translate="no">router</span> Total Port</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $odp->port_count }} Port</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[16px]" translate="no">cable</span> Terpakai</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $odp->used_port_count }} Port</span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-200 rounded-full h-2.5 dark:bg-slate-700 mt-2">
                            <div class="{{ $odp->occupancyPercent >= 100 ? 'bg-red-600' : ($odp->occupancyPercent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }} h-2.5 rounded-full" style="width: {{ $odp->occupancyPercent }}%"></div>
                        </div>
                        <div class="text-xs text-right text-slate-500 mt-1">{{ $odp->occupancyPercent }}% Penuh</div>
                    </div>
                </div>
                
                <div class="bg-slate-50 dark:bg-slate-900/50 p-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <span class="text-xs text-slate-500 flex items-center gap-1 truncate max-w-[200px]" title="{{ $odp->address }}">
                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">location_on</span>
                        {{ $odp->address ?? 'Alamat tidak diisi' }}
                    </span>
                    
                    @if($odp->gpsAvailable)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $odp->latitude }},{{ $odp->longitude }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold flex items-center gap-1 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded">
                            <span class="material-symbols-outlined notranslate text-[14px]" translate="no">navigation</span> Maps
                        </a>
                    @else
                        <span class="text-slate-400 text-xs italic">Tanpa GPS</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-1 w-full xl:col-span-3 py-12 text-center border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl">
                <span class="material-symbols-outlined notranslate text-5xl text-slate-300 dark:text-slate-600 mb-3" translate="no">search_off</span>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">ODP Tidak Ditemukan</h3>
                <p class="text-slate-500 dark:text-slate-400">Tidak ada ODP yang sesuai dengan filter pencarian Anda.</p>
                <button wire:click="$set('search', '')" class="mt-4 text-indigo-600 font-medium hover:underline">Reset Pencarian</button>
            </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $odps->links() }}
    </div>
</div>

