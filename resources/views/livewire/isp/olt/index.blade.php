@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">dns</span>
    <span class="text-lg">Data OLT</span>
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

    
    {{-- PANDUAN KONEKSI OLT --}}
    <div x-data="{ showGuide: false }" class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-xl overflow-hidden mb-6">
        <button @click="showGuide = !showGuide" class="w-full flex items-center justify-between p-4 text-left focus:outline-none">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400" style="font-size:24px">help</span>
                <span class="font-semibold text-indigo-900 dark:text-indigo-100">Panduan Mengatasi OLT Offline & Gagal Tes Koneksi</span>
            </div>
            <span class="material-symbols-outlined text-indigo-500 transform transition-transform" :class="showGuide ? 'rotate-180' : ''">expand_more</span>
        </button>
        <div x-show="showGuide" x-collapse>
            <div class="p-4 pt-0 text-sm text-indigo-800 dark:text-indigo-300 space-y-4">
                <p>Jika <strong>Test Koneksi</strong> selalu menghasilkan error atau OLT terus menerus berstatus <strong>Offline</strong>, kemungkinan besar server billing ini tidak bisa menemukan/menembus jalur ke IP OLT Anda. Gunakan salah satu arsitektur jaringan berikut:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-lg border border-indigo-100 dark:border-indigo-800/50">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-2">Opsi 1: Menggunakan IP Publik (Port Forwarding)</h4>
                        <ul class="list-disc pl-4 space-y-1 text-slate-600 dark:text-slate-400">
                            <li>Mikrotik di lokasi OLT harus punya <strong>IP Publik Statis</strong>.</li>
                            <li>Buat <strong>DST-NAT</strong> di Mikrotik untuk port <strong>161 (UDP)</strong> dan <strong>23 (TCP)</strong>.</li>
                            <li>Arahkan port tersebut ke <strong>IP Lokal OLT</strong> (misal 192.168.100.2).</li>
                            <li>Di aplikasi ini, masukkan <strong>IP Publik Mikrotik</strong> ke kolom IP Address.</li>
                            <li class="text-red-500 font-semibold mt-1">Penting: Wajib filter IP agar tidak di-hack!</li>
                        </ul>
                        <div x-data="{ copied: false }" class="mt-3 bg-slate-900 rounded-md p-3 text-xs text-green-400 font-mono overflow-x-auto relative group">
                            <button @click="navigator.clipboard.writeText($refs.script1.innerText.trim()); copied = true; setTimeout(() => copied = false, 2000)" 
                                class="absolute top-2 right-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white px-2 py-1 rounded-md transition-all opacity-0 group-hover:opacity-100 flex items-center gap-1 border border-slate-600 shadow-md">
                                <span class="material-symbols-outlined notranslate" style="font-size:14px" x-text="copied ? 'check' : 'content_copy'"></span>
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
<pre x-ref="script1" class="pt-2">
# 1. Forwarding Port (Ganti 192.168.100.2 dgn IP Lokal OLT)
/ip firewall nat
add chain=dstnat protocol=udp dst-port=161 action=dst-nat to-addresses=192.168.100.2 to-ports=161
add chain=dstnat protocol=tcp dst-port=23 action=dst-nat to-addresses=192.168.100.2 to-ports=23

# 2. Keamanan Filter (Ganti X.X.X.X dgn IP Server Billing)
/ip firewall filter
add chain=forward src-address=X.X.X.X dst-address=192.168.100.2 action=accept
add chain=forward dst-address=192.168.100.2 action=drop
</pre>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-lg border border-indigo-100 dark:border-indigo-800/50">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-2">Opsi 2: Menggunakan VPN / Tunnel (Rekomendasi)</h4>
                        <ul class="list-disc pl-4 space-y-1 text-slate-600 dark:text-slate-400">
                            <li>Buat jalur VPN (contoh: L2TP/Wireguard) antara server billing dan Mikrotik site OLT.</li>
                            <li>Buat <strong>Static Route</strong> di server billing menuju subnet lokal OLT melalui jalur VPN.</li>
                            <li>Buat NAT Masquerade out-interface (port yg mengarah ke OLT) di Mikrotik.</li>
                            <li>Di aplikasi ini, masukkan <strong>IP Asli Lokal OLT</strong> (misal 192.168.100.2).</li>
                        </ul>
                        <div x-data="{ copied: false }" class="mt-3 bg-slate-900 rounded-md p-3 text-xs text-green-400 font-mono overflow-x-auto relative group">
                            <button @click="navigator.clipboard.writeText($refs.script2.innerText.trim()); copied = true; setTimeout(() => copied = false, 2000)" 
                                class="absolute top-2 right-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white px-2 py-1 rounded-md transition-all opacity-0 group-hover:opacity-100 flex items-center gap-1 border border-slate-600 shadow-md">
                                <span class="material-symbols-outlined notranslate" style="font-size:14px" x-text="copied ? 'check' : 'content_copy'"></span>
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
<pre x-ref="script2" class="pt-2">
# Contoh jika kabel OLT dicolok ke ether5 mikrotik
/ip firewall nat
add chain=srcnat out-interface=ether5 action=masquerade comment="NAT to OLT"

# Pastikan mikrotik ping ke IP server billing & sebaliknya me-reply
</pre>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <h4 class="font-bold text-amber-800 dark:text-amber-300 mb-1">Sudah terhubung tapi informasi MAC/Hardware kosong?</h4>
                    <p class="text-amber-700 dark:text-amber-400">
                        Pastikan Anda sudah login ke CLI OLT Anda dan mengetik perintah: <code>snmp-server community public ro</code> dan <code>snmp-server enable</code>.
                        Dan pastikan <strong>Vendor</strong> yang Anda pilih saat mendaftarkan OLT benar-benar <strong>CDATA</strong> atau <strong>HSGQ</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('isp.olts.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">add</span>
                Tambah OLT
            </a>
            <button wire:click="export" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">download</span>
                Export
            </button>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari kode, nama..."
                       class="w-full pl-11 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <select wire:model.live="filters.status"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Semua Status</option>
                <option value="active" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Aktif</option>
                <option value="inactive" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Nonaktif</option>
            </select>
            <select wire:model.live="perPage"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">10 / halaman</option>
                <option value="25" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">25 / halaman</option>
                <option value="50" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">50 / halaman</option>
                <option value="100" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">100 / halaman</option>
            </select>
            @if($search || $filters['status'])
            <button wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                Reset
            </button>
            @endif
        </div>
    </div>
    
    @if(!empty($selectedOLTs ?? $selectedOLT ?? $selectedOdcs ?? $selectedOdps ?? $selectedOlts))
    <div class="flex flex-wrap items-center gap-2 p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 rounded-lg">
        <span class="text-sm text-indigo-800 dark:text-indigo-300 font-medium mr-2">{{ count($selectedOlts ?? $selectedOdcs ?? $selectedOdps ?? $selectedOlts) }} Terpilih</span>
        <button wire:click="bulkActivate" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded transition-colors shadow-sm">Aktifkan</button>
        <button wire:click="bulkDeactivate" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded transition-colors shadow-sm">Nonaktifkan</button>
        <button wire:click="bulkDelete" wire:confirm="Yakin hapus data terpilih?" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition-colors shadow-sm">Hapus Terpilih</button>
    </div>
    @endif
    

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80">
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-primary-600 border-gray-300 dark:border-slate-600 rounded focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100" />
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama OLT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Alamat</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">POP</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">ONU Online</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ONU Offline</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>

                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($olts as $olt)
                        @php
                            $onuOnline  = $olt->onus_online_count  ?? 0;
                            $onuOffline = $olt->onus_offline_count ?? 0;
                        @endphp
                        <tr wire:key="{{ $olt->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 dark:bg-slate-800 transition-colors @if($olt->trashed()) bg-red-50 dark:bg-red-900/20 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedOlts" value="{{ $olt->id }}" class="w-4 h-4 text-primary-600 border-gray-300 dark:border-slate-600 rounded focus:ring-primary-500" @if($olt->trashed()) disabled @endif />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($olt->trashed())
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/50 text-red-800">Dihapus</span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $olt->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                                        {{ $olt->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">
                                    <a href="{{ route('isp.olts.show', $olt->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $olt->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-slate-400 truncate max-w-[200px]" title="{{ $olt->address }}">{{ $olt->address ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-900 dark:text-slate-100">{{ $olt->code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-slate-100">{{ $olt->pop->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600 dark:text-slate-300">{{ $olt->vendor->name ?? '-' }}</div>
                            </td>
                            {{-- ONU Online --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($onuOnline > 0)
                                    <a href="{{ route('isp.onus.index', ['oltFilter' => $olt->id, 'statusFilter' => 'online']) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-800/50 transition-colors">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        {{ $onuOnline }}
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 dark:text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            {{-- ONU Offline --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($onuOffline > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                        {{ $onuOffline }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 dark:text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            {{-- Aksi --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Sinkron --}}
                                    <button type="button" wire:click="rowSync({{ $olt->id }})" wire:loading.attr="disabled"
                                        class="p-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded hover:bg-indigo-100 dark:bg-indigo-900/50 dark:hover:bg-indigo-800/50 transition-colors" title="Sinkronisasi">
                                        <span class="material-symbols-outlined notranslate" wire:loading.class="animate-spin" wire:target="rowSync({{ $olt->id }})" translate="no" style="font-size:16px">sync</span>
                                    </button>
                                    {{-- Detail --}}
                                    <a href="{{ route('isp.olts.show', $olt->id) }}" class="p-1.5 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-600 transition-colors" title="Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">visibility</span>
                                    </a>
                                    {{-- Edit --}}
                                    <a href="{{ route('isp.olts.edit', $olt->id) }}" class="p-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded hover:bg-amber-100 dark:bg-amber-900/50 dark:hover:bg-amber-800/50 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">edit</span>
                                    </a>
                                    {{-- Hapus --}}
                                    <button type="button" wire:click="delete({{ $olt->id }})" wire:confirm="Yakin ingin menghapus OLT ini?" class="p-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400 dark:text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-1">Belum ada OLT</h3>
                                    <p class="text-gray-500 dark:text-slate-400 mb-6">Silakan tambahkan OLT pertama.</p>
                                    <a href="{{ route('isp.olts.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah OLT
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($olts->hasPages())
            <div class="bg-white dark:bg-slate-800 px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                {{ $olts->links() }}
            </div>
        @endif
    </div>

</div>


