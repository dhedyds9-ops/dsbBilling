<!-- Speedtest Section -->
<section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-32" id="speedtest" x-data="speedtestEngine()" x-init="initEngine()">
    <!-- Section Header -->
    <div class="text-center mb-12">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 border border-cyan-200 text-cyan-700 dark:bg-primary/10 dark:border-primary/20 dark:text-primary mb-3 shadow-2xs">
            <span class="material-symbols-outlined text-[15px] animate-pulse">network_check</span>
            LIVE NETWORK SPEEDTEST
        </span>
        <h2 class="font-headline-lg text-headline-lg text-cyan-600 dark:text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">
            Uji Kecepatan Internet
        </h2>
        <p class="text-slate-600 dark:text-on-surface-variant font-body-md text-body-md max-w-2xl mx-auto">
            Ukur latensi (ping), jitter, serta kecepatan download &amp; upload koneksi internet Anda langsung ke server jaringan lokal kami.
        </p>
    </div>

    <!-- Main Speedtest Glass Container -->
    <div class="relative rounded-3xl bg-white/80 dark:bg-surface-container/50 backdrop-blur-xl border border-slate-200/80 dark:border-white/10 shadow-xl overflow-hidden p-6 sm:p-10 md:p-12 transition-all">
        <!-- Ambient Glowing Background Accents -->
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-cyan-500/10 dark:bg-primary/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-blue-500/10 dark:bg-secondary-container/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Status & Network Info Bar (Server, IP Publik, dan ISP) -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-3 gap-4 pb-6 mb-8 border-b border-slate-200/80 dark:border-white/10 text-xs sm:text-sm">
            <!-- 1. Server Pengujian -->
            <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50/70 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                <div class="w-11 h-11 rounded-xl bg-cyan-50 dark:bg-primary/10 border border-cyan-200/60 dark:border-primary/20 text-cyan-600 dark:text-primary flex items-center justify-center shrink-0 shadow-xs">
                    <span class="material-symbols-outlined text-2xl">dns</span>
                </div>
                <div class="min-w-0">
                    <div class="text-slate-400 dark:text-on-surface-variant text-[10px] font-bold uppercase tracking-wider">Server Pengujian</div>
                    <div class="font-bold text-slate-800 dark:text-on-surface flex items-center gap-1.5 truncate">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping shrink-0"></span>
                        <span class="truncate" x-text="serverName">dsBilling Edge Node</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-on-surface-variant/80 truncate" x-show="serverLocation" x-text="serverLocation"></div>
                </div>
            </div>

            <!-- 2. IP Publik Pengguna -->
            <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50/70 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-secondary-container/20 border border-blue-200/60 dark:border-secondary-container/20 text-blue-600 dark:text-secondary flex items-center justify-center shrink-0 shadow-xs">
                    <span class="material-symbols-outlined text-2xl">public</span>
                </div>
                <div class="min-w-0">
                    <div class="text-slate-400 dark:text-on-surface-variant text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span>IP Publik Anda</span>
                        <span class="text-[9px] px-1.5 py-0.2 bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 rounded font-bold">WAN</span>
                    </div>
                    <div class="font-mono font-bold text-slate-800 dark:text-on-surface text-sm sm:text-base truncate" x-text="clientIp">Mendeteksi...</div>
                    <div class="text-[11px] text-slate-500 dark:text-on-surface-variant/80 truncate" x-show="clientLocation" x-text="clientLocation"></div>
                </div>
            </div>

            <!-- 3. ISP / Operator Internet -->
            <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50/70 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-500/10 border border-purple-200/60 dark:border-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 shadow-xs">
                    <span class="material-symbols-outlined text-2xl">wifi_tethering</span>
                </div>
                <div class="min-w-0">
                    <div class="text-slate-400 dark:text-on-surface-variant text-[10px] font-bold uppercase tracking-wider">ISP / Operator Internet</div>
                    <div class="font-bold text-slate-800 dark:text-on-surface text-sm truncate" x-text="clientIsp">Mendeteksi...</div>
                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">verified</span>
                        Jaringan Terdeteksi
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Steps Bar -->
        <div class="relative z-10 mb-8 max-w-xl mx-auto">
            <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold">
                <!-- Step 1: Ping -->
                <div :class="{
                    'text-cyan-600 dark:text-primary border-cyan-500 dark:border-primary bg-cyan-50/50 dark:bg-primary/10': stage === 'ping',
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-500/10': stageIndex > 1,
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stageIndex < 1
                }" class="py-2 px-1 rounded-xl border transition-all flex flex-col sm:flex-row items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">timer</span>
                    <span>1. Ping</span>
                </div>

                <!-- Step 2: Download -->
                <div :class="{
                    'text-cyan-600 dark:text-primary border-cyan-500 dark:border-primary bg-cyan-50/50 dark:bg-primary/10': stage === 'download',
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-500/10': stageIndex > 2,
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stageIndex < 2
                }" class="py-2 px-1 rounded-xl border transition-all flex flex-col sm:flex-row items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">download</span>
                    <span>2. Download</span>
                </div>

                <!-- Step 3: Upload -->
                <div :class="{
                    'text-cyan-600 dark:text-primary border-cyan-500 dark:border-primary bg-cyan-50/50 dark:bg-primary/10': stage === 'upload',
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-500/10': stageIndex > 3,
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stageIndex < 3
                }" class="py-2 px-1 rounded-xl border transition-all flex flex-col sm:flex-row items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">upload</span>
                    <span>3. Upload</span>
                </div>

                <!-- Step 4: Selesai -->
                <div :class="{
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500 bg-emerald-50/60 dark:bg-emerald-500/15': stage === 'completed',
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stage !== 'completed'
                }" class="py-2 px-1 rounded-xl border transition-all flex flex-col sm:flex-row items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">task_alt</span>
                    <span>4. Selesai</span>
                </div>
            </div>

            <!-- Progress Track -->
            <div class="w-full bg-slate-200 dark:bg-surface-container-highest h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-cyan-500 to-emerald-400 transition-all duration-300"
                     :style="'width: ' + progress + '%'"></div>
            </div>
        </div>

        <!-- Central Speedometer Gauge & Interactive Area -->
        <div class="relative z-10 flex flex-col items-center justify-center my-6">
            <!-- Gauge Graphic (SVG) -->
            <div class="relative w-72 h-72 sm:w-80 sm:h-80 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 240 240">
                    <!-- Gauge Background Track (240 deg arc) -->
                    <circle cx="120" cy="120" r="96" fill="none"
                            stroke="currentColor"
                            class="text-slate-200 dark:text-white/10"
                            stroke-width="12"
                            stroke-dasharray="452.4"
                            stroke-dashoffset="113.1"
                            stroke-linecap="round"/>

                    <!-- Gauge Active Progress Arc -->
                    <circle cx="120" cy="120" r="96" fill="none"
                            stroke="url(#speedtestGradient)"
                            stroke-width="12"
                            stroke-dasharray="452.4"
                            :stroke-dashoffset="getStrokeDashoffset()"
                            stroke-linecap="round"
                            class="transition-all duration-150 ease-out"/>

                    <defs>
                        <linearGradient id="speedtestGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#06b6d4" />
                            <stop offset="60%" stop-color="#3b82f6" />
                            <stop offset="100%" stop-color="#10b981" />
                        </linearGradient>
                    </defs>
                </svg>

                <!-- Center Display / GO Button -->
                <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                    <!-- State: IDLE -> Big Clickable START Button -->
                    <template x-if="stage === 'idle'">
                        <button type="button" @click="startTest()"
                                class="group relative w-36 h-36 rounded-full bg-gradient-to-tr from-cyan-600 to-blue-600 dark:from-cyan-500 dark:to-blue-500 text-white font-black text-2xl flex flex-col items-center justify-center shadow-xl shadow-cyan-500/30 hover:scale-105 active:scale-95 transition-all cursor-pointer">
                            <!-- Radar pulse animation -->
                            <span class="absolute inset-0 rounded-full border-2 border-cyan-400 animate-ping opacity-60"></span>
                            <span class="material-symbols-outlined text-4xl group-hover:scale-110 transition-transform">speed</span>
                            <span class="tracking-widest mt-1 text-lg font-extrabold">MULAI</span>
                        </button>
                    </template>

                    <!-- State: RUNNING (Ping / Download / Upload) -->
                    <template x-if="stage === 'ping' || stage === 'download' || stage === 'upload'">
                        <div class="flex flex-col items-center justify-center">
                            <span class="text-xs uppercase font-extrabold tracking-widest px-3 py-1 rounded-full mb-1 transition-all"
                                  :class="{
                                      'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300': stage === 'ping',
                                      'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300': stage === 'download',
                                      'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300': stage === 'upload'
                                  }"
                                  x-text="stageLabel">
                            </span>
                            <div class="font-display-lg text-5xl sm:text-6xl font-black text-slate-900 dark:text-on-surface tracking-tight"
                                 x-text="currentDisplaySpeed.toFixed(1)">
                                0.0
                            </div>
                            <div class="text-xs sm:text-sm font-bold text-slate-400 dark:text-on-surface-variant uppercase tracking-wider mt-1"
                                 x-text="currentDisplayUnit">
                                Mbps
                            </div>
                        </div>
                    </template>

                    <!-- State: COMPLETED -->
                    <template x-if="stage === 'completed'">
                        <div class="flex flex-col items-center justify-center animate-fade-in">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-1">
                                <span class="material-symbols-outlined text-3xl">check_circle</span>
                            </div>
                            <div class="font-display-lg text-4xl sm:text-5xl font-black text-cyan-600 dark:text-primary tracking-tight"
                                 x-text="downloadSpeed.toFixed(1)">
                                0.0
                            </div>
                            <div class="text-xs font-bold text-slate-400 dark:text-on-surface-variant uppercase tracking-wider">
                                Download Mbps
                            </div>
                        </div>
                    </template>

                    <!-- State: ERROR -->
                    <template x-if="stage === 'error'">
                        <div class="flex flex-col items-center justify-center p-2">
                            <span class="material-symbols-outlined text-4xl text-rose-500 mb-1">error</span>
                            <div class="text-xs font-bold text-rose-500 text-center" x-text="errorMessage">Terjadi Kesalahan</div>
                            <button type="button" @click="startTest()" class="mt-2 text-xs font-bold px-3 py-1 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100">
                                Coba Lagi
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Status Text Message -->
            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-on-surface-variant text-center mt-2 max-w-md" x-text="statusMessage">
                Klik tombol MULAI untuk mengukur kecepatan koneksi internet Anda.
            </p>

            <!-- Cancel Button while running -->
            <button type="button" x-show="stage === 'ping' || stage === 'download' || stage === 'upload'" @click="cancelTest()"
                    class="mt-3 text-xs font-bold text-slate-500 hover:text-rose-500 transition-colors flex items-center gap-1 cursor-pointer">
                <span class="material-symbols-outlined text-sm">cancel</span>
                Batalkan Pengujian
            </button>
        </div>

        <!-- 4 Metric Output Cards -->
        <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <!-- Metric 1: Ping -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-surface/60 border border-slate-200/80 dark:border-white/5 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-slate-500 dark:text-on-surface-variant mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-base text-amber-500">timer</span> Ping
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                          :class="getPingBadgeClass()"
                          x-text="getPingBadgeText()">-</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-2xl sm:text-3xl font-black text-slate-800 dark:text-on-surface"
                          x-text="ping > 0 ? ping.toFixed(0) : '--'">--</span>
                    <span class="text-xs font-semibold text-slate-400 dark:text-on-surface-variant">ms</span>
                </div>
            </div>

            <!-- Metric 2: Jitter -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-surface/60 border border-slate-200/80 dark:border-white/5 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-slate-500 dark:text-on-surface-variant mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-base text-purple-500">network_ping</span> Jitter
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-slate-400 bg-slate-100 dark:bg-white/5"
                          x-text="jitter > 0 ? (jitter < 15 ? 'Stabil' : 'Fluktuatif') : '-'">-</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-2xl sm:text-3xl font-black text-slate-800 dark:text-on-surface"
                          x-text="jitter > 0 ? jitter.toFixed(1) : '--'">--</span>
                    <span class="text-xs font-semibold text-slate-400 dark:text-on-surface-variant">ms</span>
                </div>
            </div>

            <!-- Metric 3: Download -->
            <div class="p-4 sm:p-5 rounded-2xl bg-cyan-50/50 dark:bg-primary/5 border border-cyan-200/60 dark:border-primary/20 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-cyan-600 dark:text-primary mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">download</span> Download
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-cyan-100 dark:bg-primary/20 text-cyan-700 dark:text-primary">
                        Unduh
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-2xl sm:text-3xl font-black text-cyan-600 dark:text-primary"
                          x-text="downloadSpeed > 0 ? downloadSpeed.toFixed(1) : '--'">--</span>
                    <span class="text-xs font-semibold text-slate-400 dark:text-on-surface-variant">Mbps</span>
                </div>
            </div>

            <!-- Metric 4: Upload -->
            <div class="p-4 sm:p-5 rounded-2xl bg-blue-50/50 dark:bg-secondary-container/10 border border-blue-200/60 dark:border-secondary-container/20 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-blue-600 dark:text-blue-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">upload</span> Upload
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300">
                        Unggah
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-2xl sm:text-3xl font-black text-blue-600 dark:text-blue-400"
                          x-text="uploadSpeed > 0 ? uploadSpeed.toFixed(1) : '--'">--</span>
                    <span class="text-xs font-semibold text-slate-400 dark:text-on-surface-variant">Mbps</span>
                </div>
            </div>
        </div>

        <!-- Post-Test Recommendation Banner (Displayed on completion) -->
        <div x-show="stage === 'completed'" x-collapse x-cloak class="relative z-10 mt-8 pt-8 border-t border-slate-200/80 dark:border-white/10">
            <div class="p-6 rounded-2xl bg-gradient-to-r from-cyan-500/10 via-blue-500/10 to-emerald-500/10 border border-cyan-500/30 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-start gap-4 text-left">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white flex items-center justify-center shrink-0 shadow-md">
                        <span class="material-symbols-outlined text-2xl">insights</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-on-surface text-base sm:text-lg mb-1" x-text="getRecommendationTitle()">
                            Analisis Koneksi Anda
                        </h4>
                        <p class="text-slate-600 dark:text-on-surface-variant text-xs sm:text-sm max-w-xl">
                            <span x-text="getRecommendationText()"></span>
                            <span class="block mt-2 font-semibold text-cyan-600 dark:text-primary text-xs" x-show="clientIsp && clientIsp !== 'Mendeteksi...'">
                                Operator: <span class="font-bold" x-text="clientIsp"></span> &bull; IP: <span class="font-mono font-bold" x-text="clientIp"></span>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button" @click="startTest()"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-white dark:bg-white/10 hover:bg-slate-100 dark:hover:bg-white/20 border border-slate-200 dark:border-white/20 text-slate-700 dark:text-slate-200 transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="material-symbols-outlined text-base">refresh</span>
                        Uji Lagi
                    </button>
                    <a href="#paket"
                       class="btn-primary px-6 py-2.5 rounded-xl text-xs font-bold text-white transition-all flex items-center gap-1.5 shadow-md shadow-cyan-500/20">
                        <span>Lihat Paket Fiber</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Speedtest Engine Script -->
<script>
function speedtestEngine() {
    return {
        stage: 'idle', // 'idle' | 'ping' | 'download' | 'upload' | 'completed' | 'error'
        stageIndex: 0,
        progress: 0,
        ping: 0,
        jitter: 0,
        downloadSpeed: 0,
        uploadSpeed: 0,
        currentDisplaySpeed: 0,
        currentDisplayUnit: 'Mbps',
        stageLabel: 'Siap',
        statusMessage: 'Klik tombol MULAI untuk mengukur kecepatan koneksi internet Anda.',
        errorMessage: '',
        clientIp: 'Mendeteksi...',
        clientIsp: 'Mendeteksi...',
        clientLocation: '',
        isPrivateIp: false,
        serverName: 'dsBilling Edge Node',
        serverLocation: 'Indonesia',
        serverPartner: '',
        abortController: null,

        async initEngine() {
            // 1. Fetch info from local backend
            try {
                const resp = await fetch('{{ route('speedtest.info') }}', { cache: 'no-store' });
                const data = await resp.json();
                if (data.client_ip) this.clientIp = data.client_ip;
                if (data.client_isp) this.clientIsp = data.client_isp;
                if (data.client_city) this.clientLocation = data.client_city;
                if (data.server_name) this.serverName = data.server_name;
                if (data.server_location) this.serverLocation = data.server_location;
                if (data.server_partner) this.serverPartner = data.server_partner;
                this.isPrivateIp = !!data.is_private_ip;
            } catch (err) {
                console.warn('Backend speedtest info unavailable:', err);
            }

            // 2. If IP is private / local or ISP is not detected yet, query GeoIP directly from client browser
            if (this.isPrivateIp || !this.clientIsp || this.clientIsp === 'Mendeteksi...' || this.clientIp === '127.0.0.1') {
                try {
                    const geoResp = await fetch('https://ipwho.is/', { cache: 'no-store' });
                    const geo = await geoResp.json();
                    if (geo && geo.success) {
                        this.clientIp = geo.ip;
                        const isp = (geo.connection && (geo.connection.isp || geo.connection.org)) || geo.isp || geo.org;
                        if (isp) {
                            this.clientIsp = isp;
                        }
                        if (geo.city) {
                            this.clientLocation = geo.city + (geo.country_code ? ', ' + geo.country_code : '');
                        }
                    }
                } catch (e) {
                    // Fallback to ipify for public IP
                    try {
                        const ipifyResp = await fetch('https://api.ipify.org?format=json');
                        const ipify = await ipifyResp.json();
                        if (ipify && ipify.ip) {
                            this.clientIp = ipify.ip;
                        }
                    } catch (_) {}
                    if (!this.clientIsp || this.clientIsp === 'Mendeteksi...') {
                        this.clientIsp = 'Jaringan Lokal / ISP';
                    }
                }
            }
        },

        getStrokeDashoffset() {
            // Arc perimeter is 452.4. Max scale is 100 Mbps (or dynamically clamped up to 250)
            const maxScale = Math.max(100, this.downloadSpeed, this.uploadSpeed, this.currentDisplaySpeed);
            const val = Math.min(this.currentDisplaySpeed, maxScale);
            const ratio = val / maxScale;
            // 240 degree gauge: 0 value = 452.4 * (1 - 0.75) = offset 113.1
            // Full sweep is (452.4 * 0.75) = 339.3
            const sweep = 339.3;
            const offset = 452.4 - (ratio * sweep);
            return Math.max(113.1, Math.min(452.4, offset));
        },

        getPingBadgeText() {
            if (this.ping <= 0) return '-';
            if (this.ping < 30) return 'Sangat Cepat';
            if (this.ping < 70) return 'Bagus';
            if (this.ping < 120) return 'Sedang';
            return 'Tinggi';
        },

        getPingBadgeClass() {
            if (this.ping <= 0) return 'text-slate-400 bg-slate-100 dark:bg-white/5';
            if (this.ping < 30) return 'text-emerald-700 bg-emerald-100 dark:bg-emerald-500/20 dark:text-emerald-400';
            if (this.ping < 70) return 'text-cyan-700 bg-cyan-100 dark:bg-cyan-500/20 dark:text-cyan-400';
            if (this.ping < 120) return 'text-amber-700 bg-amber-100 dark:bg-amber-500/20 dark:text-amber-400';
            return 'text-rose-700 bg-rose-100 dark:bg-rose-500/20 dark:text-rose-400';
        },

        getRecommendationTitle() {
            if (this.downloadSpeed >= 50) return 'Koneksi Super Cepat & Optimal! 🚀';
            if (this.downloadSpeed >= 20) return 'Koneksi Cukup Baik & Stabil 👍';
            return 'Koneksi Anda Tergolong Rendah ⚠️';
        },

        getRecommendationText() {
            if (this.downloadSpeed >= 50) {
                return 'Koneksi Anda sangat mumpuni untuk 4K Ultra HD Streaming, gaming online bebas lag, conference video, dan unduhan file berukuran besar di banyak perangkat.';
            } else if (this.downloadSpeed >= 20) {
                return 'Koneksi Anda lancar untuk browsing, sosmed, dan streaming HD. Jika anggota keluarga Anda bertambah, pertimbangkan paket Fiber Optic 50 - 100 Mbps agar semakin ngebut.';
            } else {
                return 'Kecepatan internet Anda saat ini kurang optimal untuk pemakaian keluarga atau streaming berkualitas tinggi. Beralihlah ke paket Fiber Optic kami tanpa FUP mulai dari Rp 100 ribuan!';
            }
        },

        cancelTest() {
            if (this.abortController) {
                this.abortController.abort();
            }
            this.stage = 'idle';
            this.stageIndex = 0;
            this.progress = 0;
            this.currentDisplaySpeed = 0;
            this.statusMessage = 'Pengujian dibatalkan. Klik MULAI untuk mencoba lagi.';
        },

        async startTest() {
            this.stage = 'ping';
            this.stageIndex = 1;
            this.progress = 5;
            this.ping = 0;
            this.jitter = 0;
            this.downloadSpeed = 0;
            this.uploadSpeed = 0;
            this.currentDisplaySpeed = 0;
            this.currentDisplayUnit = 'ms';
            this.stageLabel = 'Menguji Ping';
            this.statusMessage = 'Mengukur latensi dan stabilitas jaringan (ping & jitter)...';
            this.errorMessage = '';
            this.abortController = new AbortController();

            try {
                // ================= PHASE 1: PING & JITTER =================
                await this.runPingTest(this.abortController.signal);
                if (this.abortController.signal.aborted) return;

                // ================= PHASE 2: DOWNLOAD =================
                this.stage = 'download';
                this.stageIndex = 2;
                this.progress = 30;
                this.currentDisplayUnit = 'Mbps';
                this.stageLabel = 'Download';
                this.statusMessage = 'Mengunduh stream data untuk mengukur kecepatan download...';
                await this.runDownloadTest(this.abortController.signal);
                if (this.abortController.signal.aborted) return;

                // ================= PHASE 3: UPLOAD =================
                this.stage = 'upload';
                this.stageIndex = 3;
                this.progress = 65;
                this.currentDisplaySpeed = 0;
                this.stageLabel = 'Upload';
                this.statusMessage = 'Mengunggah stream data untuk mengukur kecepatan upload...';
                await this.runUploadTest(this.abortController.signal);
                if (this.abortController.signal.aborted) return;

                // ================= PHASE 4: COMPLETED =================
                this.stage = 'completed';
                this.stageIndex = 4;
                this.progress = 100;
                this.currentDisplaySpeed = this.downloadSpeed;
                this.currentDisplayUnit = 'Mbps';
                this.stageLabel = 'Selesai';
                this.statusMessage = 'Pengujian selesai! Cek rincian performa jaringan Anda di bawah.';
            } catch (err) {
                if (err.name === 'AbortError') return;
                console.error('Speedtest Error:', err);
                this.stage = 'error';
                this.errorMessage = 'Gagal mengukur kecepatan. Periksa koneksi internet Anda.';
                this.statusMessage = 'Terjadi kesalahan saat pengujian jaringan.';
            }
        },

        async runPingTest(signal) {
            const pings = [];
            const count = 6;
            const pingUrl = '{{ route('speedtest.ping') }}';

            for (let i = 0; i < count; i++) {
                if (signal.aborted) return;
                const t0 = performance.now();
                const resp = await fetch(pingUrl + '?t=' + Date.now() + '_' + i, {
                    cache: 'no-store',
                    signal: signal
                });
                await resp.json();
                const t1 = performance.now();
                const rtt = Math.max(1, t1 - t0);
                
                // Skip the first ping as warmup
                if (i > 0) {
                    pings.push(rtt);
                    this.ping = pings.reduce((a, b) => a + b, 0) / pings.length;
                    this.currentDisplaySpeed = this.ping;
                }

                this.progress = 5 + Math.round((i / count) * 20);
                await new Promise(r => setTimeout(r, 60));
            }

            // Calculate Jitter (average difference between consecutive pings)
            if (pings.length > 1) {
                let jitterSum = 0;
                for (let i = 1; i < pings.length; i++) {
                    jitterSum += Math.abs(pings[i] - pings[i - 1]);
                }
                this.jitter = jitterSum / (pings.length - 1);
            } else {
                this.jitter = 1;
            }
        },

        async runDownloadTest(signal) {
            const downloadUrl = '{{ route('speedtest.download') }}?size=15000000&t=' + Date.now();
            const resp = await fetch(downloadUrl, { cache: 'no-store', signal: signal });
            
            if (!resp.body) {
                throw new Error('ReadableStream not supported');
            }

            const reader = resp.body.getReader();
            let totalBytes = 0;
            const startTime = performance.now();
            let lastUpdate = startTime;
            let lastBytes = 0;
            const samples = [];

            while (true) {
                if (signal.aborted) break;
                const { done, value } = await reader.read();
                if (done) break;

                totalBytes += value.length;
                const now = performance.now();

                // Sample every ~80ms
                if (now - lastUpdate >= 80) {
                    const elapsedSec = (now - lastUpdate) / 1000;
                    const deltaBits = (totalBytes - lastBytes) * 8;
                    const instantMbps = (deltaBits / elapsedSec) / 1000000;

                    if (instantMbps > 0) {
                        samples.push(instantMbps);
                        // Easing / smoothing filter
                        this.currentDisplaySpeed = (this.currentDisplaySpeed * 0.4) + (instantMbps * 0.6);
                    }

                    // Progress scale from 30% to 65%
                    const estProgress = 30 + Math.min(33, (totalBytes / 15000000) * 35);
                    this.progress = Math.round(estProgress);

                    lastUpdate = now;
                    lastBytes = totalBytes;
                }
            }

            const totalDurationSec = (performance.now() - startTime) / 1000;
            if (totalDurationSec > 0 && totalBytes > 0) {
                this.downloadSpeed = ((totalBytes * 8) / totalDurationSec) / 1000000;
                this.currentDisplaySpeed = this.downloadSpeed;
            }
        },

        async runUploadTest(signal) {
            return new Promise((resolve, reject) => {
                if (signal.aborted) return resolve();

                // Prepare 4MB pseudo-random binary payload
                const chunkSize = 4 * 1024 * 1024;
                const buffer = new Uint8Array(chunkSize);
                for (let i = 0; i < chunkSize; i += 1024) {
                    buffer[i] = Math.floor(Math.random() * 256);
                }
                const blob = new Blob([buffer], { type: 'application/octet-stream' });

                const xhr = new XMLHttpRequest();
                const uploadUrl = '{{ route('speedtest.upload') }}?t=' + Date.now();
                const startTime = performance.now();
                let lastTime = startTime;
                let lastLoaded = 0;

                xhr.open('POST', uploadUrl, true);
                xhr.setRequestHeader('Cache-Control', 'no-store');

                // Pass CSRF token if meta tag is present
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfMeta.getAttribute('content'));
                }

                signal.addEventListener('abort', () => {
                    xhr.abort();
                    resolve();
                });

                xhr.upload.onprogress = (event) => {
                    if (signal.aborted) return;
                    const now = performance.now();
                    const deltaMs = now - lastTime;

                    if (deltaMs >= 80 && event.loaded > lastLoaded) {
                        const deltaSec = deltaMs / 1000;
                        const deltaBits = (event.loaded - lastLoaded) * 8;
                        const instantMbps = (deltaBits / deltaSec) / 1000000;

                        if (instantMbps > 0) {
                            this.currentDisplaySpeed = (this.currentDisplaySpeed * 0.4) + (instantMbps * 0.6);
                        }

                        // Progress from 65% to 95%
                        const uploadProgress = 65 + Math.min(30, (event.loaded / event.total) * 30);
                        this.progress = Math.round(uploadProgress);

                        lastTime = now;
                        lastLoaded = event.loaded;
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const totalDurationSec = (performance.now() - startTime) / 1000;
                        if (totalDurationSec > 0 && lastLoaded > 0) {
                            this.uploadSpeed = ((lastLoaded * 8) / totalDurationSec) / 1000000;
                            this.currentDisplaySpeed = this.uploadSpeed;
                        }
                        resolve();
                    } else {
                        reject(new Error('Upload failed with status ' + xhr.status));
                    }
                };

                xhr.onerror = () => reject(new Error('Upload network error'));
                xhr.send(blob);
            });
        }
    };
}
</script>
