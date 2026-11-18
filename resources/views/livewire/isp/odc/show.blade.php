@section('page_title')
    <a href="{{ route('isp.odcs.index') }}" class="flex items-center text-slate-500 hover:text-slate-800 dark:text-slate-200 dark:hover:text-slate-100 transition-colors mr-2">
        <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
    </a>
    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm mr-2">
        {{ substr($odc->name ?? 'U', 0, 1) }}
    </div>
    <span class="text-lg">Detail ODC</span>
@endsection

<div class="space-y-6 pb-10">
    {{-- Header Content --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $odc->name }}</h1>
            <div class="flex items-center gap-3 mt-1 text-sm text-slate-500 dark:text-slate-400">
                <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $odc->code }}</span>
                <span class="px-2.5 py-0.5 font-semibold rounded-md text-xs bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                    {{ ucfirst($odc->status) }}
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('isp.odcs.edit', $odc->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold shadow-sm hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                Edit
            </a>
            <button type="button" wire:click="delete" wire:confirm="Yakin hapus data ini?" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl text-sm font-semibold hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                Hapus
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"><div class="p-4">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi ODC</h3>
            </x-slot>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Kode</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono">{{ $odc->code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Nama</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $odc->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">OLT</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $odc->olt?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">POP</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $odc->pop?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Jumlah Port</label>
                        <p class="text-slate-900 dark:text-slate-100 font-bold text-amber-600 dark:text-amber-400">{{ $odc->port_count ?: 16 }} Port</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Latitude</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono">{{ $odc->latitude ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Longitude</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono">{{ $odc->longitude ?? '-' }}</p>
                    </div>
                </div>
                @if($odc->latitude && $odc->longitude)
                <div class="pt-1">
                    <a href="https://www.google.com/maps?q={{ $odc->latitude }},{{ $odc->longitude }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors shadow-2xs">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Buka di Google Maps</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Alamat</label>
                    <p class="text-slate-900 dark:text-slate-100">{{ $odc->address ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Deskripsi</label>
                    <p class="text-slate-900 dark:text-slate-100">{{ $odc->description ?? '-' }}</p>
                </div>
            </div>
        </div></div>

        <div class="space-y-6">
            {{-- Foto Dokumentasi Tiang / Perangkat ODC --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500" style="font-size:20px">photo_camera</span>
                        Dokumentasi Tiang / Box ODC
                    </h3>
                    <a href="{{ route('isp.odcs.edit', $odc->id) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">edit</span> Edit / Ganti
                    </a>
                </div>
                <div class="p-4">
                    @php
                        $photos = is_array($odc->photos) ? $odc->photos : [];
                        $hasPhoto = !empty($photos) && !empty($photos[0]);
                    @endphp
                    @if($hasPhoto)
                        <div class="space-y-3">
                            <div class="relative group rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-950 flex items-center justify-center max-h-72">
                                <img src="{{ $photos[0] }}" class="w-full h-64 object-cover cursor-pointer hover:scale-105 transition-transform duration-300" onclick="window.open('{{ $photos[0] }}', '_blank')" onerror="this.onerror=null; this.src='/node-photo/odc/{{ $odc->id }}';" title="Klik untuk membuka ukuran penuh">
                                <div class="absolute bottom-2 right-2 px-2.5 py-1 bg-black/70 backdrop-blur-xs text-white text-[11px] font-medium rounded-lg pointer-events-none flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">zoom_in</span> Klik untuk perbesar
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">verified</span> Foto Lapangan Aktif
                                </span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('isp.odcs.edit', $odc->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">edit</span> Ubah
                                    </a>
                                    <button type="button" wire:click="deletePhoto" wire:confirm="Yakin ingin menghapus foto tiang ODC ini?" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-xs">delete</span> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-6 text-center">
                            <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-4xl mb-2">add_photo_alternate</span>
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Belum Ada Foto Tiang ODC</p>
                            <p class="text-[11px] text-slate-400 mb-4">Tambahkan foto fisik tiang/kabinet ODC untuk dokumentasi jaringan dan panduan teknisi.</p>
                            <a href="{{ route('isp.odcs.edit', $odc->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                <span class="material-symbols-outlined text-sm">add_a_photo</span> Unggah Foto Tiang Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Statistik Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Statistik ODC</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">ODP Terhubung</p>
                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $odc->odps->count() }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Kapasitas Port</p>
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $odc->port_count ?: 16 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
