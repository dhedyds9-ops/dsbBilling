<div class="p-4 sm:p-6 space-y-6 max-w-5xl mx-auto">
    <!-- Header Banner -->
    <div class="rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 50%, #1e40af 100%) !important; color: #ffffff !important;">
        <div class="absolute -right-6 -bottom-6 opacity-15 pointer-events-none" style="color: #ffffff;">
            <span class="material-symbols-outlined text-[180px]">contact_support</span>
        </div>
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold" style="background-color: rgba(255, 255, 255, 0.2); color: #dbeafe;">
                <span class="material-symbols-outlined text-sm">support_agent</span>
                <span>Pusat Bantuan & Layanan Mandiri Pelanggan</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #ffffff !important;">
                Panduan Layanan Internet Anda
            </h1>
            <p class="text-xs sm:text-sm max-w-xl leading-relaxed" style="color: #dbeafe !important;">
                Temukan panduan mudah cara pembayaran tagihan, ganti nama & password WiFi rumah, lapor gangguan internet, dan tips koneksi cepat.
            </p>
        </div>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-1.5 overflow-x-auto text-xs font-bold scrollbar-none">
        <button wire:click="setTab('bayar')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'bayar' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">credit_card</span>
            <span>Cara Bayar Tagihan</span>
        </button>
        <button wire:click="setTab('wifi')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'wifi' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">wifi</span>
            <span>Pengaturan WiFi</span>
        </button>
        <button wire:click="setTab('tiket')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'tiket' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">report_problem</span>
            <span>Lapor Gangguan</span>
        </button>
        <button wire:click="setTab('paket')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'paket' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">speed</span>
            <span>Upgrade Paket</span>
        </button>
        <button wire:click="setTab('tips')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeTab === 'tips' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">tips_and_updates</span>
            <span>Tips WiFi Ngebut</span>
        </button>
    </div>

    <!-- Content Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">

        {{-- TAB 1: CARA BAYAR --}}
        @if($activeTab === 'bayar')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-bold rounded-lg text-[11px] uppercase">Pembayaran Tagihan</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Cara Pembayaran Tagihan Internet</h2>
                <p class="text-slate-500 mt-1">Pembayaran tagihan dapat dilakukan melalui berbagai kanal resmi secara otomatis 24 jam.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">qr_code_scanner</span>
                        <span>QRIS / E-Wallet</span>
                    </span>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Scan kode QRIS yang muncul di invoice menggunakan aplikasi GoPay, OVO, DANA, ShopeePay, atau Mobile Banking apa saja. Tagihan langsung lunas seketika tanpa perlu konfirmasi manual.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">account_balance</span>
                        <span>Virtual Account (VA)</span>
                    </span>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Pilih nomor Virtual Account bank Anda (BCA, BRI, Mandiri, BNI, Permata). Masukkan nomor VA pada menu Transfer VA di ATM atau M-Banking Anda.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">store</span>
                        <span>Gerai / Agen Reseller</span>
                    </span>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Anda juga dapat membayar tunai ke mitra Reseller / loket pembayaran di lingkungan tempat tinggal Anda dengan menunjukkan Nomor Pelanggan Anda.
                    </p>
                </div>
            </div>

            <a href="{{ route('customer-portal.billing.invoice-list') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">receipt_long</span>
                <span>Lihat Tagihan Saya</span>
            </a>
        </div>
        @endif

        {{-- TAB 2: PENGATURAN WIFI --}}
        @if($activeTab === 'wifi')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg text-[11px] uppercase">Koneksi WiFi</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Nama WiFi & Ganti Password</h2>
                <p class="text-slate-500 mt-1">Kelola keamanan jaringan nirkabel di rumah Anda.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Cara Melihat Nama & Password WiFi:</h3>
                    <p>
                        1. Buka menu <strong>Info Koneksi / WiFi</strong> di portal ini.<br>
                        2. Anda dapat melihat Nama WiFi (SSID) yang aktif dan status sambungan modem.<br>
                        3. Jika ingin mengganti password WiFi atau nama WiFi, Anda dapat menekan tombol <strong>Ubah Password WiFi</strong> atau mengirim permintaan via tiket bantuan tanpa perlu memanggil teknisi ke rumah.
                    </p>
                </div>
            </div>

            <a href="{{ route('customer-portal.wifi.ssid') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">wifi</span>
                <span>Buka Info Koneksi WiFi</span>
            </a>
        </div>
        @endif

        {{-- TAB 3: LAPOR GANGGUAN --}}
        @if($activeTab === 'tiket')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 font-bold rounded-lg text-[11px] uppercase">Bantuan Teknis</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Cara Melaporkan Gangguan Internet</h2>
                <p class="text-slate-500 mt-1">Layanan aduan resmi jika internet lambat, lampu modem merah (LOS), atau tidak bisa browsing.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Langkah Cepat Sebelum Lapor:</h3>
                    <p>
                        • Coba matikan saklar adaptor modem Anda, tunggu 1 menit, lalu nyalakan kembali.<br>
                        • Periksa lampu indikator pada modem: jika lampu <strong>LOS berkedip merah</strong>, artinya kabel optik di luar rumah mungkin terkena pohon/truk. Segera buat tiket keluhan di bawah ini.
                    </p>
                </div>
            </div>

            <a href="{{ route('customer-portal.support.ticket-list') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">headset_mic</span>
                <span>Buat Tiket Gangguan</span>
            </a>
        </div>
        @endif

        {{-- TAB 4: UPGRADE PAKET --}}
        @if($activeTab === 'paket')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold rounded-lg text-[11px] uppercase">Layanan Mandiri</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Cek Paket & Upgrade Kecepatan</h2>
                <p class="text-slate-500 mt-1">Tingkatkan kecepatan Mbps internet rumah Anda kapan saja.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <p>
                    Keluarga semakin banyak yang menggunakan streaming TV, game online, atau zoom meeting? Anda dapat mengajukan kenaikan paket kecepatan internet secara instan melalui menu <strong>Self Service ➔ Ubah Paket</strong>.
                </p>
            </div>

            <a href="{{ route('customer-portal.self-service.change-plan') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">upgrade</span>
                <span>Ubah Paket Langganan</span>
            </a>
        </div>
        @endif

        {{-- TAB 5: TIPS WIFI NGEBUT --}}
        @if($activeTab === 'tips')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-bold rounded-lg text-[11px] uppercase">Edukasi & Tips</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Tips Agar WiFi di Rumah Selalu Cepat & Stabil</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">1. Penempatan Posisi Router Modem</h4>
                    <p>
                        Letakkan modem di ruang terbuka dan setinggi dada/meja. Hindari meletakkan modem di lantai, di dalam lemari kaca, atau di balik tembok tebal karena sinyal WiFi melemah bila terhalang beton.
                    </p>
                </div>

                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">2. Jauhkan dari Elektronik Penghasil Gelombang</h4>
                    <p>
                        Hindari menaruh modem tepat di samping microwave, kulkas, speaker besar, atau dispenser air yang dapat mengganggu frekuensi gelombang 2.4 GHz WiFi.
                    </p>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
