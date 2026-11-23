<div class="space-y-4 pb-8 max-w-7xl mx-auto px-3 sm:px-6">
    @section('header_title', 'Detail Pekerjaan #' . ($jobData['uuid'] ?? $jobId))

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center gap-3 text-emerald-800 dark:text-emerald-200 text-sm font-semibold shadow-sm animate-fade-in">
            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">check_circle</span>
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm font-semibold shadow-sm animate-fade-in">
            <span class="material-symbols-outlined text-rose-600 dark:text-rose-400">error</span>
            <span class="flex-1">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Top Action Bar / Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-start sm:items-center gap-3">
            <a href="{{ route('technician.my-jobs.index') }}" 
               class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl text-slate-600 dark:text-slate-300 transition-colors flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100">
                        {{ $jobData['title'] ?? ('Pekerjaan #' . $jobId) }}
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $jobData['badge_class'] ?? '' }}">
                        {{ $jobData['type_label'] ?? 'Tugas Lapangan' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $jobData['status_badge'] ?? '' }}">
                        {{ $jobData['status_label'] ?? ucfirst($jobData['status'] ?? 'Pending') }}
                    </span>
                    @if(!empty($jobData['sla_due_at']))
                        <x-sla.countdown 
                            :due-at="$jobData['sla_due_at']" 
                            :started-at="$jobData['sla_started_at'] ?? null" 
                            :sla-status="$jobData['sla_status'] ?? 'normal'" 
                            :is-completed="!empty($jobData['is_completed'])" 
                        />
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                    <span>No. Referensi: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ $jobData['uuid'] ?? $jobId }}</strong></span>
                    <span>&bull;</span>
                    <span>Prioritas: <span class="uppercase font-bold text-slate-700 dark:text-slate-300">{{ $jobData['priority'] ?? 'Normal' }}</span></span>
                    @if(!empty($jobData['sla_due_at']))
                        <span>&bull;</span>
                        <span>Batas SLA: <strong class="text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($jobData['sla_due_at'])->format('d M Y H:i') }} WIB</strong></span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            @if(!empty($jobData['can_start']))
                <button wire:click="startJob" type="button"
                        class="flex-1 md:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-base">play_arrow</span>
                    <span>Mulai Kerjakan</span>
                </button>
            @endif

            @if(!empty($jobData['can_complete']))
                <button wire:click="openCompleteModal" type="button"
                        class="flex-1 md:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>Selesaikan Pekerjaan</span>
                </button>
            @endif

            @if(!empty($jobData['wizard_url']))
                <a href="{{ $jobData['wizard_url'] }}"
                   class="flex-1 md:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-[#0866F5] hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-outlined text-base">rocket_launch</span>
                    <span>Buka Wizard PSB</span>
                </a>
            @endif

            @if(!empty($jobData['diagnostic_url']))
                <a href="{{ $jobData['diagnostic_url'] }}"
                   class="flex-1 md:flex-none inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 dark:hover:bg-sky-900/50 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-xl text-xs font-bold transition-all">
                    <span class="material-symbols-outlined text-base">troubleshoot</span>
                    <span>Diagnosa ONT</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Left 2 Columns: Details --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Data Pelanggan Card --}}
            <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-500">person</span>
                        <span>Informasi Pelanggan & Lokasi</span>
                    </h2>
                    @if(!empty($jobData['latitude']) && !empty($jobData['longitude']))
                        <a href="https://maps.google.com/?q={{ $jobData['latitude'] }},{{ $jobData['longitude'] }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            <span class="material-symbols-outlined text-sm">near_me</span>
                            <span>Buka Navigasi Maps</span>
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Pelanggan</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $jobData['customer_name'] ?? '-' }}</span>
                        @if(!empty($jobData['customer_code']) && $jobData['customer_code'] !== '-')
                            <span class="text-[11px] text-slate-500 font-mono block">ID: {{ $jobData['customer_code'] }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Kontak / Telepon</span>
                        @if(!empty($jobData['customer_phone']) && $jobData['customer_phone'] !== '-')
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-semibold text-slate-800 dark:text-slate-200 font-mono">{{ $jobData['customer_phone'] }}</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $jobData['customer_phone']) }}" target="_blank" 
                                   class="px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 rounded-md text-[10px] font-bold inline-flex items-center gap-0.5">
                                    <span>WA</span>
                                </a>
                            </div>
                        @else
                            <span class="text-slate-400 italic">-</span>
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Alamat Instalasi / Lokasi</span>
                        <span class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $jobData['customer_address'] ?? 'Alamat tidak tersedia' }}</span>
                    </div>
                </div>
            </div>

            {{-- Detail Instruksi & Catatan --}}
            <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3.5">
                <div class="border-b border-slate-100 dark:border-slate-800/80 pb-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500">description</span>
                        <span>Instruksi Kerja & Catatan Lapangan</span>
                    </h2>
                </div>

                <div class="space-y-3 text-xs">
                    @if(!empty($jobData['description']))
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Deskripsi Pekerjaan</span>
                            <div class="mt-1 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed">
                                {{ $jobData['description'] }}
                            </div>
                        </div>
                    @endif

                    @if(!empty($jobData['notes']))
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Catatan Teknis / Penyelesaian</span>
                            <div class="mt-1 p-3 bg-amber-50/60 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40 rounded-xl text-amber-900 dark:text-amber-200 whitespace-pre-line leading-relaxed">
                                {{ $jobData['notes'] }}
                            </div>
                        </div>
                    @endif

                    @if(!empty($jobData['optical_power']))
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Pengukuran Redaman (Optical Power)</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-sm mt-0.5 inline-block">
                                {{ $jobData['optical_power'] }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right 1 Column: Status Timeline & Metadata --}}
        <div class="space-y-4">
            {{-- Status Timeline --}}
            <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                    <span class="material-symbols-outlined text-blue-500">timeline</span>
                    <span>Progres Pekerjaan</span>
                </h2>

                <div class="space-y-3.5 relative pl-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
                    {{-- Step 1: Penugasan --}}
                    <div class="relative">
                        <span class="absolute -left-6 top-0.5 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-white dark:ring-slate-900 flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Pekerjaan Dibuat / Ditugaskan</p>
                            <p class="text-[11px] text-slate-500 font-mono">
                                {{ !empty($jobData['scheduled_at']) ? \Carbon\Carbon::parse($jobData['scheduled_at'])->format('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Step 2: Mulai Dikerjakan --}}
                    <div class="relative">
                        <span class="absolute -left-6 top-0.5 w-4 h-4 rounded-full {{ !empty($jobData['started_at']) || ($jobData['status'] ?? '') === 'in_progress' || !empty($jobData['is_completed']) ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700' }} ring-4 ring-white dark:ring-slate-900 flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        </span>
                        <div>
                            <p class="text-xs font-bold {{ !empty($jobData['started_at']) || ($jobData['status'] ?? '') === 'in_progress' || !empty($jobData['is_completed']) ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400' }}">
                                Mulai Dikerjakan
                            </p>
                            <p class="text-[11px] text-slate-500 font-mono">
                                {{ !empty($jobData['started_at']) ? \Carbon\Carbon::parse($jobData['started_at'])->format('d M Y, H:i') : (($jobData['status'] ?? '') === 'in_progress' ? 'Sedang berlangsung' : 'Belum dimulai') }}
                            </p>
                        </div>
                    </div>

                    {{-- Step 3: Selesai --}}
                    <div class="relative">
                        <span class="absolute -left-6 top-0.5 w-4 h-4 rounded-full {{ !empty($jobData['is_completed']) ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700' }} ring-4 ring-white dark:ring-slate-900 flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        </span>
                        <div>
                            <p class="text-xs font-bold {{ !empty($jobData['is_completed']) ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400' }}">
                                Pekerjaan Selesai
                            </p>
                            <p class="text-[11px] text-slate-500 font-mono">
                                {{ !empty($jobData['completed_at']) ? \Carbon\Carbon::parse($jobData['completed_at'])->format('d M Y, H:i') : (!empty($jobData['is_completed']) ? 'Selesai' : 'Belum selesai') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs space-y-2">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Teknisi Bertugas</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $jobData['technician_name'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Penyelesaian Pekerjaan --}}
    @if($showCompleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 animate-scale-up">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-2xl">check_circle</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Selesaikan Pekerjaan Lapangan</h3>
                    </div>
                    <button wire:click="closeCompleteModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Hasil Redaman / Optical Power (dBm)</label>
                        <input type="text" wire:model="optical_power" placeholder="Contoh: -18.50"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 outline-none font-mono">
                        <span class="text-[10px] text-slate-400 mt-1 block">Opsional. Masukkan hasil ukur OPM jika dilakukan pengukuran kabel.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Pekerjaan / Hasil Penyelesaian</label>
                        <textarea wire:model="completion_notes" rows="4" placeholder="Tuliskan catatan teknis hasil pekerjaan lapangan..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button wire:click="closeCompleteModal" type="button"
                            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button wire:click="completeJob" type="button"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition-all shadow-sm active:scale-95 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">check</span>
                        <span>Konfirmasi Selesai</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
