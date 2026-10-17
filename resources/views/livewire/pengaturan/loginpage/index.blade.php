<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined notranslate" translate="no">wifi_tethering</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Download LoginPage Hotspot</h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Template halaman login MikroTik Hotspot modern yang terhubung langsung dengan sistem dsBilling.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ $githubUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                <span>Repositori GitHub</span>
                <span class="material-symbols-outlined text-[15px]" translate="no">open_in_new</span>
            </a>
            <a href="{{ $downloadZipUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]" translate="no">cloud_download</span>
                <span>Download ZIP (v2.0)</span>
            </a>
        </div>
    </div>

    <!-- Main Content Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Download Box & Feature Overview (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Download Hero Banner -->
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 opacity-10">
                    <span class="material-symbols-outlined text-[200px]" translate="no">download</span>
                </div>
                <div class="relative z-10 max-w-xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold uppercase tracking-wider mb-3">
                        <span class="material-symbols-outlined text-xs" translate="no">stars</span>
                        Template Resmi dsBilling
                    </span>
                    <h2 class="text-2xl font-extrabold tracking-tight">dsLoginPage MikroTik Hotspot</h2>
                    <p class="text-blue-100 text-sm mt-2 leading-relaxed">
                        Desain baru bergaya aplikasi Android dengan pemisahan paket Voucher, Hotspot Member, dan Rumahan (PPPoE), serta integrasi tombol Beli Paket otomatis ke Payment Gateway.
                    </p>

                    <div class="mt-6 flex items-center gap-3 flex-wrap">
                        <a href="{{ $downloadZipUrl }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-blue-700 font-bold text-sm shadow hover:bg-blue-50 transition-colors">
                            <span class="material-symbols-outlined text-base" translate="no">download</span>
                            <span>Download File ZIP</span>
                        </a>
                        <a href="{{ $githubUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-800/60 hover:bg-blue-800 text-white font-medium text-sm transition-colors border border-blue-400/30">
                            <span>Lihat Source Code di GitHub</span>
                            <span class="material-symbols-outlined text-xs" translate="no">open_in_new</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Highlights -->
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-500" translate="no">featured_play_list</span>
                    <span>Fitur & Keunggulan Template</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700 flex items-start gap-3">
                        <span class="p-2 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                            <span class="material-symbols-outlined text-lg" translate="no">smartphone</span>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-slate-200">Tampilan Ala Aplikasi Android</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tab kategori terpisah: Voucher WiFi, Hotspot Member, dan Rumahan (PPPoE) dengan chip spesifikasi rapi.</p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700 flex items-start gap-3">
                        <span class="p-2 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-lg" translate="no">shopping_cart_checkout</span>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-slate-200">Beli Paket & Payment Gateway</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tombol beli langsung membuka checkout pembayaran online (Midtrans, Xendit, QRIS, e-Wallet, dll).</p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700 flex items-start gap-3">
                        <span class="p-2 rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                            <span class="material-symbols-outlined text-lg" translate="no">receipt_long</span>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-slate-200">Cek Tagihan Otomatis</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pelanggan dapat memasukkan ID Pelanggan atau No Invoice untuk mengecek tagihan belum lunas.</p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700 flex items-start gap-3">
                        <span class="p-2 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                            <span class="material-symbols-outlined text-lg" translate="no">sync_lock</span>
                        </span>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-slate-200">Status Akun & Kick User</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Mengecek masa aktif voucher/member dan fitur kick sesi jika akun tersangkut di perangkat lain.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Installation Guide (1 col) -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-500" translate="no">install_mobile</span>
                    <span>Panduan Pemasangan</span>
                </h3>

                <ol class="relative border-l border-slate-200 dark:border-slate-700 ml-3 space-y-4 text-xs">
                    <li class="pl-4 relative">
                        <div class="absolute -left-1.5 top-0.5 w-3 h-3 rounded-full bg-blue-600 ring-4 ring-white dark:ring-slate-800"></div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">1. Download & Ekstrak</h4>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">Download file ZIP di samping, lalu ekstrak hingga muncul folder <code class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-blue-600 font-mono">dsloginpage</code>.</p>
                    </li>
                    <li class="pl-4 relative">
                        <div class="absolute -left-1.5 top-0.5 w-3 h-3 rounded-full bg-blue-600 ring-4 ring-white dark:ring-slate-800"></div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">2. Upload ke MikroTik</h4>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">Buka Winbox, klik menu <b>Files</b>, lalu drag & drop folder <code class="font-mono text-slate-600 dark:text-slate-300">dsloginpage</code> ke direktori utama MikroTik.</p>
                    </li>
                    <li class="pl-4 relative">
                        <div class="absolute -left-1.5 top-0.5 w-3 h-3 rounded-full bg-blue-600 ring-4 ring-white dark:ring-slate-800"></div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">3. Arahkan Server Profile</h4>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">Di Winbox, buka <b>IP > Hotspot > Server Profiles</b>. Buka profil server Hotspot Anda, ubah <b>HTML Directory</b> menjadi <code class="font-mono text-blue-600">dsloginpage</code>.</p>
                    </li>
                    <li class="pl-4 relative">
                        <div class="absolute -left-1.5 top-0.5 w-3 h-3 rounded-full bg-emerald-600 ring-4 ring-white dark:ring-slate-800"></div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">4. Atur Walled Garden (Wajib)</h4>
                        <p class="text-slate-500 dark:text-slate-400 mt-0.5">Izinkan akses ke domain dsBilling sebelum login agar paket dan tagihan dapat dimuat:</p>
                    </li>
                </ol>

                <div class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-[11px] leading-relaxed relative group">
                    <pre class="overflow-x-auto whitespace-pre-wrap">/ip hotspot walled-garden
add dst-host=winets.mstore.id
add dst-host=*.winets.mstore.id
add dst-host=*.midtrans.com
add dst-host=*.xendit.co
add dst-host=*.duitku.com
add dst-host=*.tripay.co.id</pre>
                </div>
            </div>
        </div>
    </div>
</div>
