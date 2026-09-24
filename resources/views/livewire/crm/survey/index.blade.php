@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">map</span>
        </div>
        <span class="text-lg">Survey & Pemasangan</span>
    </div>
@endsection

<div class="space-y-6 pb-10">
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div wire:click="$set('activeTab', 'prospects')" class="cursor-pointer relative overflow-x-auto rounded-xl border {{ $activeTab === 'prospects' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-indigo-200 dark:border-indigo-800/60' }} shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 transition-all">
            @if($activeTab === 'prospects') <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-500 rounded-t-xl"></div> @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Antrean Prospek</h3>
                <div class="text-2xl font-black text-indigo-700 dark:text-indigo-300 mb-1">{{ number_format($stats['prospects']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Belum dijadwalkan survey</div>
            </div>
        </div>

        <div wire:click="$set('activeTab', 'scheduled')" class="cursor-pointer relative overflow-x-auto rounded-xl border {{ $activeTab === 'scheduled' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-amber-200 dark:border-amber-800/60' }} shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 transition-all">
            @if($activeTab === 'scheduled') <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500 rounded-t-xl"></div> @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-2">Jadwal Survey</h3>
                <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mb-1">{{ number_format($stats['scheduled']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Menunggu eksekusi teknisi</div>
            </div>
        </div>

        <div wire:click="$set('activeTab', 'completed')" class="cursor-pointer relative overflow-x-auto rounded-xl border {{ $activeTab === 'completed' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-emerald-200 dark:border-emerald-800/60' }} shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 transition-all">
            @if($activeTab === 'completed') <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500 rounded-t-xl"></div> @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Survey Selesai</h3>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-1">{{ number_format($stats['completed']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Riwayat survey selesai / batal</div>
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

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Prospek / Pelanggan</th>
                        <th class="px-6 py-4">Lokasi & Kontak</th>
                        @if($activeTab === 'prospects')
                            <th class="px-6 py-4">Tgl Konversi</th>
                        @else
                            <th class="px-6 py-4">Teknisi</th>
                            <th class="px-6 py-4">Jadwal Survey</th>
                            <th class="px-6 py-4">Status</th>
                        @endif
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 group" wire:key="row-{{ $activeTab }}-{{ $item->id }}">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 dark:text-slate-100">
                                {{ $activeTab === 'prospects' ? $item->name : ($item->prospect->name ?? 'Unknown') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-600 dark:text-slate-400">
                                {{ $activeTab === 'prospects' ? $item->address : ($item->address ?? ($item->prospect->address ?? '-')) }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:14px">call</span>
                                {{ $activeTab === 'prospects' ? $item->phone : ($item->prospect->phone ?? '-') }}
                            </div>
                        </td>
                        
                        @if($activeTab === 'prospects')
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                {{ $item->created_at?->format('d/m/Y H:i') }}
                            </td>
                        @else
                            <td class="px-6 py-4">
                                @if($item->assignedTo)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                            {{ substr($item->assignedTo->name, 0, 1) }}
                                        </div>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $item->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500 dark:text-slate-400 italic bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md">Belum ditugaskan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                {{ $item->scheduled_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                    @if($item->status === 'scheduled') bg-amber-100 dark:bg-amber-900/50 text-amber-700
                                    @elseif($item->status === 'in_progress') bg-blue-100 dark:bg-blue-900/50 text-blue-700
                                    @elseif($item->status === 'completed') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @else bg-red-100 dark:bg-red-900/50 text-red-700
                                    @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        @endif

                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if($activeTab === 'prospects')
                                    @if(auth()->user()->job_function !== 'TECHNICIAN')
                                    <button wire:click="openScheduleModal({{ $item->id }})" class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/50 flex items-center gap-1 transition-colors text-xs font-semibold">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">event_available</span>
                                        Buat Jadwal
                                    </button>
                                    @else
                                    <span class="text-xs text-slate-400 italic">Menunggu Jadwal</span>
                                    @endif
                                @else
                                    <button wire:click="openInputResultModal({{ $item->id }})" class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/50 flex items-center gap-1 transition-colors text-xs font-semibold">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">assignment_turned_in</span>
                                        {{ $activeTab === 'completed' ? 'Edit Hasil' : 'Input Hasil' }}
                                    </button>
                                @endif
                                
                                @if(auth()->user()->job_function !== 'TECHNICIAN')
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus data ini?" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:bg-red-900/30 flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined notranslate block mx-auto text-slate-300 mb-2" translate="no" style="font-size:48px">pending_actions</span>
                            <p class="font-medium text-slate-900 dark:text-slate-100">Tidak ada data di tab ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($items->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50/50">
            {{ $items->links() }}
        </div>
        @endif
    </div>

    {{-- Schedule Modal --}}
    @if($showScheduleModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200 dark:border-slate-700 animate-in fade-in zoom-in-95 duration-200">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Jadwalkan Survey</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Prospek: <strong class="text-indigo-600 dark:text-indigo-400">{{ $schedule_prospect_name }}</strong></p>
                </div>
                <button wire:click="$set('showScheduleModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit.prevent="saveSchedule" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal & Waktu Survei</label>
                        <input type="datetime-local" wire:model.live="scheduled_at" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" required>
                        @error('scheduled_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Owner / Reseller</label>
                        <select wire:model.live="reseller_id" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" required>
                            <option value="">-- Pilih Owner --</option>
                            @foreach($resellers as $rsl)
                                <option value="{{ $rsl->id }}">{{ $rsl->name }} ({{ $rsl->roles->pluck('name')->implode(', ') }})</option>
                            @endforeach
                        </select>
                        @error('reseller_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tugaskan Kepada Teknisi (Opsional)</label>
                    <select wire:model="assigned_to" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih Teknisi --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->workload ?? 0 }} Tugas pada tgl ini)</option>
                        @endforeach
                    </select>
                    @error('assigned_to') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Tujuan</label>
                    <textarea wire:model="survey_address" rows="2" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" required></textarea>
                    @error('survey_address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan (Pesan untuk teknisi)</label>
                    <textarea wire:model="survey_notes" rows="2" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></textarea>
                </div>
                
                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showScheduleModal', false)" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Input Hasil Survey Modal --}}
    @if($showInputResultModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-in fade-in zoom-in-95 duration-200 my-8">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:20px">assignment_turned_in</span>
                        Input Hasil Survey
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Prospek: <strong class="text-indigo-600 dark:text-indigo-400">{{ $schedule_prospect_name }}</strong></p>
                </div>
                <button wire:click="$set('showInputResultModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit.prevent="saveSurveyResult" class="p-6 space-y-5">
                {{-- ODP & Port --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">ODP Terdekat</label>
                        <select wire:model="input_odp_id" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih ODP (Opsional) --</option>
                            @foreach($odps as $odp)
                                <option value="{{ $odp->id }}">{{ $odp->name }}</option>
                            @endforeach
                        </select>
                        @error('input_odp_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Port Tersedia</label>
                        <input type="text" wire:model="input_port" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: Port 3">
                        @error('input_port') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Distance & Estimation --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jarak Tarikan (Meter)</label>
                        <input type="number" step="0.1" wire:model="input_distance" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="0">
                        @error('input_distance') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Estimasi Kabel (Meter)</label>
                        <input type="number" step="0.1" wire:model="input_cable_estimation" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="0">
                        @error('input_cable_estimation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Kordinat --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Latitude</label>
                        <input type="text" wire:model="input_latitude" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="-6.123456">
                        @error('input_latitude') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Longitude</label>
                        <input type="text" wire:model="input_longitude" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="106.123456">
                        @error('input_longitude') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Recommendation --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Rekomendasi (Kelayakan)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-emerald-500 transition-colors {{ $input_recommendation == 'feasible' ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-500 ring-1 ring-emerald-500' : 'bg-white dark:bg-slate-800' }}">
                            <input type="radio" wire:model="input_recommendation" value="feasible" class="sr-only dark:bg-slate-900 dark:text-slate-100">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:18px">check_circle</span>
                                    Layak (Feasible)
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Jaringan ter-cover dan bisa dipasang</span>
                            </div>
                        </label>

                        <label class="relative flex cursor-pointer p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-red-500 transition-colors {{ $input_recommendation == 'not_feasible' ? 'bg-red-50 dark:bg-red-900/30 border-red-500 ring-1 ring-red-500' : 'bg-white dark:bg-slate-800' }}">
                            <input type="radio" wire:model="input_recommendation" value="not_feasible" class="sr-only dark:bg-slate-900 dark:text-slate-100">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined notranslate text-red-500" translate="no" style="font-size:18px">cancel</span>
                                    Tidak Layak
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Jarak terlalu jauh atau port penuh</span>
                            </div>
                        </label>
                    </div>
                    @error('input_recommendation') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan Teknisi</label>
                    <textarea wire:model="input_notes" rows="3" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Kondisi lapangan, hambatan, atau permintaan pelanggan..."></textarea>
                    @error('input_notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" wire:click="$set('showInputResultModal', false)" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                        Simpan Hasil Survey
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
