<div>
    @if($isWorkforce)
        {{-- 1. TOP BANNER JIKA BELUM ABSEN --}}
        @if(! $hasCheckedIn)
            <div class="mb-5 p-4 rounded-xl border border-amber-300 dark:border-amber-700/60 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 dark:from-amber-950/40 dark:via-orange-950/30 dark:to-amber-950/40 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs">
                        <span class="material-symbols-outlined text-[24px]">timer</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                Presensi Shift Hari Ini Belum Dicatat
                            </h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-200/70 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300">
                                {{ $staffRole }}
                            </span>
                        </div>
                        <p class="text-xs text-amber-800/90 dark:text-amber-300/90 mt-0.5 leading-relaxed">
                            Halo <strong class="text-amber-950 dark:text-amber-100 font-bold">{{ $staffName }}</strong>, Anda belum melakukan Absen Hadir hari ini. Silakan klik tombol <strong class="text-amber-950 dark:text-amber-100">Absen Masuk</strong> untuk memulai shift dan melayani transaksi.
                        </p>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2 pl-13 md:pl-0">
                    <button wire:click="openModal" type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm hover:shadow transition cursor-pointer active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        <span>Absen Masuk Sekarang</span>
                    </button>
                </div>
            </div>
        @elseif($hasCheckedIn && ! $hasCheckedOut)
            {{-- OPTIONAL SLIM STATUS BAR UNTUK SHIFT AKTIF --}}
            <div class="mb-4 px-4 py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-700/60 bg-emerald-50/80 dark:bg-emerald-950/30 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2.5 text-emerald-800 dark:text-emerald-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Shift Aktif (Masuk: <strong>{{ $checkInTime }} WIB</strong>)</span>
                </div>
                <button wire:click="openModal" type="button" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    <span>Absen Pulang (Check-Out)</span>
                </button>
            </div>
        @endif

        {{-- 2. POP-UP MODAL INSTRUKSI & FORM ABSEN --}}
        @if($isOpen)
            <div
                x-data="{
                    lat: @entangle('latitude'),
                    lng: @entangle('longitude'),
                    locating: false,
                    gpsStatus: 'Mendeteksi lokasi...',
                    detectGps() {
                        if (navigator.geolocation) {
                            this.locating = true;
                            this.gpsStatus = 'Mengakses sensor GPS browser...';
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    this.lat = pos.coords.latitude.toFixed(6);
                                    this.lng = pos.coords.longitude.toFixed(6);
                                    this.locating = false;
                                    this.gpsStatus = 'GPS Terdeteksi: ' + this.lat + ', ' + this.lng;
                                },
                                (err) => {
                                    this.locating = false;
                                    this.gpsStatus = 'Akses GPS tidak diizinkan, presensi dicatat via IP Jaringan.';
                                },
                                { enableHighAccuracy: true, timeout: 7000 }
                            );
                        } else {
                            this.gpsStatus = 'Perangkat tidak mendukung GPS, presensi via IP Jaringan.';
                        }
                    }
                }"
                x-init="detectGps()"
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4 transition-all"
            >
                <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
                    {{-- Modal Header --}}
                    <div class="p-5 border-b border-slate-100 dark:border-slate-700/60 bg-gradient-to-r from-indigo-50/50 via-slate-50 to-emerald-50/50 dark:from-slate-800 dark:to-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shadow-xs">
                                <span class="material-symbols-outlined text-[24px]">{{ $hasCheckedIn ? 'logout' : 'alarm_on' }}</span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    {{ $hasCheckedIn ? 'Konfirmasi Absen Pulang (Check-Out)' : 'Presensi Masuk Shift Diperlukan' }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Konfirmasi kehadiran operasional harian dsBilling</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" type="button" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Tutup Modal">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-5 sm:p-6 space-y-4">
                        @if(! $hasCheckedIn)
                            {{-- Friendly Message Callout --}}
                            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-sm leading-relaxed flex items-start gap-3">
                                <span class="material-symbols-outlined text-amber-600 text-[22px] shrink-0 mt-0.5">info</span>
                                <div>
                                    <p class="font-semibold">
                                        Halo <strong class="font-black">{{ $staffName }}</strong>,
                                    </p>
                                    <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">
                                        Anda belum melakukan Absen Hadir hari ini. Silakan klik tombol <strong>Absen Masuk</strong> untuk memulai shift dan melayani transaksi operasional.
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- Check-out Confirmation Callout --}}
                            <div class="p-4 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 text-indigo-900 dark:text-indigo-200 text-sm leading-relaxed flex items-start gap-3">
                                <span class="material-symbols-outlined text-indigo-600 text-[22px] shrink-0 mt-0.5">verified</span>
                                <div>
                                    <p class="font-semibold">
                                        Shift Aktif: <strong class="font-black">{{ $staffName }}</strong>
                                    </p>
                                    <p class="mt-1 text-xs text-indigo-800 dark:text-indigo-300">
                                        Anda telah check-in pukul <strong>{{ $checkInTime }} WIB</strong>. Pastikan seluruh pekerjaan hari ini telah tercatat sebelum melakukan presensi pulang.
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($errorMessage)
                            <div class="p-3 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">error</span>
                                <span>{{ $errorMessage }}</span>
                            </div>
                        @endif

                        {{-- Info Box: Tanggal, Waktu & GPS --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-700">
                                <span class="block text-slate-400 text-[11px] font-semibold uppercase">Waktu Sekarang</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 text-sm mt-0.5 block">
                                    {{ date('d M Y') }} · {{ date('H:i') }} WIB
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-700">
                                <span class="block text-slate-400 text-[11px] font-semibold uppercase">Peran / Divisi</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm mt-0.5 block truncate">
                                    {{ $staffRole }}
                                </span>
                            </div>
                        </div>

                        {{-- GPS Status Pill --}}
                        <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-750 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300">
                            <div class="flex items-center gap-2 truncate">
                                <span class="material-symbols-outlined text-[18px] text-emerald-500" :class="{ 'animate-spin': locating }">
                                    <span x-show="!locating">location_on</span>
                                    <span x-show="locating">sync</span>
                                </span>
                                <span x-text="gpsStatus" class="truncate font-mono text-[11px]"></span>
                            </div>
                            <button type="button" @click="detectGps()" class="text-[11px] text-primary-600 hover:underline font-bold shrink-0 ml-2">
                                Ulangi GPS
                            </button>
                        </div>

                        {{-- Catatan Opsional --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                {{ $hasCheckedIn ? 'Catatan Selesai Shift / Ringkasan Pekerjaan' : 'Catatan Shift / Keterangan (Opsional)' }}
                            </label>
                            <input
                                wire:model="notes"
                                type="text"
                                placeholder="{{ $hasCheckedIn ? 'Contoh: Selesai shift, semua mutasi barang telah diupdate' : 'Contoh: Shift Pagi Gudang, Loket Kasir Kantor Pusat...' }}"
                                class="w-full px-3.5 py-2 text-xs rounded-xl bg-white dark:bg-slate-750 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="p-4 sm:px-6 bg-slate-50 dark:bg-slate-750/70 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-end gap-2.5">
                        <button
                            wire:click="closeModal"
                            type="button"
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                        >
                            Tutup
                        </button>

                        @if(! $hasCheckedIn)
                            <button
                                wire:click="checkIn"
                                wire:loading.attr="disabled"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md hover:shadow-lg transition cursor-pointer active:scale-95"
                            >
                                <span wire:loading.remove wire:target="checkIn" class="material-symbols-outlined text-[18px]">check_circle</span>
                                <span wire:loading wire:target="checkIn" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                                <span>Absen Masuk Sekarang</span>
                            </button>
                        @elseif($hasCheckedIn && ! $hasCheckedOut)
                            <button
                                wire:click="checkOut"
                                wire:loading.attr="disabled"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md hover:shadow-lg transition cursor-pointer active:scale-95"
                            >
                                <span wire:loading.remove wire:target="checkOut" class="material-symbols-outlined text-[18px]">logout</span>
                                <span wire:loading wire:target="checkOut" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                                <span>Absen Pulang Sekarang</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
