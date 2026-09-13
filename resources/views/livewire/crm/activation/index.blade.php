@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">offline_bolt</span>
        </div>
        <span class="text-lg">Aktivasi Layanan</span>
    </div>
@endsection

<div class="space-y-6 pb-10">
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div wire:click="$set('activeTab', 'pending')" class="cursor-pointer relative overflow-x-auto rounded-xl border {{ $activeTab === 'pending' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-amber-200 dark:border-amber-800/60' }} shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 transition-all">
            @if($activeTab === 'pending') <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500 rounded-t-xl"></div> @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-2">Menunggu Aktivasi</h3>
                <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mb-1">{{ number_format($stats['pending'] ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Siap diaktifkan & didaftarkan ke radius</div>
            </div>
        </div>

        <div wire:click="$set('activeTab', 'completed')" class="cursor-pointer relative overflow-x-auto rounded-xl border {{ $activeTab === 'completed' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-emerald-200 dark:border-emerald-800/60' }} shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 transition-all">
            @if($activeTab === 'completed') <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500 rounded-t-xl"></div> @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Pelanggan Aktif</h3>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-1">{{ number_format($stats['completed'] ?? 0) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Layanan telah berhasil diaktifkan</div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" 
                    class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" 
                    placeholder="Cari nama pelanggan...">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="perPage" class="py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="20">20 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="all">Semua</option>
            </select>
        </div>
    </div>

    {{-- Error / Success Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800/50 flex gap-3 text-emerald-800 dark:text-emerald-400">
            <span class="material-symbols-outlined notranslate shrink-0 mt-0.5" translate="no">check_circle</span>
            <p class="text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif
    @error('activation')
        <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-xl border border-red-200 dark:border-red-800/50 flex gap-3 text-red-800 dark:text-red-400">
            <span class="material-symbols-outlined notranslate shrink-0 mt-0.5" translate="no">error</span>
            <p class="text-sm font-bold">{{ $message }}</p>
        </div>
    @enderror

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Prospek / Pelanggan</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Teknisi / Instalasi</th>
                        <th class="px-6 py-4">ODP & Jarak</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $item->prospect->name ?? '-' }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">ID: {{ $item->prospect->id ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">call</span> {{ $item->prospect->phone ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <x-display.avatar :name="$item->assignedTo->name ?? 'Unassigned'" size="sm" />
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $item->assignedTo->name ?? 'Belum Ditugaskan' }}</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $item->updated_at->format('d M Y H:i') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $item->odp->name ?? 'Tidak Ada' }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Jarak: {{ $item->distance ?? 0 }}m | Kabel: {{ $item->cable_estimation ?? 0 }}m</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item->status === 'activated')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">check_circle</span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">pending</span> Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($item->status !== 'activated')
                                    <button wire:click="openActivationModal({{ $item->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
                                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">power</span>
                                        Aktifkan
                                    </button>
                                @else
                                    <button disabled class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-lg text-xs font-semibold cursor-not-allowed">
                                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">verified</span>
                                        Aktif
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">
                                        {{ $activeTab === 'pending' ? 'inbox' : 'verified' }}
                                    </span>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Tidak ada data</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ $activeTab === 'pending' ? 'Belum ada prospek yang siap diaktifkan layanannya.' : 'Belum ada layanan pelanggan yang aktif.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Aktivasi --}}
    @if($showActivationModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no">power</span>
                    Aktivasi Layanan: {{ $activation_prospect_name }}
                </h3>
                <button wire:click="$set('showActivationModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit="activateService">
                <div class="p-6 space-y-6">
                    {{-- Alert Box --}}
                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4 flex gap-3 text-blue-800 dark:text-blue-300">
                        <span class="material-symbols-outlined notranslate shrink-0" translate="no">info</span>
                        <div class="text-sm">
                            <p class="font-bold mb-1">Informasi Aktivasi PPPoE</p>
                            <p>Proses ini akan mendaftarkan akun PPPoE ke Radius dan menyambungkan ke jaringan Mikrotik (NAS) secara otomatis.</p>
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Network Profile (Router/NAS)</label>
                            <select wire:model="network_profile_id" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Jaringan Mikrotik --</option>
                                @foreach($networkProfiles as $np)
                                    <option value="{{ $np->id }}">{{ $np->name }} (NAS: {{ $np->nas_ip }})</option>
                                @endforeach
                            </select>
                            @error('network_profile_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Service Profile (Paket Internet)</label>
                            <select wire:model="service_profile_id" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Paket PPPoE --</option>
                                @foreach($serviceProfiles as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }} (Rp {{ number_format($sp->price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                            @error('service_profile_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username PPPoE</label>
                            <input type="text" wire:model="pppoe_username" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
                            @error('pppoe_username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password PPPoE</label>
                            <div class="relative">
                                <input type="text" wire:model="pppoe_password" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 font-mono pr-10 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            @error('pppoe_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showActivationModal', false)" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
                        Proses Aktivasi
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
