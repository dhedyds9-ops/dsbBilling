<div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-900 via-teal-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined text-[200px]">storefront</span>
        </div>
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 backdrop-blur-md rounded-full border border-emerald-400/30 text-emerald-200 text-xs font-semibold">
                <span class="material-symbols-outlined text-sm">handshake</span>
                <span>Portal Kemitraan & RT-RW Net dsBilling</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                Buku Panduan Mitra Reseller
            </h1>
            <p class="text-xs sm:text-sm text-emerald-100/80 max-w-2xl leading-relaxed">
                Panduan praktis untuk mengelola pelanggan, topup deposit saldo, mencetak voucher hotspot, mengelola stok perangkat di area Anda, dan pemantauan modem via GenieACS.
            </p>
        </div>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-1.5 overflow-x-auto text-xs font-bold scrollbar-none">
        <button wire:click="setTab('pelanggan')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'pelanggan' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">person_add</span>
            <span>1. Pendaftaran Pelanggan</span>
        </button>
        <button wire:click="setTab('keuangan')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'keuangan' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">account_balance_wallet</span>
            <span>2. Saldo & Topup</span>
        </button>
        <button wire:click="setTab('voucher')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'voucher' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">confirmation_number</span>
            <span>3. Cetak Voucher</span>
        </button>
        <button wire:click="setTab('material')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'material' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">inventory</span>
            <span>4. Stok Material & Custody</span>
        </button>
        <button wire:click="setTab('jaringan')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'jaringan' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">router</span>
            <span>5. Monitoring & TR-069</span>
        </button>
        <button wire:click="setTab('tickets')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'tickets' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">headset_mic</span>
            <span>6. Tiket Bantuan</span>
        </button>
    </div>

    <!-- Content Sections -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">

        {{-- TAB 1: PELANGGAN --}}
        @if($activeTab === 'pelanggan')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold rounded-lg text-[11px] uppercase">Registrasi Pelanggan</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Cara Menambahkan Pelanggan Baru (PPPoE / Hotspot)</h2>
                <p class="text-slate-500 mt-1">Langkah cepat mengaktifkan layanan internet untuk pelanggan di coverage area Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs">1</span>
                        <span>Isi Data Pelanggan</span>
                    </span>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Buka menu <strong>Pelanggan ➔ Tambah Pelanggan</strong>. Masukkan nama lengkap, nomor WhatsApp aktif (untuk notifikasi tagihan otomatis), dan alamat rumah.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs">2</span>
                        <span>Pilih Paket & ODP</span>
                    </span>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Pilih Profil Paket langganan (kecepatan Mbps), pilih tiang ODP dan nomor port terhubung. Sistem akan otomatis mengisi parameter PPPoE secret ke router Mikrotik.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs">3</span>
                        <span>Aktivasi & Invoice</span>
                    </span>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Sistem langsung menerbitkan invoice bulan pertama. Tagihan otomatis dipotong dari saldo deposit reseller Anda sesuai harga dasar kemitraan.
                    </p>
                </div>
            </div>

            <a href="{{ route('reseller-portal.customers.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">person_add</span>
                <span>Buka Form Tambah Pelanggan</span>
            </a>
        </div>
        @endif

        {{-- TAB 2: SALDO & KEUANGAN --}}
        @if($activeTab === 'keuangan')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-bold rounded-lg text-[11px] uppercase">Finansial & Deposit</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Pengisian Saldo (Topup) & Laporan Komisi</h2>
                <p class="text-slate-500 mt-1">Mengelola saldo akun untuk aktivasi paket dan pencairan keuntungan penjualan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Alur Topup Deposit Saldo:</h3>
                    <ol class="list-decimal list-inside space-y-1.5 text-slate-500 dark:text-slate-400 leading-relaxed">
                        <li>Buka menu <strong>Keuangan ➔ Topup Saldo</strong>.</li>
                        <li>Masukkan nominal isi saldo (misal: Rp 500.000).</li>
                        <li>Transfer ke rekening resmi kantor yang tertera di layar.</li>
                        <li>Upload foto struk / tangkapan layar bukti transfer ATM/M-Banking.</li>
                        <li>Admin keuangan kantor akan memverifikasi dan saldo Anda otomatis bertambah seketika.</li>
                    </ol>
                </div>

                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Laba & Komisi Penjualan:</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Setiap transaksi penjualan voucher hotspot atau pembayaran bulanan PPPoE oleh pelanggan Anda memberikan selisih margin keuntungan (komisi) yang dapat Anda pantau di menu <strong>Laporan Penjualan</strong> dan dapat ditarik (*withdraw*) berkala.
                    </p>
                </div>
            </div>

            <a href="{{ route('reseller-portal.finance.topup') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">account_balance_wallet</span>
                <span>Buka Menu Topup Saldo</span>
            </a>
        </div>
        @endif

        {{-- TAB 3: VOUCHER HOTSPOT --}}
        @if($activeTab === 'voucher')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-bold rounded-lg text-[11px] uppercase">Bisnis Voucher</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Generate & Cetak Voucher Hotspot</h2>
                <p class="text-slate-500 mt-1">Mencetak voucher untuk warung kopi, kafe, konter pulsa, atau warga sekitar.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Langkah Cetak Voucher:</h3>
                    <p>1. Buka menu <strong>Sales ➔ Voucher Hotspot</strong>.<br>
                       2. Pilih Profil Paket Durasi (misal: <em>2 Jam, 1 Hari, 1 Minggu</em>).<br>
                       3. Masukkan jumlah kuantiti lembar voucher (misal: 50 voucher).<br>
                       4. Pilih format cetak: <strong>Thermal Paper (58mm/80mm)</strong> untuk printer bluetooth kasir, atau <strong>A4 Grid</strong> untuk gunting manual.<br>
                       5. Klik <strong>Generate & Print</strong>. Voucher siap langsung dijual ke pembeli!</p>
                </div>
            </div>

            <a href="{{ route('reseller-portal.sales.voucher') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">print</span>
                <span>Buka Menu Cetak Voucher</span>
            </a>
        </div>
        @endif

        {{-- TAB 4: STOK MATERIAL & CUSTODY --}}
        @if($activeTab === 'material')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 font-bold rounded-lg text-[11px] uppercase">Manajemen Perangkat</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Pengelolaan Stok Modem & Serah Terima</h2>
                <p class="text-slate-500 mt-1">Aturan penguasaan perangkat titipan kantor dan opsi pasang mandiri.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 space-y-2">
                    <h3 class="font-bold text-indigo-700 dark:text-indigo-300 text-sm">Opsi A: Serahkan ke Teknisi Kantor</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Jika penarikan kabel dan penyambungan dikerjakan oleh teknisi kantor, buka menu <strong>Inventory ➔ Stok di Tangan</strong>, klik tombol <strong>"Serahkan ke Teknisi"</strong> pada unit ONT terkait, lalu pilih teknisi penerima.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 space-y-2">
                    <h3 class="font-bold text-emerald-700 dark:text-emerald-300 text-sm">Opsi B: Anda Pasang Sendiri (Mandiri)</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Jika Anda memasang kabel dan modem sendiri tanpa bantuan teknisi, Anda <strong>tidak perlu serah terima</strong>. Cukup daftarkan pelanggan di menu pelanggan, sistem otomatis mengubah status ONT dari titipan menjadi terpasang (*installed*).
                    </p>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('inventory.custodies.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm transition">
                    <span class="material-symbols-outlined text-sm">inventory_2</span>
                    <span>Lihat Stok di Tangan (Custody)</span>
                </a>
                <a href="{{ route('inventory.requisitions.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-slate-200 font-bold text-xs transition">
                    <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                    <span>Minta Stok Tambahan</span>
                </a>
            </div>
        </div>
        @endif

        {{-- TAB 5: MONITORING JARINGAN --}}
        @if($activeTab === 'jaringan')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-cyan-50 dark:bg-cyan-950/50 text-cyan-700 dark:text-cyan-300 font-bold rounded-lg text-[11px] uppercase">Network & ACS</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Monitoring Status Modem Pelanggan (TR-069)</h2>
                <p class="text-slate-500 mt-1">Mengecek kondisi redaman optik dan merestart modem dari jarak jauh.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <p>Melalui menu <strong>Network & GIS ➔ GenieACS (TR-069)</strong>, Anda dapat:</p>
                <ul class="list-disc list-inside space-y-1.5 pl-2">
                    <li>Melihat apakah modem pelanggan sedang <strong>Online</strong> atau <strong>Offline / Mati Lampu</strong>.</li>
                    <li>Melihat nilai redaman optik penerimaan (Rx Power dBm). Jika redaman lebih buruk dari <strong>-25 dBm</strong>, segera periksa sambungan kabel di tiang ODP.</li>
                    <li>Merestart modem pelanggan secara remote dari HP tanpa perlu datang ke rumah pelanggan.</li>
                    <li>Melihat nama WiFi dan mengganti password WiFi pelanggan jika pelanggan lupa password.</li>
                </ul>
            </div>

            <a href="{{ route('reseller-portal.network.acs-devices.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">router</span>
                <span>Buka Monitoring GenieACS</span>
            </a>
        </div>
        @endif

        {{-- TAB 6: TIKET GANGGUAN --}}
        @if($activeTab === 'tickets')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 font-bold rounded-lg text-[11px] uppercase">Customer Support</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Eskalasi Tiket Aduan Pelanggan</h2>
                <p class="text-slate-500 mt-1">Melaporkan keluhan pelanggan yang membutuhkan bantuan tim teknis kantor pusat.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <p>Jika terjadi kendala kabel putus akibat tertabrak pohon/truk, atau masalah server backbone pusat:</p>
                <ol class="list-decimal list-inside space-y-1.5 pl-2">
                    <li>Buka menu <strong>Support ➔ Tiket Gangguan</strong>.</li>
                    <li>Klik <strong>Buat Tiket Baru</strong>, pilih nama pelanggan yang terdampak.</li>
                    <li>Tuliskan gejala gangguan secara jelas (misal: <em>LOS merah setelah hujan deras</em>).</li>
                    <li>Tim Helpdesk / NOC kantor pusat akan langsung menerima notifikasi dan mendisposisikan tim teknisi lapangan ke lokasi Anda.</li>
                </ol>
            </div>

            <a href="{{ route('reseller-portal.tickets.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">headset_mic</span>
                <span>Buka Tiket Aduan</span>
            </a>
        </div>
        @endif

    </div>
</div>
