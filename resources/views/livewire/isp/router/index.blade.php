@section('page_title')
<div class="flex items-center gap-3">
  <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
  <span class="text-lg">Daftar Mikrotik (NAS)</span>
</div>
@endsection

<div class="space-y-5 pb-10">
    {{-- SESSION FLASH --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- TOP ACTION BAR --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-between sm:items-center bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('isp.routers.create') }}" class="inline-flex items-center justify-center p-2 sm:px-4 sm:py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap" title="Tambah Router">
                <span class="material-symbols-outlined notranslate sm:mr-1.5" translate="no" style="font-size:18px">add</span>
                <span class="hidden sm:inline">Tambah Router</span>
            </a>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari router..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <select wire:model.live="filters.status" class="w-full sm:w-auto px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Aksi</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama & Kode</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Lokasi POP</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">IP Address</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Sesi Aktif</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Status & Live</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    @forelse($routers as $router)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('isp.routers.show', $router->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 transition-colors" title="Buka Dashboard Panel Router">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">dashboard</span>
                                    </a>
                                    <button wire:click="generateProvisioningScript({{ $router->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 text-purple-600 dark:text-purple-400 transition-colors" title="Skrip Auto Config Mikrotik">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">terminal</span>
                                    </button>
                                    <button wire:click="checkConnection({{ $router->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors" title="Check Connection & Live Sync">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">network_ping</span>
                                    </button>
                                    <a href="{{ route('isp.routers.edit', $router->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="deleteRouter({{ $router->id }})" wire:confirm="Yakin ingin menghapus router ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    {{ $router->name }}
                                    @if($router->is_default)
                                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400 text-[10px] uppercase font-bold rounded-full">Default</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Kode: {{ $router->kode_router ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $router->lokasi_pop ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 font-mono text-sm text-slate-700 dark:text-slate-300">
                                {{ $router->ip_address }}<span class="text-slate-400">:{{ $router->port }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:16px">people</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ number_format($activeUsers[$router->id] ?? 0) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @php
                                        $isActive = in_array($router->status, ['active', 'online']);
                                        $isLive = $onlineStatus[$router->id] ?? $router->is_online;
                                        if (!$isLive && $router->last_seen_at && $router->last_seen_at->diffInMinutes(now()) <= 15) {
                                            $isLive = true;
                                        }
                                    @endphp
                                    @if($isActive)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                        </span>
                                    @endif

                                    @if($isActive)
                                        @if($isLive === true)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400" title="API Terhubung">
                                                <span class="material-symbols-outlined notranslate text-[12px]" translate="no">wifi</span> Terhubung
                                            </span>
                                        @elseif($isLive === false)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400" title="API Terputus">
                                                <span class="material-symbols-outlined notranslate text-[12px]" translate="no">wifi_off</span> Terputus
                                            </span>
                                        @else
                                            <button wire:click="checkConnection({{ $router->id }})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400 transition-colors" title="Klik untuk Cek & Sinkronkan">
                                                <span class="material-symbols-outlined notranslate text-[12px]" translate="no">refresh</span> Cek..
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-5xl mb-3 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no">router</span>
                                <p class="text-lg font-medium text-slate-900 dark:text-slate-100">Belum ada router</p>
                                <p class="text-sm mt-1">Silakan tambahkan router Mikrotik pertama Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($routers->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                {{ $routers->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL PROVISIONING SCRIPT (SINGLE INSTANCE) --}}
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-between items-center">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">terminal</span>
                        Skrip Auto Config MikroTik ({{ $scriptRouter?->name ?? 'Router' }})
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <span class="material-symbols-outlined notranslate text-[20px]" translate="no">close</span>
                    </button>
                </div>
                <div class="p-4 space-y-4">
                    <!-- Tab Selector -->
                    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-2">
                        <button type="button" wire:click="$set('modalTab', 'oneline')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer {{ $modalTab === 'oneline' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                            1. One-Line Fetch &amp; Import
                        </button>
                        <button type="button" wire:click="$set('modalTab', 'manual')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer {{ $modalTab === 'manual' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                            2. Skrip Manual (Terminal WinBox)
                        </button>
                    </div>

                    @if($modalTab === 'oneline')
                    <div class="space-y-3">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Jalankan perintah ini di WinBox &gt; <strong>New Terminal</strong> pada router MikroTik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                        <div class="relative bg-slate-950 rounded-xl p-4 border border-slate-800 shadow-inner group">
                            <div class="flex items-center gap-2 mb-2 border-b border-slate-800 pb-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                <span class="text-slate-400 text-[11px] font-mono ml-2">RouterOS Terminal (One-Liner)</span>
                            </div>
                            <code class="text-emerald-400 text-xs font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                            
                            <div x-data="{ copied: false }" class="absolute top-3 right-3 opacity-90 group-hover:opacity-100 transition-opacity">
                                <button type="button" 
                                        @click="window.copyToClipboard('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;', 'Perintah provisioning berhasil disalin!'); copied = true; setTimeout(() => copied = false, 2500)" 
                                        :class="copied ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 border-slate-700'"
                                        class="px-3 py-1.5 border rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm cursor-pointer transition-all duration-200" title="Salin Perintah">
                                    <template x-if="!copied">
                                        <span class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">content_copy</span>
                                            Salin Perintah
                                        </span>
                                    </template>
                                    <template x-if="copied">
                                        <span class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">check</span>
                                            Tersalin!
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="space-y-3">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Gunakan skrip manual di bawah jika router Anda belum memiliki akses internet atau DNS. Salin dan tempel langsung seluruh baris perintah berikut ke WinBox &gt; <strong>New Terminal</strong>.
                        </p>
                        <div class="relative bg-slate-950 rounded-xl p-4 border border-slate-800 shadow-inner group">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 border-b border-slate-800 pb-2 mb-2">
                                <span>Perintah Terminal WinBox:</span>
                                <span class="text-indigo-400 font-semibold">{{ $scriptRouter?->name }}</span>
                            </div>
                            <pre id="scriptCodeIndex" class="text-emerald-400 text-xs font-mono whitespace-pre-wrap select-all overflow-x-auto max-h-60 leading-relaxed">{{ $provisioningRawScript }}</pre>
                            
                            <div x-data="{ copied: false }" class="absolute top-3 right-3 opacity-90 group-hover:opacity-100 transition-opacity">
                                <button type="button" 
                                        @click="window.copyToClipboard(document.getElementById('scriptCodeIndex').innerText, 'Skrip manual berhasil disalin!'); copied = true; setTimeout(() => copied = false, 2500)" 
                                        :class="copied ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 border-slate-700'"
                                        class="px-3 py-1.5 border rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm cursor-pointer transition-all duration-200">
                                    <template x-if="!copied">
                                        <span class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">content_copy</span>
                                            Salin Skrip
                                        </span>
                                    </template>
                                    <template x-if="copied">
                                        <span class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined notranslate text-[16px]" translate="no">check</span>
                                            Tersalin!
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors cursor-pointer">
                        Selesai &amp; Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
