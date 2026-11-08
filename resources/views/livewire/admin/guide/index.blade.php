<div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-indigo-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined text-[200px]">admin_panel_settings</span>
        </div>
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-500/20 backdrop-blur-md rounded-full border border-blue-400/30 text-blue-200 text-xs font-semibold">
                <span class="material-symbols-outlined text-sm">menu_book</span>
                <span>Portal Master SOP & Standar Kerja Kantor Pusat dsBilling</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                Pusat Panduan Operasional Staf & Manajemen
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                Pusat pedoman kerja terpadu untuk peran Administrator Sistem, Kasir/Billing, Customer Service (CS/Helpdesk), dan Manajemen Eksekutif.
            </p>
        </div>
    </div>

    <!-- Quick Navigation Pills (Role Selector) -->
    <div class="bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-2 overflow-x-auto text-xs font-bold scrollbar-none">
        <button wire:click="setRoleTab('admin')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeRoleTab === 'admin' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">settings_suggest</span>
            <span>1. Administrator & Sistem</span>
        </button>
        <button wire:click="setRoleTab('kasir')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeRoleTab === 'kasir' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">point_of_sale</span>
            <span>2. Kasir & Pembayaran</span>
        </button>
        <button wire:click="setRoleTab('cs')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeRoleTab === 'cs' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">support_agent</span>
            <span>3. Customer Service (CS/Helpdesk)</span>
        </button>
        <button wire:click="setRoleTab('manager')" type="button"
                class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $activeRoleTab === 'manager' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
            <span class="material-symbols-outlined text-sm">analytics</span>
            <span>4. Manager & Keuangan</span>
        </button>
    </div>

    <!-- Content Sections -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">

        {{-- TAB 1: ADMINISTRATOR SISTEM --}}
        @if($activeRoleTab === 'admin')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-bold rounded-lg text-[11px] uppercase">Superadmin & Admin Pusat</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">SOP & Panduan Administrator Sistem</h2>
                <p class="text-slate-500 mt-1">Mengelola konfigurasi inti, pengawasan presensi seluruh personel, router, dan bot notifikasi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-600 text-sm">badge</span>
                        <span>Pusat Kendali Personel & Workforce Monitor</span>
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Di dashboard utama, pantau tab <strong>"Personel & Karyawan"</strong>. Administrator dapat melihat siapa staf yang sedang bekerja, durasi shift, tiket aktif yang sedang ditangani, titik GPS presensi, serta tombol cepat WhatsApp ke nomor staf bersangkutan.
                    </p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-600 text-sm">send</span>
                        <span>Notifikasi Bot Telegram Otomatis</span>
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Di menu <strong>Pengaturan ➔ Notifikasi Telegram</strong>, pastikan Token Bot Telegram dan Chat ID Grup tim teknisi telah terhubung. Sistem akan mengirim notifikasi otomatis saat teknisi absen masuk/keluar, pembayaran tagihan lunas, atau terjadi gangguan massal.
                    </p>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition">
                    <span class="material-symbols-outlined text-sm">dashboard</span>
                    <span>Buka Dashboard Utama</span>
                </a>
                <a href="{{ route('pengaturan.telegram') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-slate-200 font-bold text-xs transition">
                    <span class="material-symbols-outlined text-sm">settings</span>
                    <span>Pengaturan Telegram</span>
                </a>
            </div>
        </div>
        @endif

        {{-- TAB 2: KASIR & PEMBAYARAN --}}
        @if($activeRoleTab === 'kasir')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold rounded-lg text-[11px] uppercase">Front-Office Kasir</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">SOP Kasir & Penerimaan Pembayaran Tagihan</h2>
                <p class="text-slate-500 mt-1">Standar pelayanan penerimaan uang tagihan, pencetakan struk, dan rekonsiliasi kas harian.</p>
            </div>

            <div class="space-y-3">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Alur Transaksi Kasir:</h3>
                    <ol class="list-decimal list-inside space-y-1.5 text-slate-500 dark:text-slate-400 leading-relaxed">
                        <li><strong>Wajib Absen Hadir:</strong> Kasir wajib melakukan Absen Masuk di awal jam kerja agar fitur kasir terbuka.</li>
                        <li><strong>Cari Tagihan Pelanggan:</strong> Buka menu <em>Billing ➔ Pembayaran</em>, ketik nama atau ID pelanggan yang datang ke loket.</li>
                        <li><strong>Pilih Metode Bayar:</strong> Tunai (Cash), QRIS Dinamis, atau Transfer Bank.</li>
                        <li><strong>Cetak Struk Pembayaran:</strong> Klik tombol Cetak Struk (ukuran 58mm atau 80mm) dan serahkan kepada pelanggan sebagai bukti sah.</li>
                        <li><strong>Tutup Kasir (End of Shift):</strong> Di akhir jam shift, buka menu <em>Keuangan ➔ Pemasukan Harian</em> untuk mencocokkan total uang fisik di laci kasir dengan angka di sistem sebelum serah terima uang ke bendahara.</li>
                    </ol>
                </div>
            </div>

            <a href="{{ route('billing.payment.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">point_of_sale</span>
                <span>Buka Menu Kasir Pembayaran</span>
            </a>
        </div>
        @endif

        {{-- TAB 3: CUSTOMER SERVICE (CS) --}}
        @if($activeRoleTab === 'cs')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 font-bold rounded-lg text-[11px] uppercase">Helpdesk & CRM</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">SOP Customer Service & Penanganan Aduan</h2>
                <p class="text-slate-500 mt-1">Standar komunikasi keluhan konsumen, Customer 360, dan disposisi tiket ke teknisi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Prinsip Pelayanan CS:</h3>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-500 dark:text-slate-400 leading-relaxed">
                        <li><strong>Responsif:</strong> Balas pesan WhatsApp / tiket komplain maksimal <strong>5 menit</strong> setelah diterima.</li>
                        <li><strong>Cek Profil 360:</strong> Periksa apakah pelanggan sedang isolir karena belum bayar sebelum mengirim teknisi (banyak komplain ternyata belum lunas tagihan).</li>
                        <li><strong>Klasifikasi Tiket:</strong> Tentukan tingkat keparahan (Kritis / Tinggi / Normal).</li>
                    </ul>
                </div>

                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Disposisi & Penutupan Tiket:</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                        Tugaskan tiket ke teknisi yang memegang wilayah ODP pelanggan. Setelah teknisi menyelesaikan pekerjaan dan mengupload bukti foto/redaman, hubungi pelanggan via telepon/WhatsApp untuk memastikan internet sudah kembali normal sebelum menutup tiket.
                    </p>
                </div>
            </div>

            <a href="{{ route('support.ticket') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">support_agent</span>
                <span>Buka Menu Tiket Layanan</span>
            </a>
        </div>
        @endif

        {{-- TAB 4: MANAGER & EKSEKUTIF --}}
        @if($activeRoleTab === 'manager')
        <div class="space-y-6 text-xs animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="px-2.5 py-1 bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 font-bold rounded-lg text-[11px] uppercase">Manajemen & Keuangan</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-2">Panduan Analisis Bisnis, Laba Rugi & SLA</h2>
                <p class="text-slate-500 mt-1">Mengukur performa pendapatan operasional, omset reseller, dan kepuasan pelanggan.</p>
            </div>

            <div class="space-y-3 text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Metrik Finansial Utama:</h3>
                    <p>
                        • <strong>Laporan Laba Rugi:</strong> Bandingkan pemasukan langganan internet bulanan vs beban operasional (sewa bandwidth upstream, sewa tiang, gaji staf, pemeliharaan kabel).<br>
                        • <strong>Kinerja Reseller:</strong> Evaluasi mitra reseller dengan omset tertinggi dan wilayah potensi ekspansi jaringan.<br>
                        • <strong>Valuasi Inventaris:</strong> Memeriksa nilai aset perangkat ONT dan kabel dropcore yang tersimpan di gudang serta yang sedang beredar di tangan teknisi.
                    </p>
                </div>
            </div>

            <a href="{{ route('keuangan.laba-rugi') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm transition">
                <span class="material-symbols-outlined text-sm">analytics</span>
                <span>Buka Laporan Laba Rugi</span>
            </a>
        </div>
        @endif

    </div>
</div>
