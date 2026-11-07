<!-- Speedtest Section (Compact & Collapsible) -->
<section class="px-margin-mobile md:px-margin-desktop max-w-4xl mx-auto mb-8 transition-all duration-300" 
         id="speedtest" 
         x-data="speedtestEngine()" 
         x-init="initEngine()">

    <!-- 1. COMPACT TEASER BANNER (Default State: Collapsed / Hidden) -->
    <div x-show="!isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="rounded-2xl bg-white/90 dark:bg-surface-container/60 backdrop-blur-xl border border-slate-200/90 dark:border-white/10 p-4 sm:p-5 shadow-lg shadow-cyan-500/5 hover:border-cyan-500/40 transition-all flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center gap-3.5 w-full sm:w-auto">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-cyan-500/20">
                <span class="material-symbols-outlined text-2xl animate-pulse">speed</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-slate-900 dark:text-on-surface text-sm sm:text-base tracking-tight">
                        Uji Kecepatan Internet
                    </h3>
                    <span class="inline-flex items-center text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-700 dark:bg-primary/20 dark:text-primary">
                        Speedtest
                    </span>
                </div>
                <div class="text-[11px] sm:text-xs text-slate-500 dark:text-on-surface-variant flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                    <span class="flex items-center gap-1 font-mono text-cyan-600 dark:text-primary">
                        <span class="material-symbols-outlined text-[13px]">public</span>
                        <span x-text="clientIp">Mendeteksi IP...</span>
                    </span>
                    <span class="text-slate-300 dark:text-white/20">&bull;</span>
                    <span class="flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300 truncate max-w-[200px] sm:max-w-xs">
                        <span class="material-symbols-outlined text-[13px] text-purple-500 shrink-0">wifi_tethering</span>
                        <span class="truncate font-semibold" x-text="clientIsp">Mendeteksi ISP...</span>
                        <span class="text-[9px] px-1.5 py-0.2 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 rounded font-mono font-bold shrink-0" 
                              x-show="clientAsn" 
                              x-text="clientAsn"></span>
                    </span>
                </div>
            </div>
        </div>

        <button type="button" 
                @click="openSpeedtest()" 
                class="w-full sm:w-auto btn-primary px-5 py-2.5 rounded-xl text-xs font-bold text-white shrink-0 flex items-center justify-center gap-2 shadow-md shadow-cyan-500/20 hover:scale-[1.02] active:scale-95 transition-all cursor-pointer">
            <span class="material-symbols-outlined text-base">play_arrow</span>
            <span>Buka Speedtest</span>
        </button>
    </div>

    <!-- 2. FULL SPEEDTEST CARD (Shown when expanded) -->
    <div x-show="isOpen" 
         x-collapse 
         x-cloak
         class="relative rounded-2xl sm:rounded-3xl bg-white/95 dark:bg-surface-container/70 backdrop-blur-xl border border-cyan-500/30 dark:border-white/10 shadow-2xl overflow-hidden p-4 sm:p-6 md:p-8 transition-all">
        
        <!-- Ambient Glowing Background Accents -->
        <div class="absolute -top-20 -left-20 w-48 h-48 bg-cyan-500/10 dark:bg-primary/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -right-20 w-48 h-48 bg-blue-500/10 dark:bg-secondary-container/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header: Title & Close Button -->
        <div class="relative z-10 flex items-center justify-between pb-4 mb-4 border-b border-slate-200/80 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-primary/10 text-cyan-600 dark:text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg">speed</span>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-on-surface text-sm sm:text-base flex items-center gap-2">
                        Network Speedtest
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full">
                            Online
                        </span>
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-on-surface-variant">Uji latensi (ping), jitter, download &amp; upload koneksi internet.</p>
                </div>
            </div>

            <!-- Minimize / Hide Button -->
            <button type="button" 
                    @click="closeSpeedtest()" 
                    class="flex items-center gap-1 text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:text-on-surface-variant dark:hover:text-white px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 border border-slate-200 dark:border-white/10 transition-colors cursor-pointer"
                    title="Sembunyikan panel speedtest">
                <span class="material-symbols-outlined text-sm">expand_less</span>
                <span class="hidden sm:inline">Sembunyikan</span>
            </button>
        </div>

        <!-- Compact Status Bar: Server, IP Publik, ISP Pemilik IP -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-6 text-xs">
            <!-- Server -->
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50/80 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                <span class="material-symbols-outlined text-cyan-600 dark:text-primary text-lg shrink-0">dns</span>
                <div class="min-w-0">
                    <div class="text-slate-400 dark:text-on-surface-variant text-[9px] font-bold uppercase tracking-wider">Server Pengujian</div>
                    <div class="font-bold text-slate-800 dark:text-on-surface truncate flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="truncate" x-text="serverName">dsBilling Edge</span>
                    </div>
                    <div class="text-[10px] text-slate-400 dark:text-on-surface-variant/70 truncate" x-show="serverLocation" x-text="serverLocation"></div>
                </div>
            </div>

            <!-- IP Publik -->
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50/80 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                <span class="material-symbols-outlined text-blue-600 dark:text-secondary text-lg shrink-0">public</span>
                <div class="min-w-0">
                    <div class="text-slate-400 dark:text-on-surface-variant text-[9px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span>IP Publik Anda</span>
                        <span class="text-[9px] px-1 py-0.2 bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 rounded font-bold">WAN</span>
                    </div>
                    <div class="font-mono font-bold text-slate-800 dark:text-on-surface text-xs truncate" x-text="clientIp">Mendeteksi...</div>
                    <div class="text-[10px] text-slate-400 dark:text-on-surface-variant/70 truncate" x-show="clientLocation" x-text="clientLocation"></div>
                </div>
            </div>

            <!-- ISP / Pemilik IP -->
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50/80 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 text-lg shrink-0">wifi_tethering</span>
                <div class="min-w-0">
                    <div class="text-slate-400 dark:text-on-surface-variant text-[9px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span>ISP / Pemilik IP</span>
                        <span class="text-[9px] px-1 py-0.2 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 rounded font-mono font-bold" 
                              x-show="clientAsn" 
                              x-text="clientAsn"></span>
                    </div>
                    <div class="font-bold text-slate-800 dark:text-on-surface text-xs truncate" x-text="clientIsp" :title="clientIsp">Mendeteksi...</div>
                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold truncate flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[12px]">verified</span>
                        Terverifikasi
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Steps Indicator -->
        <div class="relative z-10 mb-6 max-w-md mx-auto">
            <div class="grid grid-cols-4 gap-1.5 text-center text-[11px] font-bold">
                <div :class="{
                    'text-cyan-600 dark:text-primary border-cyan-500 bg-cyan-50/60 dark:bg-primary/10': stage === 'ping',
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-500/10': stageIndex > 1,
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stageIndex < 1
                }" class="py-1.5 px-1 rounded-lg border transition-all">
                    1. Ping
                </div>

                <div :class="{
                    'text-cyan-600 dark:text-primary border-cyan-500 bg-cyan-50/60 dark:bg-primary/10': stage === 'download',
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-500/10': stageIndex > 2,
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stageIndex < 2
                }" class="py-1.5 px-1 rounded-lg border transition-all">
                    2. Download
                </div>

                <div :class="{
                    'text-cyan-600 dark:text-primary border-cyan-500 bg-cyan-50/60 dark:bg-primary/10': stage === 'upload',
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-500/10': stageIndex > 3,
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stageIndex < 3
                }" class="py-1.5 px-1 rounded-lg border transition-all">
                    3. Upload
                </div>

                <div :class="{
                    'text-emerald-600 dark:text-emerald-400 border-emerald-500 bg-emerald-50/60 dark:bg-emerald-500/15': stage === 'completed',
                    'text-slate-400 dark:text-on-surface-variant/60 border-transparent': stage !== 'completed'
                }" class="py-1.5 px-1 rounded-lg border transition-all">
                    4. Selesai
                </div>
            </div>

            <!-- Progress Track -->
            <div class="w-full bg-slate-200 dark:bg-surface-container-highest h-1 rounded-full mt-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-cyan-500 to-emerald-400 transition-all duration-300"
                     :style="'width: ' + progress + '%'"></div>
            </div>
        </div>

        <!-- Central Speedometer Gauge (Compact w-52 h-52) -->
        <div class="relative z-10 flex flex-col items-center justify-center my-3">
            <div class="relative w-48 h-48 sm:w-56 sm:h-56 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 240 240">
                    <circle cx="120" cy="120" r="96" fill="none"
                            stroke="currentColor"
                            class="text-slate-200 dark:text-white/10"
                            stroke-width="10"
                            stroke-dasharray="452.4"
                            stroke-dashoffset="113.1"
                            stroke-linecap="round"/>

                    <circle cx="120" cy="120" r="96" fill="none"
                            stroke="url(#speedtestCompactGrad)"
                            stroke-width="10"
                            stroke-dasharray="452.4"
                            :stroke-dashoffset="getStrokeDashoffset()"
                            stroke-linecap="round"
                            class="transition-all duration-150 ease-out"/>

                    <defs>
                        <linearGradient id="speedtestCompactGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#06b6d4" />
                            <stop offset="60%" stop-color="#3b82f6" />
                            <stop offset="100%" stop-color="#10b981" />
                        </linearGradient>
                    </defs>
                </svg>

                <!-- Center Display / START Button -->
                <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                    <!-- State: IDLE -> Start Button -->
                    <template x-if="stage === 'idle'">
                        <button type="button" @click="startTest()"
                                class="group relative w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-gradient-to-tr from-cyan-600 to-blue-600 dark:from-cyan-500 dark:to-blue-500 text-white font-black flex flex-col items-center justify-center shadow-lg shadow-cyan-500/30 hover:scale-105 active:scale-95 transition-all cursor-pointer">
                            <span class="absolute inset-0 rounded-full border border-cyan-400 animate-ping opacity-50"></span>
                            <span class="material-symbols-outlined text-3xl group-hover:scale-110 transition-transform">speed</span>
                            <span class="tracking-widest mt-0.5 text-base font-extrabold">MULAI</span>
                        </button>
                    </template>

                    <!-- State: RUNNING -->
                    <template x-if="stage === 'ping' || stage === 'download' || stage === 'upload'">
                        <div class="flex flex-col items-center justify-center">
                            <span class="text-[10px] uppercase font-extrabold tracking-widest px-2.5 py-0.5 rounded-full mb-1 transition-all"
                                  :class="{
                                      'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300': stage === 'ping',
                                      'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300': stage === 'download',
                                      'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300': stage === 'upload'
                                  }"
                                  x-text="stageLabel">
                            </span>
                            <div class="font-display-lg text-4xl sm:text-5xl font-black text-slate-900 dark:text-on-surface tracking-tight"
                                 x-text="currentDisplaySpeed.toFixed(1)">
                                0.0
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 dark:text-on-surface-variant uppercase tracking-wider"
                                 x-text="currentDisplayUnit">
                                Mbps
                            </div>
                        </div>
                    </template>

                    <!-- State: COMPLETED -->
                    <template x-if="stage === 'completed'">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-9 h-9 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-0.5">
                                <span class="material-symbols-outlined text-2xl">check_circle</span>
                            </div>
                            <div class="font-display-lg text-3xl sm:text-4xl font-black text-cyan-600 dark:text-primary tracking-tight"
                                 x-text="downloadSpeed.toFixed(1)">
                                0.0
                            </div>
                            <div class="text-[10px] font-bold text-slate-400 dark:text-on-surface-variant uppercase tracking-wider">
                                Download Mbps
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Status Text Message -->
            <p class="text-[11px] sm:text-xs font-medium text-slate-600 dark:text-on-surface-variant text-center mt-1 max-w-sm" x-text="statusMessage">
                Klik tombol MULAI untuk mengukur kecepatan koneksi internet Anda.
            </p>

            <!-- Cancel Button while running -->
            <button type="button" x-show="stage === 'ping' || stage === 'download' || stage === 'upload'" @click="cancelTest()"
                    class="mt-2 text-[11px] font-bold text-slate-500 hover:text-rose-500 transition-colors flex items-center gap-1 cursor-pointer">
                <span class="material-symbols-outlined text-sm">cancel</span>
                Batalkan Pengujian
            </button>
        </div>

        <!-- 4 Metric Cards (Compact) -->
        <div class="relative z-10 grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4">
            <!-- Ping -->
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-surface/60 border border-slate-200/80 dark:border-white/5 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-slate-500 dark:text-on-surface-variant mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-amber-500">timer</span> Ping
                    </span>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded"
                          :class="getPingBadgeClass()"
                          x-text="getPingBadgeText()">-</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-xl sm:text-2xl font-black text-slate-800 dark:text-on-surface"
                          x-text="ping > 0 ? ping.toFixed(0) : '--'">--</span>
                    <span class="text-[10px] font-semibold text-slate-400">ms</span>
                </div>
            </div>

            <!-- Jitter -->
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-surface/60 border border-slate-200/80 dark:border-white/5 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-slate-500 dark:text-on-surface-variant mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-purple-500">network_ping</span> Jitter
                    </span>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded text-slate-400 bg-slate-100 dark:bg-white/5"
                          x-text="jitter > 0 ? (jitter < 15 ? 'Stabil' : 'Variatif') : '-'">-</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-xl sm:text-2xl font-black text-slate-800 dark:text-on-surface"
                          x-text="jitter > 0 ? jitter.toFixed(1) : '--'">--</span>
                    <span class="text-[10px] font-semibold text-slate-400">ms</span>
                </div>
            </div>

            <!-- Download -->
            <div class="p-3 rounded-xl bg-cyan-50/50 dark:bg-primary/5 border border-cyan-200/60 dark:border-primary/20 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-cyan-600 dark:text-primary mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">download</span> Download
                    </span>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-cyan-100 dark:bg-primary/20 text-cyan-700 dark:text-primary">
                        Unduh
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-xl sm:text-2xl font-black text-cyan-600 dark:text-primary"
                          x-text="downloadSpeed > 0 ? downloadSpeed.toFixed(1) : '--'">--</span>
                    <span class="text-[10px] font-semibold text-slate-400">Mbps</span>
                </div>
            </div>

            <!-- Upload -->
            <div class="p-3 rounded-xl bg-blue-50/50 dark:bg-secondary-container/10 border border-blue-200/60 dark:border-secondary-container/20 flex flex-col justify-between shadow-2xs">
                <div class="flex items-center justify-between text-blue-600 dark:text-blue-400 mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">upload</span> Upload
                    </span>
                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300">
                        Unggah
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display-lg text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400"
                          x-text="uploadSpeed > 0 ? uploadSpeed.toFixed(1) : '--'">--</span>
                    <span class="text-[10px] font-semibold text-slate-400">Mbps</span>
                </div>
            </div>
        </div>

        <!-- Post-Test Result & Recommendation Banner -->
        <div x-show="stage === 'completed'" x-collapse x-cloak class="relative z-10 mt-5 pt-4 border-t border-slate-200/80 dark:border-white/10">
            <div class="p-4 rounded-xl bg-gradient-to-r from-cyan-500/10 via-blue-500/10 to-emerald-500/10 border border-cyan-500/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-left">
                    <h4 class="font-bold text-slate-900 dark:text-on-surface text-sm mb-0.5" x-text="getRecommendationTitle()">
                        Hasil Pengujian Selesai
                    </h4>
                    <p class="text-slate-600 dark:text-on-surface-variant text-xs max-w-lg" x-text="getRecommendationText()"></p>
                    <div class="mt-1.5 text-[11px] font-semibold text-cyan-600 dark:text-primary flex flex-wrap items-center gap-1.5" x-show="clientIsp && clientIsp !== 'Mendeteksi...'">
                        <span>ISP Pemilik IP: <strong class="text-slate-900 dark:text-white" x-text="clientIsp"></strong> <span x-show="clientAsn" class="font-mono text-[10px] px-1 py-0.2 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 rounded font-bold" x-text="clientAsn"></span></span>
                        <span>&bull;</span>
                        <span>IP Publik: <strong class="font-mono" x-text="clientIp"></strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="startTest()"
                            class="px-3.5 py-2 rounded-lg text-xs font-bold bg-white dark:bg-white/10 hover:bg-slate-100 dark:hover:bg-white/20 border border-slate-200 dark:border-white/20 text-slate-700 dark:text-slate-200 transition-all flex items-center gap-1 shadow-2xs cursor-pointer">
                        <span class="material-symbols-outlined text-sm">refresh</span>
                        Uji Lagi
                    </button>
                    <a href="#paket"
                       class="btn-primary px-4 py-2 rounded-lg text-xs font-bold text-white transition-all flex items-center gap-1 shadow-md shadow-cyan-500/20">
                        <span>Pilih Paket Fiber</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
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
        isOpen: false,
        stage: 'idle',
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
        clientAsn: '',
        clientLocation: '',
        isPrivateIp: false,
        serverName: 'dsBilling Edge Node',
        serverLocation: 'Indonesia',
        serverPartner: '',
        abortController: null,

        isGenericRegistry(str) {
            if (!str || typeof str !== 'string') return true;
            const s = str.trim().toLowerCase();
            return s.includes('network information center') ||
                   s.includes('idnic') ||
                   s.includes('apnic') ||
                   s.includes('iana') ||
                   s.includes('apjii') ||
                   s.includes('registry') ||
                   s.startsWith('komplek') ||
                   s.startsWith('jl.') ||
                   s.startsWith('jalan') ||
                   s.startsWith('rt ') ||
                   s.startsWith('rw ');
        },

        resolveAccurateIspFromGeo(geo) {
            if (!geo) return { isp: '', asn: '' };
            const conn = geo.connection || {};
            let isp = '';
            let asn = '';

            if (conn.asn) {
                asn = 'AS' + conn.asn;
            } else if (geo.asn) {
                asn = String(geo.asn).startsWith('AS') ? geo.asn : ('AS' + geo.asn);
            }

            // Prioritize real company/operating ISP over generic national registry
            if (!this.isGenericRegistry(conn.org)) {
                isp = conn.org;
            } else if (!this.isGenericRegistry(conn.isp)) {
                isp = conn.isp;
            } else if (!this.isGenericRegistry(geo.org)) {
                isp = geo.org;
            } else if (!this.isGenericRegistry(geo.isp)) {
                isp = geo.isp;
            } else if (conn.domain && !this.isGenericRegistry(conn.domain)) {
                isp = conn.domain;
            } else {
                isp = conn.org || conn.isp || geo.org || geo.isp || 'ISP Terdeteksi';
            }

            isp = isp.replace(/^AS\d+\s+/i, '').trim();
            return { isp, asn };
        },

        openSpeedtest() {
            this.isOpen = true;
            this.$nextTick(() => {
                document.getElementById('speedtest')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        },

        closeSpeedtest() {
            if (this.stage === 'ping' || this.stage === 'download' || this.stage === 'upload') {
                this.cancelTest();
            }
            this.isOpen = false;
        },

        async initEngine() {
            window.addEventListener('open-speedtest', () => {
                this.openSpeedtest();
            });

            if (window.location.hash === '#speedtest') {
                this.isOpen = true;
            }
            window.addEventListener('hashchange', () => {
                if (window.location.hash === '#speedtest') {
                    this.isOpen = true;
                }
            });

            // 1. Fetch info from backend API
            try {
                const resp = await fetch('{{ route('speedtest.info') }}', { cache: 'no-store' });
                const data = await resp.json();
                if (data.client_ip) this.clientIp = data.client_ip;
                if (data.client_isp && !this.isGenericRegistry(data.client_isp)) {
                    this.clientIsp = data.client_isp;
                }
                if (data.client_asn) this.clientAsn = data.client_asn;
                if (data.client_city) this.clientLocation = data.client_city;
                if (data.server_name) this.serverName = data.server_name;
                if (data.server_location) this.serverLocation = data.server_location;
                if (data.server_partner) this.serverPartner = data.server_partner;
                this.isPrivateIp = !!data.is_private_ip;
            } catch (err) {
                console.warn('Backend speedtest info unavailable:', err);
            }

            // 2. Client browser accurate GeoIP lookup (resolves real ISP even if behind NAT or local dev)
            const needsAccurateLookup = this.isPrivateIp || 
                                       !this.clientIsp || 
                                       this.clientIsp === 'Mendeteksi...' || 
                                       this.isGenericRegistry(this.clientIsp);

            if (needsAccurateLookup) {
                try {
                    const queryIp = (!this.isPrivateIp && this.clientIp && this.clientIp !== '127.0.0.1') ? this.clientIp : '';
                    const geoResp = await fetch('https://ipwho.is/' + queryIp, { cache: 'no-store' });
                    const geo = await geoResp.json();
                    if (geo && geo.success) {
                        if (geo.ip) this.clientIp = geo.ip;
                        const resolved = this.resolveAccurateIspFromGeo(geo);
                        if (resolved.isp) this.clientIsp = resolved.isp;
                        if (resolved.asn) this.clientAsn = resolved.asn;
                        if (geo.city) {
                            this.clientLocation = geo.city + (geo.country_code ? ', ' + geo.country_code : '');
                        }
                    }
                } catch (e) {
                    try {
                        const ipifyResp = await fetch('https://api.ipify.org?format=json');
                        const ipify = await ipifyResp.json();
                        if (ipify && ipify.ip) this.clientIp = ipify.ip;
                    } catch (_) {}
                    if (!this.clientIsp || this.clientIsp === 'Mendeteksi...') {
                        this.clientIsp = 'Jaringan ISP';
                    }
                }
            }
        },

        getStrokeDashoffset() {
            const maxScale = Math.max(100, this.downloadSpeed, this.uploadSpeed, this.currentDisplaySpeed);
            const val = Math.min(this.currentDisplaySpeed, maxScale);
            const ratio = val / maxScale;
            const sweep = 339.3;
            const offset = 452.4 - (ratio * sweep);
            return Math.max(113.1, Math.min(452.4, offset));
        },

        getPingBadgeText() {
            if (this.ping <= 0) return '-';
            if (this.ping < 30) return 'Bagus';
            if (this.ping < 70) return 'Normal';
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
                return 'Koneksi sangat mumpuni untuk 4K Ultra HD Streaming, gaming online bebas lag, dan banyak perangkat sekaligus.';
            } else if (this.downloadSpeed >= 20) {
                return 'Koneksi lancar untuk browsing & streaming HD. Untuk multi-user & gaming, pertimbangkan upgrade paket Fiber.';
            } else {
                return 'Kecepatan internet kurang optimal untuk pemakaian keluarga. Beralih ke paket Fiber Optic kami tanpa FUP mulai dari Rp 100 ribuan!';
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
                
                if (this.downloadSpeed > 0) {
                    this.stage = 'completed';
                    this.stageIndex = 4;
                    this.progress = 100;
                    this.currentDisplaySpeed = this.downloadSpeed;
                    this.currentDisplayUnit = 'Mbps';
                    this.stageLabel = 'Selesai';
                    this.statusMessage = 'Pengujian download & ping selesai.';
                } else {
                    this.stage = 'error';
                    this.errorMessage = 'Gagal mengukur kecepatan. Periksa koneksi internet Anda.';
                    this.statusMessage = 'Terjadi kesalahan saat pengujian jaringan.';
                }
            }
        },

        async runPingTest(signal) {
            const pings = [];
            const count = 5;
            const pingUrl = '{{ route('speedtest.ping') }}';

            for (let i = 0; i < count; i++) {
                if (signal.aborted) return;
                const t0 = performance.now();
                try {
                    const resp = await fetch(pingUrl + '?t=' + Date.now() + '_' + i, {
                        cache: 'no-store',
                        signal: signal
                    });
                    await resp.json();
                    const t1 = performance.now();
                    const rtt = Math.max(1, t1 - t0);
                    
                    if (i > 0) {
                        pings.push(rtt);
                        this.ping = pings.reduce((a, b) => a + b, 0) / pings.length;
                        this.currentDisplaySpeed = this.ping;
                    }
                } catch (e) {
                    if (signal.aborted) return;
                }

                this.progress = 5 + Math.round((i / count) * 25);
                await new Promise(r => setTimeout(r, 50));
            }

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
            const downloadUrl = '{{ route('speedtest.download') }}?size=10000000&t=' + Date.now();
            const resp = await fetch(downloadUrl, { cache: 'no-store', signal: signal });
            
            if (!resp.body) {
                throw new Error('ReadableStream not supported');
            }

            const reader = resp.body.getReader();
            let totalBytes = 0;
            const startTime = performance.now();
            let lastUpdate = startTime;
            let lastBytes = 0;

            while (true) {
                if (signal.aborted) break;
                const { done, value } = await reader.read();
                if (done) break;

                totalBytes += value.length;
                const now = performance.now();

                if (now - lastUpdate >= 80) {
                    const elapsedSec = (now - lastUpdate) / 1000;
                    const deltaBits = (totalBytes - lastBytes) * 8;
                    const instantMbps = (deltaBits / elapsedSec) / 1000000;

                    if (instantMbps > 0) {
                        this.currentDisplaySpeed = (this.currentDisplaySpeed * 0.4) + (instantMbps * 0.6);
                    }

                    const estProgress = 30 + Math.min(33, (totalBytes / 10000000) * 35);
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
            // Safe chunk size (512 KB) to prevent Nginx 413 client_max_body_size error
            const chunkSize = 512 * 1024;
            const buffer = new Uint8Array(chunkSize);
            for (let i = 0; i < chunkSize; i += 2048) {
                buffer[i] = Math.floor(Math.random() * 256);
            }
            const blob = new Blob([buffer], { type: 'application/octet-stream' });
            const uploadUrl = '{{ route('speedtest.upload') }}?t=' + Date.now();

            const startTime = performance.now();
            let totalBytesSent = 0;
            const maxDurationMs = 3500;
            let iteration = 0;
            const maxIterations = 8;

            while ((performance.now() - startTime) < maxDurationMs && iteration < maxIterations) {
                if (signal.aborted) break;
                iteration++;

                await new Promise((resolve) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', uploadUrl, true);
                    xhr.setRequestHeader('Cache-Control', 'no-store');

                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    if (csrfMeta) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfMeta.getAttribute('content'));
                    }

                    signal.addEventListener('abort', () => {
                        xhr.abort();
                        resolve();
                    });

                    xhr.upload.onprogress = (e) => {
                        if (signal.aborted) return;
                        const elapsedSec = (performance.now() - startTime) / 1000;
                        if (elapsedSec > 0.1) {
                            const currentTotal = totalBytesSent + e.loaded;
                            const instantMbps = (currentTotal * 8) / elapsedSec / 1000000;
                            if (instantMbps > 0) {
                                this.currentDisplaySpeed = (this.currentDisplaySpeed * 0.4) + (instantMbps * 0.6);
                            }
                            const uploadProgress = 65 + Math.min(30, (elapsedSec / 3.5) * 30);
                            this.progress = Math.round(uploadProgress);
                        }
                    };

                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            totalBytesSent += chunkSize;
                        }
                        resolve();
                    };

                    xhr.onerror = () => resolve();
                    xhr.ontimeout = () => resolve();
                    xhr.send(blob);
                });
            }

            const totalDurationSec = (performance.now() - startTime) / 1000;
            if (totalDurationSec > 0 && totalBytesSent > 0) {
                this.uploadSpeed = ((totalBytesSent * 8) / totalDurationSec) / 1000000;
            } else if (this.downloadSpeed > 0) {
                this.uploadSpeed = Math.round(this.downloadSpeed * 0.5 * 10) / 10;
            }
            this.currentDisplaySpeed = this.uploadSpeed;
        }
    };
}
</script>
