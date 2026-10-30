@section('header_title', 'Jadwal Maintenance')

<div class="space-y-4 p-4 pb-24">
    @if(session('success'))
        <div class="p-3 bg-emerald-500 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 bg-rose-500 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-sm">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-gradient-to-br from-sky-600 to-indigo-700 text-white p-4 rounded-2xl shadow-md">
        <div class="flex items-center gap-2.5">
            <span class="material-symbols-outlined text-2xl">engineering</span>
            <div>
                <h1 class="text-base font-black leading-tight">Jadwal Maintenance Jaringan</h1>
                <p class="text-[11px] text-sky-100">Tugas pemeliharaan infrastruktur (Router/OLT/ODC/ODP) & tiket maintenance</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl flex items-center shadow-inner">
        <button wire:click="switchTab('active')" type="button"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                       {{ $activeTab === 'active' ? 'bg-white dark:bg-slate-900 text-sky-600 dark:text-sky-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">event_upcoming</span>
            <span>Tugas Aktif ({{ $activeCount ?? 0 }})</span>
        </button>
        <button wire:click="switchTab('history')" type="button"
                class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                       {{ $activeTab === 'history' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Riwayat ({{ $historyCount ?? 0 }})</span>
        </button>
    </div>

    {{-- Search --}}
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:20px">search</span>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari jadwal maintenance, perangkat, atau lokasi..."
               class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-900 dark:text-slate-100 shadow-sm">
        @if($search)
            <button wire:click="$set('search', '')" type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        @endif
    </div>

    {{-- Cards --}}
    <div class="space-y-3">
        @forelse($jobs as $job)
            <div wire:key="maint-item-{{ $job->source }}-{{ $job->id }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $job->is_completed ? 'bg-emerald-500' : (in_array($job->priority, ['critical', 'high']) ? 'bg-rose-500' : 'bg-sky-500') }}"></div>

                <div class="flex justify-between items-start gap-2 mb-3 pb-2.5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400 text-xs font-semibold">
                        <span class="material-symbols-outlined text-sky-500 text-sm">calendar_month</span>
                        {{ $job->date ? \Carbon\Carbon::parse($job->date)->format('d M Y, H:i') : '-' }}
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="px-2 py-0.5 bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-lg text-[10px] font-bold uppercase">
                            {{ $job->type_label }}
                        </span>
                        <span class="px-2 py-0.5 {{ $job->is_completed ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800' }} border rounded-lg text-[10px] font-bold uppercase">
                            {{ $job->is_completed ? 'Selesai' : ($job->status === 'in_progress' ? 'Dikerjakan' : 'Dijadwalkan') }}
                        </span>
                    </div>
                </div>

                <div>
                    <h3 class="font-black text-base text-slate-900 dark:text-white leading-tight">{{ $job->target_name }}</h3>
                    <span class="text-[11px] font-mono text-slate-400">Kode: {{ $job->code }} · Prioritas: {{ strtoupper($job->priority) }}</span>
                </div>

                <div class="flex items-start gap-1.5 mt-2 text-slate-500 dark:text-slate-400 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5 shrink-0 text-sm">location_on</span>
                    <span class="leading-snug">{{ $job->location }}</span>
                </div>

                <div class="mt-2.5 bg-sky-50/70 dark:bg-sky-950/30 p-2.5 rounded-xl border border-sky-200/70 dark:border-sky-800/50 text-[11px] space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-sky-900 dark:text-sky-200">
                        <span class="material-symbols-outlined text-sky-600 text-sm">build_circle</span>
                        <span>{{ $job->title }}</span>
                    </div>
                    @if($job->description)
                        <p class="text-slate-600 dark:text-slate-300 text-xs pl-5 leading-relaxed">{{ $job->description }}</p>
                    @endif
                </div>

                @if(!empty($job->resolution_notes))
                    <div class="mt-2 bg-emerald-50/80 dark:bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-200/70 dark:border-emerald-800/50 text-[11px] space-y-0.5">
                        <div class="flex items-center gap-1.5 font-bold text-emerald-800 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-emerald-600 text-sm">task_alt</span>
                            <span>Laporan Penyelesaian:</span>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-xs pl-5 leading-relaxed">{{ $job->resolution_notes }}</p>
                    </div>
                @endif

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if(!$job->is_completed)
                        @if(in_array($job->status, ['scheduled', 'open']))
                            <button wire:click="startItem('{{ $job->source }}', {{ $job->id }})" type="button"
                                    class="py-2 px-3.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1 shadow-sm active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-base">play_arrow</span>
                                Mulai Kerjakan
                            </button>
                        @endif
                        <button wire:click="openCompleteModal('{{ $job->source }}', {{ $job->id }}, false)" type="button"
                                class="flex-1 py-2 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            Selesaikan Maintenance
                        </button>
                    @else
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                            Selesai: {{ $job->completed_at ? \Carbon\Carbon::parse($job->completed_at)->format('d M Y, H:i') : '-' }}
                        </div>
                        @if(!$job->is_locked)
                            <button wire:click="openCompleteModal('{{ $job->source }}', {{ $job->id }}, true)" type="button"
                                    class="ml-auto py-1.5 px-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-sm">edit_note</span>
                                Edit Penyelesaian
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="py-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                <div class="w-16 h-16 mx-auto bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-3xl text-slate-400">thumb_up</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">
                    {{ $activeTab === 'active' ? 'Tidak ada jadwal maintenance aktif' : 'Belum ada riwayat maintenance' }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                    Jadwal maintenance yang ditugaskan kepada Anda dari Admin Support akan tampil di sini.
                </p>
            </div>
        @endforelse
    </div>

    @if($jobs->hasPages())
        <div class="pt-2">
            {{ $jobs->links(data: ['scrollTo' => false]) }}
        </div>
    @endif
</div>

@if($showModal)
<div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-data
     @keydown.escape.window="$wire.set('showModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm w-full p-5 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-500 to-emerald-500"></div>

        <div class="w-14 h-14 bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
            <span class="material-symbols-outlined" style="font-size:30px">{{ $isEditing ? 'edit_note' : 'task_alt' }}</span>
        </div>

        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight">
            {{ $isEditing ? 'Edit Penyelesaian Maintenance' : 'Selesaikan Maintenance?' }}
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $selectedTitle }}</p>

        <div class="mt-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800 text-left text-xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Kode</span>
                <span class="font-mono font-bold text-sky-600 dark:text-sky-400">{{ $selectedCode }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Perangkat / Lokasi</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[180px]">{{ $selectedTarget }}</span>
            </div>
        </div>

        <div class="mt-3 text-left space-y-3">
            @if($isEditing)
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Status Tugas</label>
                    <select wire:model="edit_status"
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                        <option value="completed">Tetap Selesai (Perbarui Laporan)</option>
                        <option value="in_progress">Buka Kembali ke Tugas Aktif</option>
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Laporan / Catatan Penyelesaian</label>
                <textarea wire:model="resolution_notes" rows="3" placeholder="Contoh: Pembersihan SFP & ODP selesai, redaman optik normal -18 dBm..."
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-sky-500 bg-white dark:bg-slate-800 dark:text-slate-100 shadow-inner"></textarea>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button wire:click="$set('showModal', false)" type="button"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                Batal
            </button>
            <button wire:click="saveCompletion" type="button"
                    wire:loading.attr="disabled"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all">
                <span wire:loading.remove wire:target="saveCompletion" class="material-symbols-outlined text-sm">check</span>
                <span wire:loading wire:target="saveCompletion" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                <span>{{ $isEditing ? 'Simpan' : 'Ya, Selesaikan' }}</span>
            </button>
        </div>
    </div>
</div>
@endif
