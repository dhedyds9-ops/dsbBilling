<div x-data="{ open: false }" 
     @open-modal.window="if ($event.detail.name === 'olt-guide-modal') open = true"
     x-show="open" 
     style="display: none;"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="w-full max-w-4xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Panduan Integrasi OLT ke dsBilling
            </h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Content -->
        <div class="px-6 py-5 overflow-y-auto space-y-6">
            @php
                $serverIp = request()->server('SERVER_ADDR') ?? (filter_var(request()->getHost(), FILTER_VALIDATE_IP) ? request()->getHost() : 'IP_SERVER_BILLING');
                if (!filter_var($serverIp, FILTER_VALIDATE_IP)) {
                    $serverIp = 'IP_SERVER_BILLING';
                }
            @endphp
            
            <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Kenapa butuh Telnet/SSH dan SNMP?</h4>
                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                    Sistem dsBilling menggunakan protokol standar jaringan (Telnet/SSH dan SNMP) untuk berbicara langsung ke perangkat keras OLT. dsBilling <strong>TIDAK MENGGUNAKAN</strong> Web GUI OLT (HTTP Port 80 / 8080) karena web OLT didesain untuk tampilan visual manusia, bukan untuk automasi mesin.
                </p>
            </div>

            {{-- SOLUSI BANYAK OLT 1 IP PUBLIK --}}
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 p-4 rounded-xl space-y-3">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 mt-0.5" style="font-size:22px">hub</span>
                    <div>
                        <h4 class="font-bold text-indigo-950 dark:text-indigo-200 text-sm">Solusi Banyak OLT dengan 1 IP Publik (Port Mapping)</h4>
                        <p class="text-xs text-indigo-800 dark:text-indigo-300 mt-1 leading-relaxed">
                            Jika Anda memiliki banyak OLT di belakang MikroTik dengan hanya 1 IP Publik, petakan port eksternal yang berbeda ke masing-masing IP lokal OLT. Port internal tetap 161 (SNMP) dan 23 (Telnet).
                        </p>
                    </div>
                </div>

                {{-- Tabel Port Mapping --}}
                <div class="overflow-x-auto bg-white dark:bg-slate-800 rounded-lg border border-indigo-100 dark:border-slate-700">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="p-2">Perangkat</th>
                                <th class="p-2">IP Lokal OLT</th>
                                <th class="p-2">Form dsBilling (IP Address)</th>
                                <th class="p-2">SNMP Port</th>
                                <th class="p-2">CLI Port</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 font-mono text-slate-600 dark:text-slate-300">
                            <tr>
                                <td class="p-2 font-sans font-bold text-indigo-600">OLT #1</td>
                                <td class="p-2">192.168.200.10</td>
                                <td class="p-2 font-sans">IP Publik MikroTik</td>
                                <td class="p-2 text-emerald-600 font-bold">1611</td>
                                <td class="p-2 text-emerald-600 font-bold">2301</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-sans font-bold text-indigo-600">OLT #2</td>
                                <td class="p-2">192.168.200.11</td>
                                <td class="p-2 font-sans">IP Publik MikroTik</td>
                                <td class="p-2 text-emerald-600 font-bold">1612</td>
                                <td class="p-2 text-emerald-600 font-bold">2302</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-sans font-bold text-indigo-600">OLT #3</td>
                                <td class="p-2">192.168.200.12</td>
                                <td class="p-2 font-sans">IP Publik MikroTik</td>
                                <td class="p-2 text-emerald-600 font-bold">1613</td>
                                <td class="p-2 text-emerald-600 font-bold">2303</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Skrip MikroTik Multi-OLT --}}
                <div x-data="{ copied: false }" class="bg-slate-900 rounded-lg p-3 text-xs text-green-400 font-mono overflow-x-auto relative group">
                    <button type="button" @click="window.copyToClipboard($refs.scriptGuideMulti.innerText.trim(), 'Skrip Multi-OLT berhasil disalin!'); copied = true; setTimeout(() => copied = false, 2500)" 
                        :class="copied ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white border-slate-600'"
                        class="absolute top-2 right-2 px-2.5 py-1 rounded-md transition-all flex items-center gap-1 border shadow-md">
                        <span class="material-symbols-outlined notranslate" style="font-size:14px" x-text="copied ? 'check' : 'content_copy'"></span>
                        <span x-text="copied ? 'Tersalin!' : 'Salin Skrip Multi-OLT'"></span>
                    </button>
<pre x-ref="scriptGuideMulti" class="pt-2"># =================================================================
# Multi-OLT Port Forwarding (1 IP Publik untuk Banyak OLT)
# Jalankan di Menu: MikroTik WinBox -> New Terminal
# =================================================================
/
:do { /ip firewall nat remove [find comment~"managed-by=dsbilling: Multi-OLT"] } on-error={ :nothing }
:do { /ip firewall filter remove [find comment~"managed-by=dsbilling: Multi-OLT"] } on-error={ :nothing }

# 1. Forwarding Port OLT #1 (IP: 192.168.200.10)
/ip firewall nat add chain=dstnat protocol=udp dst-port=1611 dst-address-type=local action=dst-nat to-addresses=192.168.200.10 to-ports=161 comment="managed-by=dsbilling: Multi-OLT 1 SNMP"
/ip firewall nat add chain=dstnat protocol=tcp dst-port=2301 dst-address-type=local action=dst-nat to-addresses=192.168.200.10 to-ports=23 comment="managed-by=dsbilling: Multi-OLT 1 Telnet"

# 2. Forwarding Port OLT #2 (IP: 192.168.200.11)
/ip firewall nat add chain=dstnat protocol=udp dst-port=1612 dst-address-type=local action=dst-nat to-addresses=192.168.200.11 to-ports=161 comment="managed-by=dsbilling: Multi-OLT 2 SNMP"
/ip firewall nat add chain=dstnat protocol=tcp dst-port=2302 dst-address-type=local action=dst-nat to-addresses=192.168.200.11 to-ports=23 comment="managed-by=dsbilling: Multi-OLT 2 Telnet"

# 3. Forwarding Port OLT #3 (IP: 192.168.200.12)
/ip firewall nat add chain=dstnat protocol=udp dst-port=1613 dst-address-type=local action=dst-nat to-addresses=192.168.200.12 to-ports=161 comment="managed-by=dsbilling: Multi-OLT 3 SNMP"
/ip firewall nat add chain=dstnat protocol=tcp dst-port=2303 dst-address-type=local action=dst-nat to-addresses=192.168.200.12 to-ports=23 comment="managed-by=dsbilling: Multi-OLT 3 Telnet"

# 4. Masquerade Balik (Wajib untuk seluruh subnet OLT)
/ip firewall nat add chain=srcnat dst-address=192.168.200.0/24 action=masquerade comment="managed-by=dsbilling: Multi-OLT Masquerade"

# 5. Keamanan Firewall (Hanya Server Billing {{ $serverIp }} yang Diizinkan)
/ip firewall filter add chain=forward src-address={{ $serverIp }} dst-address=192.168.200.0/24 action=accept comment="managed-by=dsbilling: Multi-OLT Allow Billing" place-before=0
/ip firewall filter add chain=forward dst-address=192.168.200.0/24 protocol=tcp dst-port=23 action=drop comment="managed-by=dsbilling: Multi-OLT Drop Public Telnet" place-before=1
/ip firewall filter add chain=forward dst-address=192.168.200.0/24 protocol=udp dst-port=161 action=drop comment="managed-by=dsbilling: Multi-OLT Drop Public SNMP" place-before=2
/
</pre>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 dark:text-slate-100 mb-3 border-b pb-2">Konfigurasi di dsBilling (Tambah/Edit OLT)</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                    Saat mendaftarkan masing-masing OLT di menu <strong>Jaringan -> OLT -> Tambah OLT</strong>:
                </p>
                <ul class="list-disc pl-5 text-sm text-slate-600 dark:text-slate-400 space-y-2">
                    <li><strong>IP Address:</strong> Isi dengan <strong>IP Publik MikroTik</strong> (misal: <code>157.20.128.237</code>).</li>
                    <li><strong>SNMP Port:</strong> Isi dengan port mapping SNMP OLT bersangkutan (misal: <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded text-primary-600 font-bold">1611</code> untuk OLT 1, <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded text-primary-600 font-bold">1612</code> untuk OLT 2).</li>
                    <li><strong>CLI Port:</strong> Isi dengan port mapping Telnet/SSH OLT bersangkutan (misal: <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded text-primary-600 font-bold">2301</code> untuk OLT 1, <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded text-primary-600 font-bold">2302</code> untuk OLT 2).</li>
                    <li><strong>CLI Mode:</strong> Pilih Telnet (atau SSH jika Anda mengaktifkan SSH di OLT).</li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 dark:text-slate-100 mb-3 border-b pb-2">3. Konfigurasi di dalam OLT (ZTE/HSGQ/Dll)</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                    Pastikan OLT Anda sudah mengizinkan koneksi SNMP dan Telnet dari luar.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-900/50">
                        <h5 class="font-semibold text-sm mb-2">ZTE C320/C300</h5>
                        <pre class="text-xs font-mono whitespace-pre-wrap" style="color: #475569;">ZXAN(config)# snmp-server community public ro
ZXAN(config)# snmp-server community private rw
ZXAN(config)# snmp-server version v2c</pre>
                    </div>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-900/50">
                        <h5 class="font-semibold text-sm mb-2">HSGQ/V-SOL</h5>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Biasanya fitur SNMP dan Telnet sudah aktif secara default, namun pastikan community <strong>public</strong> (read-only) dan <strong>private</strong> (read-write) tersedia.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end">
            <button @click="open = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors">
                Tutup Panduan
            </button>
        </div>
    </div>
</div>






