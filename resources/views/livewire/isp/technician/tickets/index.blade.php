@section('header_title', 'Tiket Gangguan Saya')

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

    {{-- Tabs --}}
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl flex items-center shadow-inner">
        <button wire:click="switchTab('open')" type="button"
            class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                   {{ $activeTab === 'open' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">build</span>
            <span>Tugas Aktif ({{ $openCount ?? 0 }})</span>
        </button>
        <button wire:click="switchTab('resolved')" type="button"
            class="flex-1 py-2.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center justify-center gap-1.5
                   {{ $activeTab === 'resolved' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
            <span class="material-symbols-outlined text-sm">history</span>
            <span>Riwayat ({{ $resolvedCount ?? 0 }})</span>
        </button>
    </div>

    {{-- Search Bar --}}
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:20px">search</span>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor tiket, keluhan, nama, atau alamat pelanggan..."
               class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 shadow-sm">
        @if($search)
            <button wire:click="$set('search', '')" type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        @endif
    </div>

    {{-- Ticket List --}}
    <div class="space-y-3">
        @forelse($tickets as $ticket)
            @php
                $isResolved = in_array($ticket->status, ['resolved', 'closed']);
                $code = !empty($ticket->uuid)
                    ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6)))
                    : ('TKT-' . $ticket->id);
                $phone = $ticket->customer?->phone;
                $lat = $ticket->customer?->latitude;
                $lng = $ticket->customer?->longitude;
            @endphp
            <div wire:key="tech-ticket-{{ $ticket->id }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $isResolved ? 'bg-emerald-500' : (in_array($ticket->priority, ['critical', 'high']) ? 'bg-rose-500' : 'bg-indigo-500') }}"></div>

                <div class="flex justify-between items-start gap-2 mb-3 pb-2.5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400 text-xs font-semibold">
                        <span class="material-symbols-outlined text-indigo-500 text-sm">event</span>
                        {{ $ticket->created_at?->format('d M Y, H:i') ?? '-' }}
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="px-2 py-0.5 bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-[10px] font-bold uppercase">
                            {{ strtoupper($ticket->category ?? 'Gangguan') }}
                        </span>
                        <span class="px-2 py-0.5 {{ $isResolved ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200 dark:border-amber-800' }} border rounded-lg text-[10px] font-bold uppercase">
                            {{ $isResolved ? 'Selesai' : ($ticket->status === 'in_progress' ? 'Diproses' : 'Aktif') }}
                        </span>
                    </div>
                </div>

                <div>
                    <h4 class="font-black text-base text-slate-900 dark:text-white leading-tight">
                        {{ $ticket->customer?->name ?? 'Pelanggan #' . ($ticket->customer_id ?? '-') }}
                    </h4>
                    <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">ID: {{ $code }} (#{{ $ticket->id }})</span>
                </div>

                @if($phone && $phone !== '-')
                    <div class="flex items-center gap-3 mt-1.5 text-xs">
                        <a href="tel:{{ $phone }}" class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 hover:text-emerald-600">
                            <span class="material-symbols-outlined text-emerald-500 text-sm">call</span>
                            {{ $phone }}
                        </a>
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
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

                <div class="flex items-start gap-1.5 mt-2 text-slate-500 dark:text-slate-400 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5 shrink-0 text-sm">location_on</span>
                    <span class="leading-snug">{{ $ticket->customer?->address ?? 'Alamat tidak tersedia' }}</span>
                </div>

                <div class="mt-2.5 bg-amber-50/70 dark:bg-amber-950/30 p-2.5 rounded-xl border border-amber-200/70 dark:border-amber-800/50 text-[11px] space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-amber-900 dark:text-amber-200">
                        <span class="material-symbols-outlined text-amber-600 text-sm">build</span>
                        <span>Keluhan: {{ $ticket->title }}</span>
                    </div>
                    @if($ticket->description)
                        <p class="text-slate-600 dark:text-slate-300 text-xs pl-5 leading-relaxed">
                            {{ $ticket->description }}
                        </p>
                    @endif
                </div>

                @if(!empty($ticket->resolution_notes))
                    <div class="mt-2 bg-emerald-50/80 dark:bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-200/70 dark:border-emerald-800/50 text-[11px] space-y-0.5">
                        <div class="flex items-center gap-1.5 font-bold text-emerald-800 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-emerald-600 text-sm">task_alt</span>
                            <span>Catatan Penyelesaian:</span>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-xs pl-5 leading-relaxed">
                            {{ $ticket->resolution_notes }}
                        </p>
                    </div>
                @endif

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if($lat && $lng)
                        <a href="https://maps.google.com/?q={{ $lat }},{{ $lng }}" target="_blank"
                           class="flex-1 py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
                            <span class="material-symbols-outlined text-blue-500 text-base">near_me</span>
                            Buka Peta
                        </a>
                    @endif

                    @if(!$isResolved)
                        @if($ticket->status === 'open')
                            <button wire:click="startTicket({{ $ticket->id }})" type="button"
                                    class="py-2 px-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1 shadow-sm active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-base">play_arrow</span>
                                Kerjakan
                            </button>
                        @endif
                        <button wire:click="openResolveModal({{ $ticket->id }})" type="button"
                                class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            Selesaikan Tiket
                        </button>
                    @else
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                            Selesai: {{ $ticket->resolved_at ? $ticket->resolved_at->format('d M Y, H:i') : '-' }}
                        </div>
                        @if($ticket->status === 'resolved')
                            <button wire:click="openEditResolutionModal({{ $ticket->id }})" type="button"
                                    class="ml-auto py-1.5 px-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-sm">edit_note</span>
                                Edit Penyelesaian
                            </button>
                        @else
                            <span class="ml-auto py-1 px-2.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">lock</span>
                                Terkunci (Closed)
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-10 text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-600" style="font-size:36px">assignment_turned_in</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">
                    {{ $activeTab === 'open' ? 'Tidak ada tiket gangguan aktif' : 'Belum ada riwayat tiket selesai' }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                    Hanya tiket gangguan yang ditugaskan kepada akun Anda yang ditampilkan di halaman ini.
                </p>
            </div>
        @endforelse
    </div>

    @if($tickets->hasPages())
        <div class="pt-2">
            {{ $tickets->links(data: ['scrollTo' => false]) }}
        </div>
    @endif
</div>

{{-- Modal Konfirmasi Selesaikan Tiket --}}
@if($showResolveModal)
<div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all"
     x-data
     @keydown.escape.window="$wire.set('showResolveModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm w-full p-5 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500"></div>

        <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
            <span class="material-symbols-outlined" style="font-size:32px">task_alt</span>
        </div>

        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight">Selesaikan Tiket?</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Konfirmasi bahwa perbaikan gangguan telah selesai ditangani.</p>

        <div class="mt-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800 text-left text-xs space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Kode Tiket</span>
                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedTicketCode }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Pelanggan</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[180px]">{{ $selectedTicketCustomer }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Subjek</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium truncate max-w-[180px]">{{ $selectedTicketTitle }}</span>
            </div>
        </div>

        <div class="mt-3 text-left">
            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">
                Catatan Perbaikan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
            </label>
            <textarea wire:model="resolution_notes" rows="2" placeholder="Contoh: Kabel fiber optik disambung, sinyal normal..."
                      class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-800 dark:text-slate-100 shadow-inner"></textarea>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button wire:click="$set('showResolveModal', false)" type="button"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                Batal
            </button>
            <button wire:click="confirmResolve" type="button"
                    wire:loading.attr="disabled"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all">
                <span wire:loading.remove wire:target="confirmResolve" class="material-symbols-outlined text-sm">check</span>
                <span wire:loading wire:target="confirmResolve" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                <span>Ya, Selesaikan</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Modal Edit Penyelesaian Tiket --}}
@if($showEditResolutionModal)
<div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all"
     x-data
     @keydown.escape.window="$wire.set('showEditResolutionModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm w-full p-5 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 to-blue-500"></div>

        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
            <span class="material-symbols-outlined" style="font-size:30px">edit_note</span>
        </div>

        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight">Edit Penyelesaian Tiket</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbarui catatan hasil perbaikan atau buka kembali tiket jika masih perlu tindak lanjut.</p>

        <div class="mt-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800 text-left text-xs space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Kode Tiket</span>
                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedTicketCode }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Pelanggan</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[180px]">{{ $selectedTicketCustomer }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-400 font-semibold text-[10px] uppercase">Subjek</span>
                <span class="text-slate-700 dark:text-slate-300 font-medium truncate max-w-[180px]">{{ $selectedTicketTitle }}</span>
            </div>
        </div>

        <div class="mt-3 text-left space-y-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Status Tiket</label>
                <select wire:model="edit_resolution_status"
                        class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                    <option value="resolved">Tetap Selesai (Perbarui Catatan)</option>
                    <option value="in_progress">Buka Kembali ke Tugas Aktif (Diproses)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Catatan Perbaikan / Penyelesaian</label>
                <textarea wire:model="resolution_notes" rows="3" placeholder="Tuliskan detail tindakan perbaikan, pergantian konektor/kabel, redaman akhir..."
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 dark:text-slate-100 shadow-inner"></textarea>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button wire:click="$set('showEditResolutionModal', false)" type="button"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                Batal
            </button>
            <button wire:click="saveResolutionUpdate" type="button"
                    wire:loading.attr="disabled"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all">
                <span wire:loading.remove wire:target="saveResolutionUpdate" class="material-symbols-outlined text-sm">save</span>
                <span wire:loading wire:target="saveResolutionUpdate" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>
</div>
@endif
