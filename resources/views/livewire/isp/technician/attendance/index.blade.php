@section('header_title', 'Absensi')

<div class="space-y-4 p-4">

    {{-- Hero Clock Banner --}}
    <div class="p-5 rounded-2xl shadow-lg relative overflow-hidden text-center text-white"
         style="background: linear-gradient(135deg, #4f46e5 0%, #1d4ed8 100%) !important; color: #ffffff !important;"
         x-data="{
             currentTime: '--:--:--',
             init() {
                 this.tick();
                 setInterval(() => this.tick(), 1000);
             },
             tick() {
                 try {
                     const now = new Date();
                     const h = String(now.getHours()).padStart(2, '0');
                     const m = String(now.getMinutes()).padStart(2, '0');
                     const s = String(now.getSeconds()).padStart(2, '0');
                     this.currentTime = h + ':' + m + ':' + s;
                 } catch (e) {
                     this.currentTime = 'Error';
                 }
             }
         }" x-init="init()">
        <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined" style="font-size:130px">fingerprint</span>
        </div>
        <div class="relative z-10">
            <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wider mb-2">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
            <div class="text-5xl font-black tracking-wider font-mono mb-1" x-text="currentTime">--:--:--</div>
            <p class="text-indigo-200 text-xs">Pastikan Anda berada di area kerja.</p>
        </div>
    </div>

    {{-- Error / Success Messages --}}
    @if($errorMessage)
        <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-2xl border border-red-200 dark:border-red-800/50 flex gap-3 text-red-800 dark:text-red-400">
            <span class="material-symbols-outlined shrink-0" style="font-size:20px">error</span>
            <p class="text-xs font-bold self-center">{{ $errorMessage }}</p>
        </div>
    @endif
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800/50 flex gap-3 text-emerald-800 dark:text-emerald-400">
            <span class="material-symbols-outlined shrink-0" style="font-size:20px">check_circle</span>
            <p class="text-xs font-bold self-center">{{ session('success') }}</p>
        </div>
    @endif

    {{-- GPS Status + Main Action --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden"
         x-data="{
             gpsLoading: true,
             gpsError: false,
             init() {
                 this.getLocation();
             },
             getLocation() {
                 try {
                     this.gpsLoading = true;
                     this.gpsError = false;
                     if (!navigator.geolocation) {
                         this.gpsLoading = false;
                         this.gpsError = true;
                         return;
                     }
                     navigator.geolocation.getCurrentPosition(
                         (pos) => {
                             this.$dispatch('gps-located', { lat: pos.coords.latitude, lng: pos.coords.longitude });
                             this.gpsLoading = false;
                             this.gpsError = false;
                         },
                         (err) => {
                             this.gpsLoading = false;
                             this.gpsError = true;
                         },
                         { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                     );
                 } catch (e) {
                     this.gpsLoading = false;
                     this.gpsError = true;
                 }
             }
         }" x-init="init()">

        {{-- GPS Bar --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-colors"
                     :class="gpsLoading ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-500' : (gpsError ? 'bg-red-100 dark:bg-red-900/50 text-red-500' : 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-500')">
                    <span class="material-symbols-outlined" style="font-size:18px"
                          :class="gpsLoading ? 'animate-spin' : ''"
                          x-text="gpsLoading ? 'sync' : (gpsError ? 'location_disabled' : 'my_location')">
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">GPS</p>
                    <p class="text-xs font-bold"
                       :class="gpsLoading ? 'text-blue-600 dark:text-blue-400' : (gpsError ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400')"
                       x-text="gpsLoading ? 'Mencari lokasi...' : (gpsError ? 'Tidak Ditemukan' : 'Lokasi Aktif')">
                    </p>
                </div>
            </div>
            <button @click="getLocation()" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-full hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined" style="font-size:20px">refresh</span>
            </button>
        </div>

        {{-- Main Action --}}
        <div class="p-5">
            @if(!$attendance)
                {{-- Belum Check In --}}
                @php
                    $homeBranch = auth()->user()?->branch;
                @endphp
                <div class="space-y-4">
                    <div class="text-center py-1">
                        <span class="inline-block px-4 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full text-xs font-black uppercase tracking-wider">
                            Status: Belum Hadir
                        </span>
                    </div>

                    {{-- Informasi Cabang & Multi-Site Auto-Detect --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs space-y-1.5">
                        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200 font-semibold">
                            <span class="material-symbols-outlined text-indigo-500 text-[18px]">domain</span>
                            <span>Cabang Penugasan: <strong>{{ $homeBranch?->name ?? 'Kantor Pusat' }}</strong></span>
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-normal">
                            *Sistem mendukung presensi otomatis di <strong>Kantor Pusat maupun seluruh Kantor Cabang resmi</strong> perusahaan yang terdaftar.
                        </p>
                    </div>

                    {{-- Toggle Penugasan Lapangan / Tugas Luar --}}
                    <div class="p-3.5 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40 flex items-start justify-between gap-3">
                        <div>
                            <label for="isFieldAssignment" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-indigo-600 text-[18px]">alt_route</span>
                                Penugasan Luar / Lapangan
                            </label>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-normal">
                                Centang jika Anda ditugaskan langsung ke lokasi pelanggan / ODP tanpa mampir ke kantor cabang.
                            </p>
                        </div>
                        <input type="checkbox" id="isFieldAssignment" wire:model.live="isFieldAssignment" class="w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600 cursor-pointer mt-0.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">
                            {{ $isFieldAssignment ? 'Catatan Tugas Luar (Wajib)' : 'Catatan Shift (Opsional)' }}
                        </label>
                        <input type="text" wire:model="notes"
                               class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100"
                               placeholder="{{ $isFieldAssignment ? 'Contoh: No. Tiket TKT-102, perbaikan ODP-03...' : 'Kondisi kesehatan, alasan, dll...' }}">
                    </div>

                    <button wire:click="checkIn"
                            wire:loading.attr="disabled"
                            wire:target="checkIn"
                            x-bind:disabled="gpsLoading || gpsError"
                            class="w-full py-4 bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-base flex items-center justify-center gap-2 hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-lg shadow-emerald-600/30 cursor-pointer">
                        <span wire:loading.remove wire:target="checkIn" class="material-symbols-outlined shrink-0" style="font-size:24px">how_to_reg</span>
                        <span wire:loading wire:target="checkIn" class="material-symbols-outlined shrink-0 animate-spin" style="font-size:24px">progress_activity</span>
                        <span>CHECK IN SEKARANG</span>
                    </button>
                </div>

            @elseif($attendance && !$attendance->checked_out_at)
                {{-- Sudah Check In (Mode Shift Aktif) --}}
                @php
                    $minutesWorked = $attendance->checked_in_at ? \Carbon\Carbon::parse($attendance->checked_in_at)->diffInMinutes(now()) : 0;
                    $hoursWorked = floor($minutesWorked / 60);
                    $remMinutes = $minutesWorked % 60;
                @endphp
                <div class="space-y-4">
                    {{-- Status Card: Sedang Bekerja --}}
                    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-center">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 text-xs font-black uppercase tracking-wider mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Shift Kerja Sedang Aktif
                        </div>
                        <div class="flex items-center justify-center gap-2 text-emerald-700 dark:text-emerald-300">
                            <span class="material-symbols-outlined" style="font-size:28px">schedule</span>
                            <span class="text-3xl font-black font-mono">{{ $attendance->checked_in_at->format('H:i') }} WIB</span>
                        </div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                            Durasi Dinas: {{ $hoursWorked > 0 ? "{$hoursWorked} jam {$remMinutes} menit" : "{$remMinutes} menit" }}
                        </p>
                        @if(!empty($attendance->notes))
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 pt-2 border-t border-emerald-100 dark:border-emerald-900/40 truncate">
                                {{ $attendance->notes }}
                            </p>
                        @endif
                    </div>

                    {{-- Tombol Buka Menu Tugas Hari Ini --}}
                    <a href="{{ route('technician.dashboard') }}" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-md transition cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">assignment</span>
                        <span>Buka Menu Tugas & Pekerjaan</span>
                    </a>

                    {{-- Check-Out Section dengan Proteksi Time-Lock --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        @if($minutesWorked < 15)
                            {{-- Time Lock Safeguard --}}
                            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex items-start gap-2.5 text-amber-800 dark:text-amber-300 text-xs">
                                <span class="material-symbols-outlined text-amber-600 text-[20px] shrink-0 mt-0.5">lock_clock</span>
                                <div>
                                    <p class="font-bold">Tombol Check-Out Terkunci Sementara</p>
                                    <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5">
                                        Untuk mencegah salah tekan / double tap, tombol Check-Out baru dapat digunakan minimal 15 menit setelah Check-In (tersisa <strong>{{ 15 - $minutesWorked }} menit</strong> lagi).
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- Tombol Checkout Aktif Setelah 15 Menit --}}
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Catatan Akhir Hari (Opsional)</label>
                                    <input type="text" wire:model="notes"
                                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100"
                                           placeholder="Laporan pekerjaan hari ini...">
                                </div>
                                <button wire:click="promptCheckOut"
                                        x-bind:disabled="gpsLoading || gpsError"
                                        class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-md shadow-amber-500/20 cursor-pointer">
                                    <span class="material-symbols-outlined text-[20px]">logout</span>
                                    <span>Selesaikan Shift & Check-Out</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                {{-- Sudah Check Out --}}
                <div class="text-center py-4">
                    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined" style="font-size:44px;font-variation-settings:'FILL' 1">verified</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Tugas Selesai!</h3>
                    <p class="text-slate-400 text-sm">Anda telah menyelesaikan jam kerja hari ini.</p>
                    <div class="mt-5 flex items-center justify-center gap-8 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Masuk</p>
                            <p class="font-mono font-black text-slate-700 dark:text-slate-300 text-2xl mt-0.5">{{ $attendance->checked_in_at->format('H:i') }}</p>
                        </div>
                        <div class="w-px h-10 bg-slate-200 dark:bg-slate-700"></div>
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Pulang</p>
                            <p class="font-mono font-black text-slate-700 dark:text-slate-300 text-2xl mt-0.5">{{ $attendance->checked_out_at->format('H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL DIALOG KONFIRMASI CHECK-OUT --}}
    @if($confirmingCheckOut && $attendance)
        @php
            $mWorked = $attendance->checked_in_at ? \Carbon\Carbon::parse($attendance->checked_in_at)->diffInMinutes(now()) : 0;
            $hWorked = floor($mWorked / 60);
            $rMins = $mWorked % 60;
        @endphp
        <div class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-sm bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                <div class="p-5 text-center">
                    <div class="w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-outlined text-[32px]">warning</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Konfirmasi Selesai Dinas</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Halo <strong>{{ auth()->user()->name }}</strong>, Anda telah berdinas selama <strong>{{ $hWorked > 0 ? "{$hWorked} jam {$rMins} menit" : "{$rMins} menit" }}</strong> hari ini (Masuk pukul {{ $attendance->checked_in_at->format('H:i') }} WIB).<br><br>
                        Apakah Anda yakin seluruh pekerjaan telah tuntas dan ingin <strong>Check-Out</strong> sekarang?
                    </p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700 flex gap-2.5">
                    <button type="button" wire:click="cancelCheckOut" class="flex-1 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="checkOut" wire:loading.attr="disabled" wire:target="checkOut" class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span wire:loading.remove wire:target="checkOut">Ya, Check-Out</span>
                        <span wire:loading wire:target="checkOut" class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Riwayat 5 Hari --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">calendar_month</span>
            <h2 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider">5 Hari Terakhir</h2>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($history as $hist)
                <div class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $hist->date->format('d M Y') }}</p>
                        <div class="flex gap-4 mt-0.5">
                            <span class="text-xs text-slate-400 font-mono">
                                <span class="text-emerald-500 font-bold">IN</span> {{ $hist->checked_in_at ? $hist->checked_in_at->format('H:i') : '-' }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">
                                <span class="text-amber-500 font-bold">OUT</span> {{ $hist->checked_out_at ? $hist->checked_out_at->format('H:i') : '-' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        @if($hist->status->value === 'checked_in')
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full text-[10px] font-black uppercase">Aktif</span>
                        @elseif($hist->status->value === 'checked_out')
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full text-[10px] font-black uppercase">Selesai</span>
                        @elseif($hist->status->value === 'late')
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full text-[10px] font-black uppercase">Telat</span>
                        @else
                            <span class="px-2.5 py-1 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-full text-[10px] font-black uppercase">Absen</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 dark:text-slate-400" style="font-size:36px">event_busy</span>
                    <p class="text-sm font-bold text-slate-400 mt-2">Belum ada riwayat absensi.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

