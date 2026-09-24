<div class="space-y-6 pb-10">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm">
        <span class="material-symbols-outlined notranslate" style="font-size:20px">check_circle</span>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-800 dark:text-red-300 text-sm">
        <span class="material-symbols-outlined notranslate" style="font-size:20px">error</span>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('isp.olts.index') }}" class="flex items-center text-slate-500 hover:text-slate-800 dark:text-slate-200 dark:hover:text-slate-100 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-base">
                {{ substr($olt->name ?? 'U', 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $olt->name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $olt->code }}</span>
                    <span class="px-2.5 py-0.5 font-semibold rounded-md text-xs
                        {{ $olt->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                        {{ $olt->status === 'active' ? 'Online' : 'Offline' }}
                    </span>
                    @if($olt->uptime_text)
                        <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-slate-600 dark:text-slate-400">Uptime: {{ $olt->uptime_text }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="syncOlt"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white border border-transparent rounded-xl text-sm font-semibold shadow-sm hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-all cursor-pointer"
                wire:loading.attr="disabled">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px"
                    wire:loading.class="animate-spin" wire:target="syncOlt">sync</span>
                <span wire:loading.remove wire:target="syncOlt">Sinkronisasi</span>
                <span wire:loading wire:target="syncOlt">Menyinkronkan...</span>
            </button>
            <a href="{{ route('isp.olts.edit', $olt->id) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold shadow-sm hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                Edit
            </a>
            <button type="button" wire:click="delete" wire:confirm="Yakin hapus data ini?"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl text-sm font-semibold hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                Hapus
            </button>
        </div>
    </div>

    {{-- Info + PON Ports Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Informasi OLT --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Informasi OLT</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Kode</label>
                            <p class="text-slate-900 dark:text-slate-100 font-mono text-sm">{{ $olt->code }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Nama</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">POP</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->pop->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Vendor</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->vendor->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Model</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->model ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Serial Number</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm font-mono">{{ $olt->serial_number ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Firmware</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->firmware_version ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">IP Address</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm font-mono">{{ $olt->ip_address ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Jumlah Port</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->port_count ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Uptime</label>
                            <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->uptime_text ?? '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Deskripsi</label>
                        <p class="text-slate-900 dark:text-slate-100 text-sm">{{ $olt->description ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribusi PON Port --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Distribusi Port PON</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-3 pb-4 border-b border-slate-200 dark:border-slate-700">
                        <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-100 dark:border-slate-700">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total PON Port</p>
                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $olt->ponPorts->count() }}</p>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-100 dark:border-emerald-800">
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 mb-1">ONU Online</p>
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $olt->onus->where('last_seen_at', '>=', now()->subMinutes(5))->count() }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-100 dark:border-slate-700">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">ONU Offline</p>
                            <p class="text-2xl font-bold text-slate-600 dark:text-slate-400">{{ $olt->onus->filter(fn($o) => !$o->last_seen_at || $o->last_seen_at < now()->subMinutes(5))->count() }}</p>
                        </div>
                    </div>

                    <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                        @forelse($olt->ponPorts as $port)
                        @php
                            $portOnline  = $port->onus->where('status', 'active')->count();
                            $portOffline = $port->onus->where('status', 'inactive')->count();
                            $portTotal   = $port->onus->count();
                        @endphp
                        <a href="{{ route('isp.onus.index', ['ponFilter' => $port->id, 'oltFilter' => $olt->id]) }}"
                            class="group flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/30 border border-slate-200 dark:border-slate-700 rounded-lg hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-sm transition-all cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-md bg-indigo-50 dark:bg-indigo-900/30 group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm transition-colors">
                                    {{ $port->port_number ?? $loop->iteration }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $port->name ?? 'PON ' . $port->port_number }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $port->type ? strtoupper($port->type) . ' Port' : 'Gpon Port' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($portOnline > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>{{ $portOnline }}
                                </span>
                                @endif
                                @if($portOffline > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300">
                                    {{ $portOffline }} off
                                </span>
                                @endif
                                <span class="px-2.5 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-md text-xs font-bold">
                                    {{ $portTotal }} ONU
                                </span>
                                <span class="material-symbols-outlined notranslate text-slate-400 dark:text-slate-500 dark:text-slate-400 group-hover:text-indigo-500 transition-colors" style="font-size:18px">chevron_right</span>
                            </div>
                        </a>
                        @empty
                        <div class="text-center py-8 text-sm text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined notranslate block mb-2 opacity-40" style="font-size:36px">lan</span>
                            Belum ada port PON yang terdaftar.<br>
                            Klik Sinkronisasi untuk mengambil data dari OLT.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
