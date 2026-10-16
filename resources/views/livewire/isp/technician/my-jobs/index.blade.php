@section('header_title', 'Tugas Saya')

<div class="space-y-4 p-4 pb-24">

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined" style="font-size:130px">engineering</span>
        </div>
        <div class="relative z-10">
            <p class="text-indigo-200 text-xs font-semibold mb-1">Daftar instalasi (PSB) & survey lapangan</p>
            <div class="grid grid-cols-2 gap-3 mt-3">
                <button wire:click="switchTab('active')" type="button" class="bg-white/20 dark:bg-slate-800/20 hover:bg-white/30 rounded-xl p-3 flex items-center justify-between text-left transition-all {{ $activeTab === 'active' ? 'ring-2 ring-white' : '' }}">
                    <div>
                        <p class="text-xs text-indigo-100 font-medium">Tugas Aktif</p>
                        <h3 class="text-2xl font-black mt-0.5">{{ $stats['active'] ?? 0 }}</h3>
                    </div>
                    <span class="material-symbols-outlined opacity-80" style="font-size:28px">pending_actions</span>
                </button>
                <button wire:click="switchTab('history')" type="button" class="bg-white/20 dark:bg-slate-800/20 hover:bg-white/30 rounded-xl p-3 flex items-center justify-between text-left transition-all {{ $activeTab === 'history' ? 'ring-2 ring-white' : '' }}">
                    <div>
                        <p class="text-xs text-indigo-100 font-medium">Riwayat Selesai</p>
                        <h3 class="text-2xl font-black mt-0.5">{{ $stats['history'] ?? 0 }}</h3>
                    </div>
                    <span class="material-symbols-outlined opacity-80" style="font-size:28px">check_circle</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Tabs --}}
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl flex items-center shadow-inner">
        <button wire:click="switchTab('active')" type="button"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                       {{ $activeTab === 'active' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">schedule</span>
            <span>Tugas Aktif ({{ $stats['active'] ?? 0 }})</span>
        </button>
        <button wire:click="switchTab('history')" type="button"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                       {{ $activeTab === 'history' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Riwayat ({{ $stats['history'] ?? 0 }})</span>
        </button>
    </div>

    {{-- Sub Filters: Tipe Pekerjaan --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <button wire:click="filterJobType('all')" type="button"
                class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap
                       {{ $jobType === 'all' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
            Semua ({{ ($stats['active'] ?? 0) + ($stats['history'] ?? 0) }})
        </button>
        <button wire:click="filterJobType('installation')" type="button"
                class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap inline-flex items-center gap-1
                       {{ $jobType === 'installation' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-sm">build_circle</span>
            Instalasi / PSB
        </button>
        <button wire:click="filterJobType('survey')" type="button"
                class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap inline-flex items-center gap-1
                       {{ $jobType === 'survey' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
            <span class="material-symbols-outlined text-sm">radar</span>
            Survey
        </button>
    </div>

    {{-- Search Bar --}}
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:20px">search</span>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, alamat, username, atau SN ONU..."
               class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 shadow-sm">
        @if($search)
            <button wire:click="$set('search', '')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        @endif
    </div>

    {{-- Task List --}}
    <div class="space-y-3">
        @forelse($jobs as $job)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $job->is_completed ? 'bg-emerald-500' : 'bg-indigo-500' }}"></div>

            {{-- Header: Tanggal & Kategori Badges --}}
            <div class="flex justify-between items-start gap-2 mb-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400 text-xs font-semibold">
                    <span class="material-symbols-outlined text-indigo-500 text-sm">event</span>
                    {{ $job->date ? \Carbon\Carbon::parse($job->date)->format('d M Y, H:i') : '-' }}
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="px-2 py-0.5 {{ $job->job_type === 'installation' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800' }} rounded-lg text-[10px] font-bold uppercase">
                        {{ $job->type_badge }}
                    </span>
                    @if($job->is_completed)
                        <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-[10px] font-bold uppercase">
                            {{ $job->status_label }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 rounded-lg text-[10px] font-bold uppercase">
                            {{ $job->status_label }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Customer Name & Info --}}
            <div class="flex items-start justify-between gap-2">
                <div>
                    <h4 class="font-black text-base text-slate-900 dark:text-white leading-tight">
                        {{ $job->title }}
                    </h4>
                    @if($job->code && $job->code !== '-')
                        <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">ID: {{ $job->code }}</span>
                    @endif
                </div>
            </div>

            {{-- Phone --}}
            @if($job->phone && $job->phone !== '-')
                <div class="flex items-center gap-3 mt-1.5 text-xs">
                    <a href="tel:{{ $job->phone }}" class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 hover:text-emerald-600">
                        <span class="material-symbols-outlined text-emerald-500 text-sm">call</span>
                        {{ $job->phone }}
                    </a>
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $job->phone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 hover:underline font-semibold text-[11px]">
                        <span class="material-symbols-outlined text-xs">chat</span>
                        WhatsApp
                    </a>
                </div>
            @endif

            {{-- Address --}}
            <div class="flex items-start gap-1.5 mt-2 text-slate-500 dark:text-slate-400 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                <span class="material-symbols-outlined text-slate-400 mt-0.5 shrink-0 text-sm">location_on</span>
                <span class="leading-snug">{{ $job->address }}</span>
            </div>

            {{-- Installation Technical Details (if any) --}}
            @if($job->job_type === 'installation')
                <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-3 gap-2 bg-slate-50/80 dark:bg-slate-800/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 text-[11px]">
                    @if($job->username)
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Username</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $job->username }}</span>
                        </div>
                    @endif
                    @if($job->package_name)
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Paket</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 truncate block">{{ $job->package_name }}</span>
                        </div>
                    @endif
                    @if($job->onu_sn)
                        <div class="col-span-2 sm:col-span-1">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">ONU SN</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300 font-semibold">{{ $job->onu_sn }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Actions --}}
            <div class="mt-3 flex items-center gap-2">
                @if($job->latitude && $job->longitude)
                    <a href="https://maps.google.com/?q={{ $job->latitude }},{{ $job->longitude }}" target="_blank"
                       class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
                        <span class="material-symbols-outlined text-blue-500 text-base">near_me</span>
                        Buka Peta
                    </a>
                @endif

                @if($job->job_type === 'survey' && !$job->is_completed)
                    <button wire:click="openInputResultModal({{ $job->survey_id }})"
                            class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-base">edit_document</span>
                        Input Hasil
                    </button>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-10 text-center shadow-sm">
            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-slate-300 dark:text-slate-600" style="font-size:36px">
                    {{ $activeTab === 'active' ? 'inbox' : 'history' }}
                </span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">
                {{ $activeTab === 'active' ? 'Tidak ada tugas aktif' : 'Belum ada riwayat pekerjaan' }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                @if($activeTab === 'active')
                    Semua instalasi dan survey telah selesai, atau belum ada penugasan baru.
                @else
                    Instalasi dan survey yang selesai dikerjakan akan tersimpan di sini.
                @endif
            </p>
            <div class="mt-4">
                <a href="{{ route('technician.installation.wizard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    Mulai Pasang Baru (Instalasi)
                </a>
            </div>
        </div>
        @endforelse
    </div>

    @if($jobs->hasPages())
        <div class="pt-2">{{ $jobs->links(data: ['scrollTo' => false]) }}</div>
    @endif

</div>

{{-- Modal Input Hasil Survey (Fullscreen) --}}
@if($showInputResultModal)
<div class="fixed inset-0 z-[100] bg-white dark:bg-slate-900 flex flex-col overflow-hidden">
    {{-- Modal Header --}}
    <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900 sticky top-0 z-10">
        <div>
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">engineering</span>
                Hasil Survey Lapangan
            </h3>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mt-0.5">Input data teknis jalur & ODP</p>
        </div>
        <button wire:click="$set('showInputResultModal', false)" class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
            <span class="material-symbols-outlined" style="font-size:22px">close</span>
        </button>
    </div>

    {{-- Modal Body --}}
    <form wire:submit.prevent="saveSurveyResult" class="flex-1 overflow-y-auto p-4 space-y-6 pb-32">

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">router</span> Detail Koneksi ODP
            </h4>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">ODP Terdekat</label>
                <select wire:model="input_odp_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    <option value="">-- Pilih ODP --</option>
                    @foreach($odps as $odp)
                        <option value="{{ $odp->id }}">{{ $odp->name }}</option>
                    @endforeach
                </select>
                @error('input_odp_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Port</label>
                <input type="text" wire:model="input_port" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="Contoh: Port 3">
                @error('input_port') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">straighten</span> Estimasi Jarak
            </h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Tarikan (m)</label>
                    <input type="number" step="0.1" wire:model="input_distance" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center font-bold" placeholder="0">
                    @error('input_distance') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Kabel (m)</label>
                    <input type="number" step="0.1" wire:model="input_cable_estimation" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center font-bold" placeholder="0">
                    @error('input_cable_estimation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">share_location</span> Titik Koordinat (GPS)
            </h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Latitude</label>
                    <input type="text" wire:model="input_latitude" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center" placeholder="-6.123">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Longitude</label>
                    <input type="text" wire:model="input_longitude" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center" placeholder="106.123">
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">verified</span> Kelayakan Pemasangan
            </h4>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex flex-col items-center cursor-pointer p-4 border-2 rounded-2xl transition-colors {{ $input_recommendation == 'feasible' ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-500' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" wire:model="input_recommendation" value="feasible" class="sr-only">
                    <span class="material-symbols-outlined mb-1 {{ $input_recommendation == 'feasible' ? 'text-emerald-500' : 'text-slate-400' }}" style="font-size:32px">check_circle</span>
                    <span class="text-sm font-bold {{ $input_recommendation == 'feasible' ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400' }}">LAYAK</span>
                </label>
                <label class="flex flex-col items-center cursor-pointer p-4 border-2 rounded-2xl transition-colors {{ $input_recommendation == 'not_feasible' ? 'bg-red-50 dark:bg-red-900/30 border-red-500' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700' }}">
                    <input type="radio" wire:model="input_recommendation" value="not_feasible" class="sr-only">
                    <span class="material-symbols-outlined mb-1 {{ $input_recommendation == 'not_feasible' ? 'text-red-500' : 'text-slate-400' }}" style="font-size:32px">cancel</span>
                    <span class="text-sm font-bold {{ $input_recommendation == 'not_feasible' ? 'text-red-700 dark:text-red-400' : 'text-slate-600 dark:text-slate-400' }}">TDK LAYAK</span>
                </label>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">speaker_notes</span> Catatan Lapangan
            </h4>
            <textarea wire:model="input_notes" rows="3" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="Kondisi lapangan, hambatan, permintaan user..."></textarea>
        </div>
    </form>

    {{-- Fixed Submit at Bottom --}}
    <div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
        <button wire:click="saveSurveyResult" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-transform shadow-md shadow-indigo-600/20">
            <span class="material-symbols-outlined shrink-0" style="font-size:20px">save</span>
            SIMPAN HASIL PEKERJAAN
        </button>
    </div>
</div>
@endif
