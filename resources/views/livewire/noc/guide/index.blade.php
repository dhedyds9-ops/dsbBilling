<div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-950 via-purple-950 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined text-[200px]">monitor_heart</span>
        </div>
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-purple-500/20 backdrop-blur-md rounded-full border border-purple-400/30 text-purple-200 text-xs font-semibold">
                <span class="material-symbols-outlined text-sm">hub</span>
                <span>Network Operation Center (NOC) Standard Operating Procedure</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                Buku Panduan & SOP Network Operation Center (NOC)
            </h1>
            <p class="text-xs sm:text-sm text-purple-200/80 max-w-2xl leading-relaxed">
                Panduan komprehensif untuk tim NOC dalam mengawasi stabilitas infrastruktur jaringan fiber optic, OLT GPON/EPON, Router Core Mikrotik, sistem GenieACS TR-069, serta mitigasi insiden jaringan massal.
            </p>
        </div>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-1.5 overflow-x-auto text-xs font-bold scrollbar-none">
        <button wire:click="setTab('olt')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'olt' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">server</span>
            <span>1. OLT & PON Management</span>
        </button>
        <button wire:click="setTab('acs')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'acs' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">router</span>
            <span>2. GenieACS (TR-069)</span>
        </button>
        <button wire:click="setTab('mikrotik')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'mikrotik' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">settings_ethernet</span>
            <span>3. Router Core & PPPoE</span>
        </button>
        <button wire:click="setTab('alarms')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'alarms' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">notifications_active</span>
            <span>4. Alarms & Telegram Alert</span>
        </button>
        <button wire:click="setTab('outage')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'outage' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">warning</span>
            <span>5. SOP Insiden Outage Massal</span>
        </button>
    </div>

    <!-- Content Sections -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">

        {{-- TAB 1: OLT --}}
        @if($activeTab === 'olt')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 font-bold rounded-lg text-[11px] uppercase">Optical Line Terminal</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Monitoring OLT, PON Port & Optical Power</h2>
                <p class="text-slate-500 mt-1">Mengawasi kapasitas port PON, distribusi splitter, dan deteksi dini redaman optik abnormal.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Pemeriksaan Berkala OLT:</h3>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-500 dark:text-slate-400 leading-relaxed">
                        <li>Cek status uplink SFP 10G/1G menuju core router.</li>
                        <li>Periksa port PON: Pastikan jumlah ONU per port tidak melebihi rasio splitter (maks 1:64 atau 1:128 sesuai spesifikasi OLT).</li>
                        <li>Pantau suhu modul SFP PON dan load CPU OLT.</li>
                    </ul>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Standar Redaman Optik (Optical Budget):</h3>
                    <div class="space-y-1 font-mono text-[11px]">
                        <p class="text-emerald-600 dark:text-emerald-400 font-bold">• -14 dBm s/d -22 dBm : Sangat Bagus (Optimal)</p>
                        <p class="text-blue-600 dark:text-blue-400 font-bold">• -22 dBm s/d -25 dBm : Wajar (Standar ISP)</p>
                        <p class="text-amber-600 dark:text-amber-400 font-bold">• -25 dBm s/d -27 dBm : Peringatan (Kritis)</p>
                        <p class="text-rose-600 dark:text-rose-400 font-bold">• Lebih buruk dari -27 dBm : LOS / Paket Drop Tinggi</p>
                    </div>
                </div>
            </div>

            <a href="{{ route('noc.olts.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">server</span>
                <span>Buka Data OLT</span>
            </a>
        </div>
        @endif

        {{-- TAB 2: GENIEACS --}}
        @if($activeTab === 'acs')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-cyan-50 dark:bg-cyan-950/50 text-cyan-700 dark:text-cyan-300 font-bold rounded-lg text-[11px] uppercase">TR-069 Auto Configuration</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Manajemen Perangkat Modem via GenieACS</h2>
                <p class="text-slate-500 mt-1">Konfigurasi otomatis parameter WAN PPPoE, WiFi, serta remote troubleshooting modem pelanggan.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Fungsi Utama GenieACS NOC:</h3>
                    <p>
                        1. <strong>Zero-Touch Provisioning (ZTP):</strong> Saat teknisi memasang modem baru di rumah pelanggan, GenieACS otomatis mem-push konfigurasi PPPoE, VLAN ID, dan pengaturan SSID WiFi tanpa perlu login ke GUI modem.<br>
                        2. <strong>Remote Reboot:</strong> Merestart modem pelanggan yang hang dari dashboard NOC.<br>
                        3. <strong>Firmware Upgrade Batch:</strong> Melakukan patch update firmware pada ratusan modem sekaligus secara terjadwal pada jam sepi (pukul 02:00 - 04:00 dini hari).
                    </p>
                </div>
            </div>

            <a href="{{ route('noc.acs.devices.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">router</span>
                <span>Buka Device GenieACS</span>
            </a>
        </div>
        @endif

        {{-- TAB 3: MIKROTIK --}}
        @if($activeTab === 'mikrotik')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-bold rounded-lg text-[11px] uppercase">Routing & Bandwidth</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Pengawasan Router Core Mikrotik & PPPoE</h2>
                <p class="text-slate-500 mt-1">Mengontrol traffic bandwidth, queue shaping, pool IP pelanggan, dan firewall isolir.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Tugas Harian NOC pada Router:</h3>
                    <p>• <strong>Monitor CPU Load & Memory:</strong> Pastikan utilisasi CPU tidak menyentuh 90% secara terus-menerus.<br>
                       • <strong>Monitor Link Transit / Upstream:</strong> Periksa apakah jalur ISP upstream utama mengalami saturasi (penuh) dan perlu dialihkan ke link backup.<br>
                       • <strong>Sinkronisasi PPPoE Active Secrets:</strong> Periksa kesesuaian antara user aktif di Mikrotik dengan database penagihan sistem.</p>
                </div>
            </div>

            <a href="{{ route('noc.routers.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">settings_ethernet</span>
                <span>Buka Router Management</span>
            </a>
        </div>
        @endif

        {{-- TAB 4: ALARMS --}}
        @if($activeTab === 'alarms')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-bold rounded-lg text-[11px] uppercase">Alerting System</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Sistem Notifikasi Alarm & Telegram Bot</h2>
                <p class="text-slate-500 mt-1">Mekanisme deteksi otomatis gangguan dan pengiriman peringatan dini ke grup Telegram tim teknis.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Kategori Peringatan Telegram:</h3>
                    <p>• <strong>Dying Gasp / Listrik Padam:</strong> Terkirim saat beberapa modem di satu wilayah padam serentak karena listrik PLN padam.<br>
                       • <strong>Loss of Signal (LOS Massal):</strong> Terkirim saat satu PON port kehilangan lebih dari 5 ONU sekaligus (indikasi kuat kabel optik putus).<br>
                       • <strong>High Latency / Ping RTO:</strong> Peringatan ketika ping ke gateway upstream mengalami lonjakan (jitter tinggi).</p>
                </div>
            </div>

            <a href="{{ route('noc.alarms.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">notifications_active</span>
                <span>Buka Alarm Log</span>
            </a>
        </div>
        @endif

        {{-- TAB 5: SOP OUTAGE --}}
        @if($activeTab === 'outage')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 font-bold rounded-lg text-[11px] uppercase">Incident Response</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">SOP Tanggap Darurat Kabel Fiber Putus (Fiber Cut)</h2>
                <p class="text-slate-500 mt-1">Protokol penanganan insiden ketika terjadi pemadaman massal akibat kabel distribusi/backbone putus.</p>
            </div>

            <div class="p-4 rounded-2xl bg-rose-50/50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/40 space-y-3 text-slate-700 dark:text-slate-300 leading-relaxed">
                <h4 class="font-bold text-rose-800 dark:text-rose-300 text-sm">Protokol 4 Langkah NOC:</h4>
                <ol class="list-decimal list-inside space-y-2 text-xs">
                    <li><strong>Identifikasi Titik Gangguan:</strong> Buka menu <em>Topology & ODP Map</em>. Cari ODP mana saja yang berstatus merah untuk melokalisir segmen kabel optik yang bermasalah.</li>
                    <li><strong>Kirim Peringatan ke Customer Service (CS):</strong> Beri tahu CS bahwa area X sedang mengalami gangguan backbone agar CS siap menjawab pertanyaan pelanggan dengan seragam.</li>
                    <li><strong>Disposisi Tim Lapangan Splicing (Splicer):</strong> Kirim tim teknisi fiber dengan membawa OTDR (*Optical Time Domain Reflectometer*) dan mesin fusion splicer ke estimasi titik putus.</li>
                    <li><strong>Verifikasi & Penutupan Insiden:</strong> Setelah kabel disambung, pastikan seluruh ONU kembali online dan redaman telah sesuai standar sebelum menutup tiket insiden.</li>
                </ol>
            </div>
        </div>
        @endif

    </div>
</div>
