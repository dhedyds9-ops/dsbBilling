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

    {{-- Filter Scope (Hanya untuk Admin / Pengawas) --}}
    @if(!empty($isAdmin))
        <div class="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-2 rounded-2xl shadow-sm text-xs">
            <span class="font-bold text-slate-600 dark:text-slate-400 pl-1 flex items-center gap-1.5 text-[11px]">
                <span class="material-symbols-outlined text-sm text-indigo-500">filter_alt</span>
                <span>Tampilan Tiket:</span>
            </span>
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                <button type="button" wire:click="setScope('my')"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $scope === 'my' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
                    Tiket Saya
                </button>
                <button type="button" wire:click="setScope('all')"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $scope === 'all' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
                    Semua Tiket
                </button>
            </div>
        </div>
    @endif

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

                <div class="mt-2 text-slate-500 dark:text-slate-400 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl space-y-2">
                    <div class="flex items-start gap-1.5">
                        <span class="material-symbols-outlined text-slate-400 mt-0.5 shrink-0 text-sm">location_on</span>
                        <div class="flex-1">
                            <span class="leading-snug block">{{ $ticket->customer?->address ?? 'Alamat tidak tersedia' }}</span>
                            @if($lat && $lng)
                                <div class="mt-1 flex items-center gap-1.5 text-[11px] font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
                                    <span class="material-symbols-outlined text-xs">my_location</span>
                                    <span>{{ $lat }}, {{ $lng }}</span>
                                </div>
                            @else
                                <div class="mt-1 text-[11px] text-amber-600 dark:text-amber-400 font-medium">
                                    <span>Koordinat GPS pelanggan belum tersimpan</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($ticket->customer_id)
                        <div class="pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-2"
                             x-data="{ updating: false }">
                            <span class="text-[10px] text-slate-400 font-medium">Titik Lokasi Rumah:</span>
                            <button type="button"
                                    :disabled="updating"
                                    @click="
                                        if (!navigator.geolocation) {
                                            alert('Fitur Geolocation tidak didukung di browser ini.');
                                            return;
                                        }
                                        updating = true;
                                        navigator.geolocation.getCurrentPosition(
                                            (pos) => {
                                                $wire.updateCustomerCoordinates({{ $ticket->customer_id }}, pos.coords.latitude, pos.coords.longitude)
                                                    .then(() => { updating = false; })
                                                    .catch(() => { updating = false; });
                                            },
                                            (err) => {
                                                updating = false;
                                                alert('Gagal mengambil GPS: ' + err.message + '. Pastikan izin lokasi (GPS) diaktifkan di browser HP.');
                                            },
                                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                                        );
                                    "
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg text-[11px] font-bold active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-xs" :class="updating ? 'animate-spin' : ''" x-text="updating ? 'progress_activity' : 'near_me'"></span>
                                <span x-text="updating ? 'Mendeteksi GPS...' : '📍 Update GPS dari HP'"></span>
                            </button>
                        </div>
                    @endif
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

                @if(!empty($ticket->photos) && is_array($ticket->photos))
                    <div class="mt-2.5">
                        <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">photo_library</span>
                            <span>Foto Bukti Pekerjaan ({{ count($ticket->photos) }}):</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($ticket->photos as $photoUrl)
                                @php
                                    $secureUrl = \Illuminate\Support\Str::startsWith($photoUrl, ['http://', 'https://', '/technician-portal'])
                                        ? $photoUrl
                                        : (\Illuminate\Support\Str::contains($photoUrl, 'tickets/') ? route('technician.tickets.photo', ['ticket' => $ticket->id, 'filename' => basename($photoUrl)]) : asset($photoUrl));
                                @endphp
                                <a href="{{ $secureUrl }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:opacity-90 transition-opacity">
                                    <img src="{{ $secureUrl }}" alt="Bukti Lapangan" class="w-full h-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Bukti Penyelesaian (Redaman OPM & Tanda Tangan) --}}
                @if($isResolved && ($ticket->optical_power || $ticket->digital_signature))
                    <div class="mt-3 p-3 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-xl border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            @if($ticket->optical_power)
                                <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200">
                                    <span class="material-symbols-outlined text-sm text-indigo-500">settings_input_antenna</span>
                                    <span>Redaman OPM: <strong class="text-emerald-600 dark:text-emerald-400 font-mono">{{ $ticket->optical_power }}</strong></span>
                                </div>
                            @endif
                            @if($ticket->digital_signature)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 text-[10px] font-bold inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">draw</span> Ttd Pelanggan Terverifikasi
                                </span>
                            @endif
                        </div>
                        @if($ticket->digital_signature)
                            <div class="w-16 h-10 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-100 overflow-hidden p-0.5 shrink-0 shadow-sm" title="Tanda Tangan Pelanggan">
                                <img src="{{ $ticket->digital_signature }}" alt="Ttd" class="w-full h-full object-contain">
                            </div>
                        @endif
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
                        <a href="{{ Route::has('technician.diagnostic.index') ? route('technician.diagnostic.index', ['ticket_id' => $ticket->id, 'customer_id' => $ticket->customer_id]) : url('/technician-portal/diagnostic?ticket_id='.$ticket->id.'&customer_id='.$ticket->customer_id) }}"
                           class="py-2 px-3 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 dark:hover:bg-sky-900/50 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 rounded-xl font-bold text-xs flex items-center justify-center gap-1 transition-colors"
                           title="Cek Sinyal & Diagnosa ONT">
                            <span class="material-symbols-outlined text-sm">troubleshoot</span>
                            <span>Diagnosa</span>
                        </a>
                        <a href="{{ Route::has('technician.swap-ont.index') ? route('technician.swap-ont.index', ['ticket_id' => $ticket->id, 'customer_id' => $ticket->customer_id]) : url('/technician-portal/swap-ont?ticket_id='.$ticket->id.'&customer_id='.$ticket->customer_id) }}"
                           class="py-2 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-xl font-bold text-xs flex items-center justify-center gap-1 transition-colors"
                           title="Ganti / Swap ONT Pelanggan">
                            <span class="material-symbols-outlined text-sm">sync_alt</span>
                            <span>Ganti ONT</span>
                        </a>
                        <button wire:click="openProgressModal({{ $ticket->id }})" type="button"
                                class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs flex items-center justify-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-base text-amber-500">edit_note</span>
                            Progress
                        </button>
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

{{-- Modal Konfirmasi Selesaikan Tiket --}}
@if($showResolveModal)
<div class="fixed inset-0 z-[120] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-sm transition-all"
     x-data
     @keydown.escape.window="$wire.set('showResolveModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm sm:max-w-md w-full max-h-[90vh] sm:max-h-[85vh] flex flex-col text-center relative overflow-hidden">
        {{-- Top Accent Bar --}}
        <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 shrink-0"></div>

        {{-- Header with Close Button --}}
        <div class="p-4 pb-2 relative shrink-0">
            <button type="button" wire:click="$set('showResolveModal', false)"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>

            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-inner">
                <span class="material-symbols-outlined" style="font-size:28px">task_alt</span>
            </div>

            <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">Selesaikan Tiket?</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Konfirmasi perbaikan gangguan telah selesai ditangani.</p>
        </div>

        {{-- Scrollable Form Content --}}
        <div class="overflow-y-auto px-4 py-2 space-y-3 flex-1 text-left overscroll-contain">
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800 text-xs space-y-1.5">
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

            {{-- Pengukuran Redaman Optik (OPM dBm) --}}
            <div class="space-y-1.5 p-3 bg-slate-50 dark:bg-slate-850 rounded-2xl border border-slate-100 dark:border-slate-800">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-indigo-500">settings_input_antenna</span>
                        Hasil Redaman Optik (OPM)
                    </span>
                    <span class="text-[9px] text-slate-400 font-normal lowercase">standar: -15 s/d -24 dBm</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" wire:model="optical_power" placeholder="Misal: -19.5 dBm"
                           class="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    <div class="flex gap-1 shrink-0">
                        <button type="button" @click="$wire.set('optical_power', '-18.5 dBm')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 rounded-lg text-[10px] font-bold text-slate-600 dark:text-slate-300">-18.5</button>
                        <button type="button" @click="$wire.set('optical_power', '-19.0 dBm')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 rounded-lg text-[10px] font-bold text-slate-600 dark:text-slate-300">-19.0</button>
                        <button type="button" @click="$wire.set('optical_power', '-21.0 dBm')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 rounded-lg text-[10px] font-bold text-slate-600 dark:text-slate-300">-21.0</button>
                    </div>
                </div>
            </div>

            {{-- Pemakaian Material Lapangan (Stok Saya) --}}
            <div class="space-y-1.5 p-3 bg-slate-50 dark:bg-slate-850 rounded-2xl border border-slate-100 dark:border-slate-800">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-indigo-500">inventory_2</span>
                        Catat Pemakaian Material (Stok)
                    </span>
                    <span class="text-[9px] text-slate-400 font-normal lowercase">opsional</span>
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <select wire:model="material_id" class="w-full px-2.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">-- Tanpa Material --</option>
                            @foreach($availableMaterials as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->name }} (Sisa: {{ $mat->stock }} {{ $mat->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="number" wire:model="material_qty" min="0" placeholder="Qty"
                               class="w-full px-2.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-center bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>
                </div>
            </div>

            {{-- Checklist SOP Pengerjaan --}}
            <div class="space-y-2 p-3 bg-slate-50 dark:bg-slate-850 rounded-2xl border border-slate-100 dark:border-slate-800">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-emerald-600">checklist</span>
                    Checklist SOP Pengerjaan
                </label>
                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklists.odp_checked" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-[11px]">Redaman ODP & ONT diukur normal</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklists.cable_installed" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-[11px]">Kabel dropcore & konektor terpasang rapi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklists.onu_online" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-[11px]">ONT online, lampu PON hijau stabil</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="checklists.speedtest_ok" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-[11px]">Speedtest & browsing di HP pelanggan normal</span>
                    </label>
                </div>
            </div>

            {{-- Tanda Tangan Digital Pelanggan --}}
            <div class="space-y-1.5 p-3 bg-slate-50 dark:bg-slate-850 rounded-2xl border border-slate-100 dark:border-slate-800"
                 x-data="{
                     isDrawing: false,
                     canvas: null,
                     ctx: null,
                     hasSignature: false,
                     init() {
                         this.canvas = this.$refs.sigCanvas;
                         this.ctx = this.canvas.getContext('2d');
                         this.ctx.lineWidth = 2.5;
                         this.ctx.lineCap = 'round';
                         this.ctx.strokeStyle = '#0f172a';
                     },
                     start(e) {
                         this.isDrawing = true;
                         this.draw(e);
                     },
                     stop() {
                         if (!this.isDrawing) return;
                         this.isDrawing = false;
                         this.ctx.beginPath();
                         this.hasSignature = true;
                         $wire.set('digital_signature', this.canvas.toDataURL('image/png'));
                     },
                     draw(e) {
                         if (!this.isDrawing) return;
                         const rect = this.canvas.getBoundingClientRect();
                         const clientX = e.clientX || (e.touches && e.touches[0].clientX);
                         const clientY = e.clientY || (e.touches && e.touches[0].clientY);
                         const x = clientX - rect.left;
                         const y = clientY - rect.top;
                         this.ctx.lineTo(x, y);
                         this.ctx.stroke();
                         this.ctx.beginPath();
                         this.ctx.moveTo(x, y);
                     },
                     clear() {
                         this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                         this.hasSignature = false;
                         $wire.set('digital_signature', '');
                     }
                 }">
                <div class="flex items-center justify-between">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-indigo-500">draw</span>
                        Tanda Tangan Pelanggan
                    </label>
                    <button type="button" @click="clear" class="text-[10px] text-rose-600 dark:text-rose-400 font-bold hover:underline flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">restart_alt</span>
                        Hapus
                    </button>
                </div>
                <p class="text-[10px] text-slate-400">Mintalah pelanggan membubuhkan tanda tangan langsung di bawah:</p>
                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-100 overflow-hidden shadow-inner">
                    <canvas x-ref="sigCanvas" width="360" height="120"
                            class="w-full h-28 touch-none bg-white dark:bg-slate-100 cursor-crosshair"
                            @mousedown="start($event)" @mouseup="stop()" @mousemove="draw($event)"
                            @touchstart.prevent="start($event)" @touchend.prevent="stop()" @touchmove.prevent="draw($event)">
                    </canvas>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">
                    Catatan Perbaikan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                </label>
                <textarea wire:model="resolution_notes" rows="2" placeholder="Contoh: Kabel fiber optik disambung, redaman -19 dBm normal..."
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 bg-white dark:bg-slate-800 dark:text-slate-100 shadow-inner"></textarea>
            </div>

            {{-- Upload Foto Pekerjaan (Kamera Langsung & Galeri) --}}
            <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">
                    Foto Bukti Lapangan <span class="text-slate-400 font-normal lowercase">(Kamera / Galeri)</span>
                </label>

                <div class="grid grid-cols-2 gap-2">
                    {{-- Tombol Buka Kamera Langsung --}}
                    <div>
                        <input type="file" wire:model="upload_photos" accept="image/*" capture="environment" id="tickets-resolve-camera-input" class="hidden">
                        <label for="tickets-resolve-camera-input" class="cursor-pointer flex flex-col items-center justify-center p-2.5 rounded-xl border-2 border-dashed border-emerald-300 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-950/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-center transition-all active:scale-95 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center mb-1 shadow-sm">
                                <span class="material-symbols-outlined text-lg">photo_camera</span>
                            </div>
                            <span class="text-xs font-bold text-emerald-900 dark:text-emerald-200">Foto Kamera</span>
                            <span class="text-[9px] text-emerald-600/80 dark:text-emerald-400">Jepret Langsung</span>
                        </label>
                    </div>

                    {{-- Tombol Pilih dari Galeri --}}
                    <div>
                        <input type="file" wire:model="upload_photos" multiple accept="image/*" id="tickets-resolve-gallery-input" class="hidden">
                        <label for="tickets-resolve-gallery-input" class="cursor-pointer flex flex-col items-center justify-center p-2.5 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 text-center transition-all active:scale-95 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-slate-600 text-white flex items-center justify-center mb-1 shadow-sm">
                                <span class="material-symbols-outlined text-lg">photo_library</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Pilih Galeri</span>
                            <span class="text-[9px] text-slate-500 dark:text-slate-400">Album / File</span>
                        </label>
                    </div>
                </div>

                <div wire:loading wire:target="upload_photos" class="text-xs text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5 py-1">
                    <span class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                    <span>Mengunggah foto...</span>
                </div>

                @if(!empty($upload_photos))
                    @php
                        $photoList = is_array($upload_photos) ? $upload_photos : [$upload_photos];
                    @endphp
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach($photoList as $index => $photo)
                            <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100">
                                @if(is_object($photo) && method_exists($photo, 'temporaryUrl'))
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                                @endif
                                <button type="button" wire:click="removePhoto({{ $index }})"
                                        class="absolute top-1 right-1 w-6 h-6 bg-rose-600 text-white rounded-full flex items-center justify-center shadow hover:bg-rose-700 active:scale-95">
                                    <span class="material-symbols-outlined text-xs">close</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Sticky Action Buttons Footer --}}
        <div class="p-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-850 backdrop-blur flex items-center gap-2 shrink-0 pb-6 sm:pb-3.5">
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
<div class="fixed inset-0 z-[120] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-sm transition-all"
     x-data
     @keydown.escape.window="$wire.set('showEditResolutionModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm sm:max-w-md w-full max-h-[90vh] sm:max-h-[85vh] flex flex-col text-center relative overflow-hidden">
        {{-- Top Accent Bar --}}
        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-blue-500 shrink-0"></div>

        {{-- Header with Close Button --}}
        <div class="p-4 pb-2 relative shrink-0">
            <button type="button" wire:click="$set('showEditResolutionModal', false)"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>

            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-inner">
                <span class="material-symbols-outlined" style="font-size:28px">edit_note</span>
            </div>

            <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">Edit Penyelesaian Tiket</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Perbarui catatan hasil perbaikan atau buka kembali tiket jika masih perlu tindak lanjut.</p>
        </div>

        {{-- Scrollable Form Content --}}
        <div class="overflow-y-auto px-4 py-2 space-y-3 flex-1 text-left overscroll-contain">
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800 text-xs space-y-1.5">
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

        {{-- Sticky Action Buttons Footer --}}
        <div class="p-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-850 backdrop-blur flex items-center gap-2 shrink-0 pb-6 sm:pb-3.5">
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

{{-- Modal Update Progress Pekerjaan --}}
@if($showProgressModal)
<div class="fixed inset-0 z-[120] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/70 backdrop-blur-sm transition-all"
     x-data
     @keydown.escape.window="$wire.set('showProgressModal', false)">
    <div class="bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm sm:max-w-md w-full max-h-[90vh] sm:max-h-[85vh] flex flex-col text-center relative overflow-hidden">
        {{-- Top Accent Bar --}}
        <div class="h-1.5 bg-gradient-to-r from-amber-500 to-orange-500 shrink-0"></div>

        {{-- Header with Close Button --}}
        <div class="p-4 pb-2 relative shrink-0">
            <button type="button" wire:click="$set('showProgressModal', false)"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>

            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-inner">
                <span class="material-symbols-outlined" style="font-size:28px">edit_document</span>
            </div>

            <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">Update Progress Lapangan</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Perbarui status dan catatan kendala sebelum tiket diselesaikan.</p>
        </div>

        {{-- Scrollable Form Content --}}
        <div class="overflow-y-auto px-4 py-2 space-y-3 flex-1 text-left overscroll-contain">
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800 text-xs space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-semibold text-[10px] uppercase">Kode Tiket</span>
                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedTicketCode }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-semibold text-[10px] uppercase">Pelanggan</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[180px]">{{ $selectedTicketCustomer }}</span>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Status Pengerjaan</label>
                <select wire:model="progress_status"
                        class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                    <option value="in_progress">Sedang Diproses (In Progress)</option>
                    <option value="pending_material">Menunggu Material / Splicer (Pending Material)</option>
                    <option value="pending_customer">Menunggu Konfirmasi Pelanggan (Pending Customer)</option>
                    <option value="open">Masih Terbuka (Open)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Kategori Gangguan</label>
                <input type="text" wire:model="progress_category" placeholder="Contoh: LOS, Kabel Putus, Adaptor Mati..."
                       class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 bg-white dark:bg-slate-800 dark:text-slate-100">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Catatan Pekerjaan / Hasil Pengecekan</label>
                <textarea wire:model="progress_notes" rows="3" placeholder="Tuliskan kendala di lapangan, redaman awal, atau langkah yang sedang dikerjakan..."
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 bg-white dark:bg-slate-800 dark:text-slate-100 shadow-inner"></textarea>
            </div>

            {{-- Upload Foto Saat Update Progress (Kamera & Galeri) --}}
            <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">
                    Foto Lapangan / Kendala <span class="text-slate-400 font-normal lowercase">(Kamera / Galeri)</span>
                </label>

                <div class="grid grid-cols-2 gap-2">
                    {{-- Tombol Buka Kamera Langsung --}}
                    <div>
                        <input type="file" wire:model="upload_photos" accept="image/*" capture="environment" id="tickets-progress-camera-input" class="hidden">
                        <label for="tickets-progress-camera-input" class="cursor-pointer flex flex-col items-center justify-center p-2.5 rounded-xl border-2 border-dashed border-amber-300 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-950/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 text-center transition-all active:scale-95 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center mb-1 shadow-sm">
                                <span class="material-symbols-outlined text-lg">photo_camera</span>
                            </div>
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-200">Foto Kamera</span>
                            <span class="text-[9px] text-amber-600/80 dark:text-amber-400">Jepret Langsung</span>
                        </label>
                    </div>

                    {{-- Tombol Pilih dari Galeri --}}
                    <div>
                        <input type="file" wire:model="upload_photos" multiple accept="image/*" id="tickets-progress-gallery-input" class="hidden">
                        <label for="tickets-progress-gallery-input" class="cursor-pointer flex flex-col items-center justify-center p-2.5 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 text-center transition-all active:scale-95 shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-slate-600 text-white flex items-center justify-center mb-1 shadow-sm">
                                <span class="material-symbols-outlined text-lg">photo_library</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Pilih Galeri</span>
                            <span class="text-[9px] text-slate-500 dark:text-slate-400">Album / File</span>
                        </label>
                    </div>
                </div>

                <div wire:loading wire:target="upload_photos" class="mt-1 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5 py-1">
                    <span class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                    <span>Mengunggah foto...</span>
                </div>

                @if(!empty($upload_photos))
                    @php
                        $photoList = is_array($upload_photos) ? $upload_photos : [$upload_photos];
                    @endphp
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach($photoList as $index => $photo)
                            <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100">
                                @if(is_object($photo) && method_exists($photo, 'temporaryUrl'))
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                                @endif
                                <button type="button" wire:click="removePhoto({{ $index }})"
                                        class="absolute top-1 right-1 w-6 h-6 bg-rose-600 text-white rounded-full flex items-center justify-center shadow hover:bg-rose-700 active:scale-95">
                                    <span class="material-symbols-outlined text-xs">close</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Sticky Action Buttons Footer --}}
        <div class="p-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-850 backdrop-blur flex items-center gap-2 shrink-0 pb-6 sm:pb-3.5">
            <button wire:click="$set('showProgressModal', false)" type="button"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors">
                Batal
            </button>
            <button wire:click="saveProgress" type="button"
                    wire:loading.attr="disabled"
                    class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-95 transition-all">
                <span wire:loading.remove wire:target="saveProgress" class="material-symbols-outlined text-sm">save</span>
                <span wire:loading wire:target="saveProgress" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                <span>Simpan Progress</span>
            </button>
        </div>
    </div>
</div>
@endif
</div>
