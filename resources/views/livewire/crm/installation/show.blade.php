<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.installations.index') }}" 
               class="p-2.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition-all shadow-xs">
                <span class="material-symbols-outlined notranslate text-lg" translate="no">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $installation->customer_display_name }}</h1>
                    @php
                        $st = $installation->status;
                        $badge = match($st) {
                            'completed'   => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                            'in_progress' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                            'canceled'    => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                            default       => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                        };
                        $label = match($st) {
                            'completed'   => 'Selesai',
                            'in_progress' => 'Berlangsung',
                            'canceled'    => 'Dibatalkan',
                            default       => 'Dijadwalkan',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge }}">
                        {{ $label }}
                    </span>
                </div>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400 font-mono">Kode Instalasi: #{{ $installation->id }} | UUID: {{ $installation->uuid }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('crm.installations.edit', $installation->id) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow gap-1.5">
                <span class="material-symbols-outlined notranslate text-lg" translate="no">edit</span>
                Edit Instalasi
            </a>
        </div>
    </div>

    {{-- Detail Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Info Lengkap Pelanggan & Perangkat --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Informasi Pengerjaan --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no">assignment</span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Informasi Pengerjaan</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Nama Pelanggan</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $installation->customer_display_name }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Asal Data / Sumber</span>
                        @if($installation->customer_id)
                            <span class="text-slate-800 dark:text-slate-200 font-medium">Pelanggan Terdaftar (ID: #{{ $installation->customer_id }})</span>
                        @elseif($installation->survey_id)
                            <span class="text-indigo-600 dark:text-indigo-400 font-medium">Hasil Survey Lapangan #{{ $installation->survey_id }}</span>
                        @else
                            <span class="text-slate-600 dark:text-slate-300">Input Manual</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Jadwal Tanggal</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100">
                            {{ $installation->scheduled_date ? $installation->scheduled_date->translatedFormat('l, d F Y') : ($installation->scheduled_at ? $installation->scheduled_at->translatedFormat('l, d F Y') : '-') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Waktu / Jam</span>
                        <span class="font-mono text-slate-900 dark:text-slate-100 font-medium">
                            {{ $installation->scheduled_time ?? ($installation->scheduled_at ? $installation->scheduled_at->format('H:i') : '-') }} WIB
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Teknisi Penanggung Jawab</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $installation->technician_display_name }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Prioritas Pengerjaan</span>
                        <span class="capitalize font-semibold text-slate-800 dark:text-slate-200">{{ $installation->priority ?? 'medium' }}</span>
                    </div>
                </div>

                @if($installation->notes)
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mb-1">Catatan Instalasi:</span>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">
                            {{ $installation->notes }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Informasi Perangkat --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no">router</span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Data Perangkat yang Terpasang</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm font-mono">
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl">
                        <span class="text-xs text-slate-400 font-sans block mb-1">Serial Number ONU / ONT</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100">{{ $installation->onu_serial_number ?: '-' }}</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl">
                        <span class="text-xs text-slate-400 font-sans block mb-1">MAC Address ONU</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100">{{ $installation->onu_mac_address ?: '-' }}</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl">
                        <span class="text-xs text-slate-400 font-sans block mb-1">Model Router / Access Point</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100 font-sans">{{ $installation->router_model ?: '-' }}</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl">
                        <span class="text-xs text-slate-400 font-sans block mb-1">Serial Number Router</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100">{{ $installation->router_serial_number ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Meta & Status --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no">info</span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Riwayat & Pembuat</h2>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Dibuat Pada</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $installation->created_at ? $installation->created_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Terakhir Diperbarui</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $installation->updated_at ? $installation->updated_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Pembuat Dokumen</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $installation->createdBy?->name ?? 'Sistem' }}</span>
                    </div>
                    @if($installation->completed_at)
                        <div>
                            <span class="text-slate-400 block mb-0.5">Tanggal Selesai</span>
                            <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $installation->completed_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
