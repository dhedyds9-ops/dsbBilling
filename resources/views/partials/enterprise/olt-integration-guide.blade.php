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
            
            <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Kenapa butuh Telnet/SSH dan SNMP-</h4>
                <p class="text-sm text-primary-700 leading-relaxed">
                    Sistem dsBilling menggunakan protokol standar jaringan (Telnet/SSH dan SNMP) untuk berbicara langsung ke perangkat keras OLT. dsBilling <strong>TIDAK BISA</strong> menggunakan Web GUI OLT (HTTP Port 80 / 8080) karena web OLT didesain untuk manusia- bukan untuk dibaca oleh mesin.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 dark:text-slate-100 mb-3 border-b pb-2">1. Konfigurasi di Mikrotik (Port Forwarding / DST-NAT)</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                    Jika OLT Anda berada di jaringan lokal (misal IP <code>192.168.10.2</code>) di belakang router Mikrotik yang memiliki IP Publik- Anda perlu melakukan Port Forwarding.
                </p>
                
                <div class="space-y-4">
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                        <h5 class="font-semibold text-sm mb-2">A. Forwarding Telnet (Untuk Perintah CLI)</h5>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Forward port publik <strong>2301</strong> ke port <strong>23</strong> OLT.</p>
                        <pre class="bg-slate-900 p-3 rounded text-xs font-mono overflow-x-auto" style="color: #f1f5f9;">/ip firewall nat
add action=dst-nat chain=dstnat dst-address=[IP_PUBLIK] dst-port=2301 protocol=tcp \
    to-addresses=[IP_LOKAL_OLT] to-ports=23 comment="Forward Telnet OLT 1"</pre>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                        <h5 class="font-semibold text-sm mb-2">B. Forwarding SNMP (Untuk Monitoring & Traffic)</h5>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Forward port publik <strong>1611</strong> ke port <strong>161</strong> OLT. (Catatan: SNMP menggunakan <strong>UDP</strong>)</p>
                        <pre class="bg-slate-900 p-3 rounded text-xs font-mono overflow-x-auto" style="color: #f1f5f9;">/ip firewall nat
add action=dst-nat chain=dstnat dst-address=[IP_PUBLIK] dst-port=1611 protocol=udp \
    to-addresses=[IP_LOKAL_OLT] to-ports=161 comment="Forward SNMP OLT 1"</pre>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 dark:text-slate-100 mb-3 border-b pb-2">2. Konfigurasi di dsBilling (Tambah/Edit OLT)</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
                    Setelah Mikrotik disetting- isikan form dsBilling sebagai berikut:
                </p>
                <ul class="list-disc pl-5 text-sm text-slate-600 dark:text-slate-400 space-y-2">
                    <li><strong>IP Address:</strong> Isi dengan IP Publik Mikrotik Anda.</li>
                    <li><strong>SNMP Port:</strong> Isi dengan port publik SNMP (misal: <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded text-primary-600">1611</code>).</li>
                    <li><strong>CLI Port:</strong> Isi dengan port publik Telnet/SSH (misal: <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded text-primary-600">2301</code>).</li>
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
                            Biasanya fitur SNMP dan Telnet sudah aktif secara default- namun pastikan community <strong>public</strong> (read-only) dan <strong>private</strong> (read-write) tersedia.
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






