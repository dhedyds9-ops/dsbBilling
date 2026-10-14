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

    {{-- STATISTIK KARTU OLT & ONU --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card Total OLT --}}
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total OLT</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $stats['total_olt'] ?? 0 }}</span>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ $stats['active_olt'] ?? 0 }} Aktif</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" style="font-size:24px">dns</span>
            </div>
        </div>

        {{-- Card Total ONU --}}
        <a href="{{ route('isp.onus.index') }}" class="group bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-purple-300 dark:hover:border-purple-600 hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Total ONU</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $stats['total_onu'] ?? 0 }}</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Terdaftar</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 group-hover:scale-105 transition-transform flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" style="font-size:24px">router</span>
            </div>
        </a>

        {{-- Card ONU Online --}}
        <a href="{{ route('isp.onus.index', ['statusFilter' => 'online']) }}" class="group bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-emerald-300 dark:hover:border-emerald-600 hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">ONU Online</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['online_onu'] ?? 0 }}</span>
                    @if(($stats['total_onu'] ?? 0) > 0)
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/40 px-1.5 py-0.5 rounded">{{ $stats['online_percentage'] ?? 0 }}%</span>
                    @endif
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" style="font-size:24px">wifi</span>
            </div>
        </a>

        {{-- Card ONU Offline --}}
        <a href="{{ route('isp.onus.index', ['statusFilter' => 'offline']) }}" class="group bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-red-300 dark:hover:border-red-600 hover:shadow-md transition-all flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">ONU Offline</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-red-600 dark:text-red-400">{{ $stats['offline_onu'] ?? 0 }}</span>
                    <span class="text-xs font-medium text-red-500 dark:text-red-400">Terputus</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 group-hover:scale-105 transition-transform flex items-center justify-center">
                <span class="material-symbols-outlined notranslate" style="font-size:24px">wifi_off</span>
            </div>
        </a>
    </div>

    {{-- PANDUAN KONEKSI OLT --}}
    {{-- PANDUAN KONEKSI OLT --}}
    <div x-data="{ showGuide: false, guideTab: 'multi' }" class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-xl overflow-hidden mb-6">
        <button @click="showGuide = !showGuide" class="w-full flex items-center justify-between p-4 text-left focus:outline-none hover:bg-indigo-100/50 dark:hover:bg-indigo-900/50 transition-colors">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400" style="font-size:24px">help</span>
                <div>
                    <span class="font-semibold text-indigo-900 dark:text-indigo-100">Panduan Konfigurasi OLT & Solusi Banyak OLT (1 IP Publik)</span>
                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5">Petunjuk Port Forwarding, Masquerade, serta trik menghubungkan banyak OLT dengan satu IP Publik.</p>
                </div>
            </div>
            <span class="material-symbols-outlined text-indigo-500 transform transition-transform" :class="showGuide ? 'rotate-180' : ''">expand_more</span>
        </button>
        <div x-show="showGuide" x-collapse>
            <div class="p-4 pt-0 text-sm text-indigo-800 dark:text-indigo-300 space-y-4">
                @php
                    $serverIp = request()->server('SERVER_ADDR') ?? (filter_var(request()->getHost(), FILTER_VALIDATE_IP) ? request()->getHost() : 'IP_SERVER_BILLING');
                    if (!filter_var($serverIp, FILTER_VALIDATE_IP)) {
                        $serverIp = 'IP_SERVER_BILLING';
                    }
                @endphp

                {{-- TAB BUTTONS --}}
                <div class="flex flex-wrap gap-2 border-b border-indigo-200 dark:border-indigo-800 pb-3">
                    <button type="button" @click="guideTab = 'multi'" 
                            :class="guideTab === 'multi' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-slate-700'"
                            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 border border-indigo-200 dark:border-slate-700">
                        <span class="material-symbols-outlined notranslate" style="font-size:16px">hub</span>
                        <span>Banyak OLT (1 IP Publik)</span>
                        <span class="px-1.5 py-0.2 bg-amber-400 text-slate-900 rounded text-[10px] font-bold">Populer</span>
                    </button>
                    <button type="button" @click="guideTab = 'single'" 
                            :class="guideTab === 'single' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-slate-700'"
                            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 border border-indigo-200 dark:border-slate-700">
                        <span class="material-symbols-outlined notranslate" style="font-size:16px">router</span>
                        <span>1 OLT (1 IP Publik Standar)</span>
                    </button>
                    <button type="button" @click="guideTab = 'vpn'" 
                            :class="guideTab === 'vpn' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-100 dark:hover:bg-slate-700'"
                            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 border border-indigo-200 dark:border-slate-700">
                        <span class="material-symbols-outlined notranslate" style="font-size:16px">vpn_lock</span>
                        <span>Mode VPN Tunnel</span>
                    </button>
                </div>

                {{-- TAB CONTENT 1: BANYAK OLT DENGAN 1 IP PUBLIK --}}
                <div x-show="guideTab === 'multi'" class="space-y-3">
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/50 space-y-3">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-amber-500 mt-0.5" style="font-size:20px">lightbulb</span>
                            <div>
                                <h4 class="font-bold text-slate-800 dark:text-slate-200">Bagaimana Cara Menghubungkan Banyak OLT Jika IP Publik MikroTik Cuma 1?</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                    Karena port standar SNMP (<code>161</code>) dan Telnet (<code>23</code>) tidak bisa dibagi dua ke IP yang berbeda, solusinya adalah menggunakan <strong>Port Mapping (Port Translation)</strong> di MikroTik. 
                                    Setiap OLT lokal dipetakan ke <strong>nomor port eksternal yang unik</strong>, sementara port internal ke OLT tetap 161 dan 23.
                                </p>
                            </div>
                        </div>

                        {{-- TABEL PEMETAAN PORT --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                <thead class="bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-semibold">
                                    <tr>
                                        <th class="p-2 border-b border-slate-200 dark:border-slate-700">Perangkat OLT</th>
                                        <th class="p-2 border-b border-slate-200 dark:border-slate-700">IP Lokal OLT</th>
                                        <th class="p-2 border-b border-slate-200 dark:border-slate-700">Input IP di dsBilling</th>
                                        <th class="p-2 border-b border-slate-200 dark:border-slate-700">SNMP Port (UDP)</th>
                                        <th class="p-2 border-b border-slate-200 dark:border-slate-700">CLI Port (TCP)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 font-mono text-slate-600 dark:text-slate-300">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-750">
                                        <td class="p-2 font-sans font-bold text-indigo-600 dark:text-indigo-400">OLT #1</td>
                                        <td class="p-2">192.168.200.10</td>
                                        <td class="p-2 text-slate-800 dark:text-slate-200">IP Publik MikroTik</td>
                                        <td class="p-2 text-emerald-600 font-bold">1611</td>
                                        <td class="p-2 text-emerald-600 font-bold">2301</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-750">
                                        <td class="p-2 font-sans font-bold text-indigo-600 dark:text-indigo-400">OLT #2</td>
                                        <td class="p-2">192.168.200.11</td>
                                        <td class="p-2 text-slate-800 dark:text-slate-200">IP Publik MikroTik</td>
                                        <td class="p-2 text-emerald-600 font-bold">1612</td>
                                        <td class="p-2 text-emerald-600 font-bold">2302</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-750">
                                        <td class="p-2 font-sans font-bold text-indigo-600 dark:text-indigo-400">OLT #3</td>
                                        <td class="p-2">192.168.200.12</td>
                                        <td class="p-2 text-slate-800 dark:text-slate-200">IP Publik MikroTik</td>
                                        <td class="p-2 text-emerald-600 font-bold">1613</td>
                                        <td class="p-2 text-emerald-600 font-bold">2303</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- SKRIP MIKROTIK BANYAK OLT --}}
                        <div x-data="{ copied: false }" class="bg-slate-900 rounded-md p-3 text-xs text-green-400 font-mono overflow-x-auto relative group">
                            <button type="button" @click="window.copyToClipboard($refs.scriptMulti.innerText.trim(), 'Skrip Multi-OLT Port Forwarding berhasil disalin!'); copied = true; setTimeout(() => copied = false, 2500)" 
                                :class="copied ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white border-slate-600'"
                                class="absolute top-2 right-2 px-2.5 py-1 rounded-md transition-all flex items-center gap-1 border shadow-md">
                                <span class="material-symbols-outlined notranslate" style="font-size:14px" x-text="copied ? 'check' : 'content_copy'"></span>
                                <span x-text="copied ? 'Tersalin!' : 'Salin Skrip Multi-OLT'"></span>
                            </button>
<pre x-ref="scriptMulti" class="pt-2"># =================================================================
# dsBilling Multi-OLT Setup Script (1 IP Publik untuk Banyak OLT)
# Sesuaikan IP OLT (192.168.200.10, .11, dst) dan Subnet OLT Anda
# =================================================================
/
:do { /ip firewall nat remove [find comment~"managed-by=dsbilling: Multi-OLT"] } on-error={ :nothing }
:do { /ip firewall filter remove [find comment~"managed-by=dsbilling: Multi-OLT"] } on-error={ :nothing }

# -----------------------------------------------------------------
# 1. Forwarding Port OLT #1 (IP Lokal: 192.168.200.10)
# -----------------------------------------------------------------
/ip firewall nat add chain=dstnat protocol=udp dst-port=1611 dst-address-type=local action=dst-nat to-addresses=192.168.200.10 to-ports=161 comment="managed-by=dsbilling: Multi-OLT 1 SNMP"
/ip firewall nat add chain=dstnat protocol=tcp dst-port=2301 dst-address-type=local action=dst-nat to-addresses=192.168.200.10 to-ports=23 comment="managed-by=dsbilling: Multi-OLT 1 Telnet"

# -----------------------------------------------------------------
# 2. Forwarding Port OLT #2 (IP Lokal: 192.168.200.11)
# -----------------------------------------------------------------
/ip firewall nat add chain=dstnat protocol=udp dst-port=1612 dst-address-type=local action=dst-nat to-addresses=192.168.200.11 to-ports=161 comment="managed-by=dsbilling: Multi-OLT 2 SNMP"
/ip firewall nat add chain=dstnat protocol=tcp dst-port=2302 dst-address-type=local action=dst-nat to-addresses=192.168.200.11 to-ports=23 comment="managed-by=dsbilling: Multi-OLT 2 Telnet"

# -----------------------------------------------------------------
# 3. Forwarding Port OLT #3 (IP Lokal: 192.168.200.12)
# -----------------------------------------------------------------
/ip firewall nat add chain=dstnat protocol=udp dst-port=1613 dst-address-type=local action=dst-nat to-addresses=192.168.200.12 to-ports=161 comment="managed-by=dsbilling: Multi-OLT 3 SNMP"
/ip firewall nat add chain=dstnat protocol=tcp dst-port=2303 dst-address-type=local action=dst-nat to-addresses=192.168.200.12 to-ports=23 comment="managed-by=dsbilling: Multi-OLT 3 Telnet"

# -----------------------------------------------------------------
# 4. Masquerade Subnet OLT (PENTING: Agar balasan OLT kembali lewat MikroTik)
# Ganti 192.168.200.0/24 dengan subnet OLT di jaringan lokal Anda
# -----------------------------------------------------------------
/ip firewall nat add chain=srcnat dst-address=192.168.200.0/24 action=masquerade comment="managed-by=dsbilling: Multi-OLT Masquerade"

# -----------------------------------------------------------------
# 5. Filter Keamanan (Hanya Server Billing {{ $serverIp }} yang Diizinkan Akses)
# -----------------------------------------------------------------
/ip firewall filter add chain=forward src-address={{ $serverIp }} dst-address=192.168.200.0/24 action=accept comment="managed-by=dsbilling: Multi-OLT Allow Billing" place-before=0
/ip firewall filter add chain=forward dst-address=192.168.200.0/24 protocol=tcp dst-port=23 action=drop comment="managed-by=dsbilling: Multi-OLT Drop Public Telnet" place-before=1
/ip firewall filter add chain=forward dst-address=192.168.200.0/24 protocol=udp dst-port=161 action=drop comment="managed-by=dsbilling: Multi-OLT Drop Public SNMP" place-before=2
/
</pre>
                        </div>
                    </div>
                </div>

                {{-- TAB CONTENT 2: 1 OLT (1 IP PUBLIK STANDAR) --}}
                <div x-show="guideTab === 'single'" class="space-y-3">
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/50 space-y-3">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">1 OLT Menggunakan Port Standar (161 UDP & 23 TCP)</h4>
                        <ul class="list-disc pl-4 space-y-1 text-xs text-slate-600 dark:text-slate-400">
                            <li>Mikrotik di lokasi OLT memiliki <strong>IP Publik Statis</strong>.</li>
                            <li>Port standar <strong>161 (SNMP)</strong> dan <strong>23 (Telnet)</strong> diarahkan ke 1 IP lokal OLT (misal 192.168.200.10).</li>
                            <li>Di form tambah OLT dsBilling, masukkan <strong>IP Publik Mikrotik</strong>, SNMP Port <strong>161</strong>, dan CLI Port <strong>23</strong>.</li>
                            <li class="text-red-500 font-semibold mt-1">Wajib menerapkan Filter IP agar perangkat OLT terlindungi dari serangan scanner publik.</li>
                        </ul>
                        <div x-data="{ copied: false }" class="mt-3 bg-slate-900 rounded-md p-3 text-xs text-green-400 font-mono overflow-x-auto relative group">
                            <button type="button" @click="window.copyToClipboard($refs.script1.innerText.trim(), 'Skrip OLT Port Forwarding berhasil disalin!'); copied = true; setTimeout(() => copied = false, 2500)" 
                                :class="copied ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white border-slate-600'"
                                class="absolute top-2 right-2 px-2.5 py-1 rounded-md transition-all flex items-center gap-1 border shadow-md">
                                <span class="material-symbols-outlined notranslate" style="font-size:14px" x-text="copied ? 'check' : 'content_copy'"></span>
                                <span x-text="copied ? 'Tersalin!' : 'Salin Skrip'"></span>
                            </button>
<pre x-ref="script1" class="pt-2"># =================================================================
# Port Forwarding OLT Tunggal ke dsBilling (Mode IP Publik Standar)
# Ganti 192.168.200.10 dengan IP Lokal OLT Anda
# =================================================================
/
:do { /ip firewall nat remove [find comment~"managed-by=dsbilling: OLT"] } on-error={ :nothing }
:do { /ip firewall filter remove [find comment~"managed-by=dsbilling: OLT"] } on-error={ :nothing }

# 1. Forward Port SNMP (UDP 161) dan Telnet (TCP 23)
/ip firewall nat add chain=dstnat protocol=udp dst-port=161 dst-address-type=local action=dst-nat to-addresses=192.168.200.10 to-ports=161 comment="managed-by=dsbilling: OLT SNMP"
/ip firewall nat add chain=dstnat protocol=tcp dst-port=23 dst-address-type=local action=dst-nat to-addresses=192.168.200.10 to-ports=23 comment="managed-by=dsbilling: OLT Telnet"

# 2. Masquerade Balik (PENTING: Mencegah paket balasan OLT tersesat)
/ip firewall nat add chain=srcnat dst-address=192.168.200.10 action=masquerade comment="managed-by=dsbilling: OLT Masquerade"

# 3. Filter Keamanan (Hanya Server Billing {{ $serverIp }} yang Diizinkan Akses OLT)
/ip firewall filter add chain=forward src-address={{ $serverIp }} dst-address=192.168.200.10 action=accept comment="managed-by=dsbilling: OLT Allow Billing" place-before=0
/ip firewall filter add chain=forward dst-address=192.168.200.10 protocol=tcp dst-port=23 action=drop comment="managed-by=dsbilling: OLT Drop Public Telnet" place-before=1
/ip firewall filter add chain=forward dst-address=192.168.200.10 protocol=udp dst-port=161 action=drop comment="managed-by=dsbilling: OLT Drop Public SNMP" place-before=2
/
</pre>
                        </div>
                    </div>
                </div>

                {{-- TAB CONTENT 3: MODE VPN TUNNEL --}}
                <div x-show="guideTab === 'vpn'" class="space-y-3">
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/50 space-y-3">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">Mode VPN Tunnel (Tanpa Membuka IP Publik)</h4>
                        <ul class="list-disc pl-4 space-y-1 text-xs text-slate-600 dark:text-slate-400">
                            <li>Hubungkan Mikrotik ke <strong>VPN dsBilling</strong> (tanpa perlu IP Publik statis).</li>
                            <li>Buat <strong>NAT Masquerade</strong> ke arah IP lokal OLT agar OLT dapat membalas traffic dari VPN.</li>
                            <li>Di aplikasi dsBilling, masukkan langsung <strong>IP Asli Lokal OLT</strong> (misal 192.168.200.10).</li>
                        </ul>
                        <div x-data="{ copied: false }" class="mt-3 bg-slate-900 rounded-md p-3 text-xs text-green-400 font-mono overflow-x-auto relative group">
                            <button type="button" @click="window.copyToClipboard($refs.script2.innerText.trim(), 'Skrip OLT VPN Masquerade berhasil disalin!'); copied = true; setTimeout(() => copied = false, 2500)" 
                                :class="copied ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white border-slate-600'"
                                class="absolute top-2 right-2 px-2.5 py-1 rounded-md transition-all flex items-center gap-1 border shadow-md">
                                <span class="material-symbols-outlined notranslate" style="font-size:14px" x-text="copied ? 'check' : 'content_copy'"></span>
                                <span x-text="copied ? 'Tersalin!' : 'Salin Skrip'"></span>
                            </button>
<pre x-ref="script2" class="pt-2"># =================================================================
# Masquerade OLT ke dsBilling (Mode VPN Tunnel)
# Ganti 192.168.200.10 dengan IP Lokal OLT Anda
# =================================================================
/
:do { /ip firewall nat remove [find comment~"managed-by=dsbilling: OLT Masquerade"] } on-error={ :nothing }

# 1. Masquerade Traffic ke OLT (Memungkinkan Server Billing Akses Subnet OLT via VPN)
/ip firewall nat add chain=srcnat dst-address=192.168.200.10 action=masquerade comment="managed-by=dsbilling: OLT Masquerade"

# 2. Tes Ping ke IP OLT
/ping 192.168.200.10 count=3
/
</pre>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <h4 class="font-bold text-amber-800 dark:text-amber-300 mb-1">Sudah terhubung tapi informasi MAC/Hardware kosong?</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-400">
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
            <a href="{{ route('isp.onus.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-emerald-200 dark:shadow-none transition-all" title="Daftar & Manajemen Semua ONU">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">router</span>
                Kelola ONU
            </a>
            <button wire:click="syncAll" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-blue-200 dark:shadow-none transition-all cursor-pointer" title="Sinkronkan status semua OLT">
                <span class="material-symbols-outlined notranslate mr-1.5" wire:loading.class="animate-spin" wire:target="syncAll" translate="no" style="font-size:18px">sync</span>
                <span wire:loading.remove wire:target="syncAll">Sinkronkan Semua</span>
                <span wire:loading wire:target="syncAll">Sinkronisasi...</span>
            </button>
            <button wire:click="export" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all cursor-pointer">
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
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama OLT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">POP</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">ONU Online</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ONU Offline</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button wire:click="rowSync({{ $olt->id }})" wire:loading.attr="disabled" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 dark:text-indigo-400 transition-colors cursor-pointer" title="Sinkronkan OLT Sekarang">
                                        <span class="material-symbols-outlined notranslate" wire:loading.class="animate-spin" wire:target="rowSync({{ $olt->id }})" translate="no" style="font-size:18px">sync</span>
                                    </button>
                                    <a href="{{ route('isp.onus.index', ['oltFilter' => $olt->id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-purple-50 hover:bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 dark:text-purple-400 transition-colors" title="Lihat ONU Terpasang (OLT ini)">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">router</span>
                                    </a>
                                    <a href="{{ route('isp.olts.show', $olt->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 transition-colors" title="Detail / PON Ports">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </a>
                                    <a href="{{ route('isp.olts.edit', $olt->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 dark:text-emerald-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $olt->id }})" wire:confirm="Yakin ingin menghapus OLT ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($olt->trashed())
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/50 text-red-800">Dihapus</span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $olt->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                                        {{ $olt->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    @if(!empty($olt->last_error) && $olt->status !== 'active')
                                        <div class="mt-1 flex items-center gap-1 text-[11px] text-amber-600 dark:text-amber-400 font-medium" title="{{ $olt->last_error }}">
                                            <span class="material-symbols-outlined notranslate" style="font-size:14px">error</span>
                                            <span>Kendala Sync</span>
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">
                                    <a href="{{ route('isp.olts.show', $olt->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $olt->name }}
                                    </a>
                                </div>
                                @if(!empty($olt->last_error) && $olt->status !== 'active')
                                    <div class="text-[11px] text-red-500 dark:text-red-400 font-normal mt-0.5 line-clamp-2 max-w-sm flex items-start gap-1" title="{{ $olt->last_error }}">
                                        <span class="material-symbols-outlined notranslate text-[13px] mt-0.5 flex-shrink-0">warning</span>
                                        <span class="line-clamp-2">{{ $olt->last_error }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $olt->ip_address ?: '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-900 dark:text-slate-100">{{ $olt->code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-slate-100">{{ $olt->pop?->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600 dark:text-slate-300">{{ $olt->vendor?->name ?? '-' }}</div>
                            </td>
                            {{-- ONU Online --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($onuOnline > 0)
                                    <a href="{{ route('isp.onus.index', ['oltFilter' => $olt->id, 'statusFilter' => 'online']) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-800/50 transition-colors" title="Lihat ONU Online">
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
                                    <a href="{{ route('isp.onus.index', ['oltFilter' => $olt->id, 'statusFilter' => 'offline']) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 transition-colors" title="Lihat ONU Offline">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                        {{ $onuOffline }}
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 dark:text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            {{-- Aksi --}}
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


