@section('header_title', 'Panduan Teknisi Lapangan')

<div class="space-y-4 p-4 pb-28 max-w-xl mx-auto">
    <!-- Header Banner -->
    <div class="rounded-2xl p-5 shadow-md relative overflow-hidden" style="background: linear-gradient(135deg, #d97706 0%, #ea580c 50%, #b45309 100%) !important; color: #ffffff !important;">
        <div class="absolute -right-4 -bottom-4 opacity-15 pointer-events-none" style="color: #ffffff;">
            <span class="material-symbols-outlined text-[130px]">engineering</span>
        </div>
        <div class="relative z-10 space-y-1.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase" style="background-color: rgba(0, 0, 0, 0.25); color: #fef3c7;">
                <span class="material-symbols-outlined text-xs">verified</span>
                <span>Buku Pedoman Kerja Teknisi ISP</span>
            </div>
            <h1 class="text-xl font-black" style="color: #ffffff !important;">Panduan Operasional Lapangan</h1>
            <p class="text-[11px] leading-relaxed" style="color: #fef3c7 !important;">
                Panduan resmi tata cara absensi GPS, penyelesaian tiket pasang baru (PSB), perbaikan gangguan, pemakaian kabel/modem, serta pencatatan bukti kerja.
            </p>
        </div>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-2xl flex items-center shadow-inner gap-1 overflow-x-auto text-xs font-bold scrollbar-none">
        <button wire:click="setTab('absensi')" type="button"
                class="flex-1 py-2 px-2.5 rounded-xl transition-all flex items-center justify-center gap-1 shrink-0
                {{ $activeTab === 'absensi' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-sm">fingerprint</span>
            <span>Absensi</span>
        </button>
        <button wire:click="setTab('jobs')" type="button"
                class="flex-1 py-2 px-2.5 rounded-xl transition-all flex items-center justify-center gap-1 shrink-0
                {{ $activeTab === 'jobs' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-sm">assignment</span>
            <span>Tugas & Tiket</span>
        </button>
        <button wire:click="setTab('psb')" type="button"
                class="flex-1 py-2 px-2.5 rounded-xl transition-all flex items-center justify-center gap-1 shrink-0
                {{ $activeTab === 'psb' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-sm">build_circle</span>
            <span>Instalasi PSB</span>
        </button>
        <button wire:click="setTab('material')" type="button"
                class="flex-1 py-2 px-2.5 rounded-xl transition-all flex items-center justify-center gap-1 shrink-0
                {{ $activeTab === 'material' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-sm">inventory_2</span>
            <span>Stok & Alat</span>
        </button>
        <button wire:click="setTab('odp')" type="button"
                class="flex-1 py-2 px-2.5 rounded-xl transition-all flex items-center justify-center gap-1 shrink-0
                {{ $activeTab === 'odp' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-sm">share_location</span>
            <span>ODP & GPS</span>
        </button>
        <button wire:click="setTab('troubleshooting')" type="button"
                class="flex-1 py-2 px-2.5 rounded-xl transition-all flex items-center justify-center gap-1 shrink-0
                {{ $activeTab === 'troubleshooting' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
            <span class="material-symbols-outlined text-sm">help</span>
            <span>Solusi & FAQ</span>
        </button>
    </div>

    <!-- TAB 1: ABSENSI -->
    @if($activeTab === 'absensi')
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 text-xs animate-fadeIn">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 font-bold rounded text-[10px] uppercase">SOP Presensi</span>
            <h2 class="text-base font-black text-slate-800 dark:text-slate-100 mt-1">Tata Cara Presensi GPS Teknisi</h2>
            <p class="text-slate-500 text-[11px] mt-0.5">Aturan kehadiran wajib sebelum memulai pekerjaan di lapangan.</p>
        </div>

        <div class="space-y-3">
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 space-y-1">
                <p class="font-bold text-slate-800 dark:text-slate-200">1. Geofencing Multi-Cabang (Radius 150 Meter)</p>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed text-[11px]">
                    Sistem mendeteksi seluruh kantor cabang aktif. Jika Anda teknisi kantor pusat namun hari ini ditugaskan di kantor cabang A, Anda <strong>tetap bisa absen hadir</strong> selama berada dalam radius 150 meter dari kantor cabang tersebut.
                </p>
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 space-y-1">
                <p class="font-bold text-slate-800 dark:text-slate-200">2. Mode Dinas Luar Kota (Field Assignment)</p>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed text-[11px]">
                    Jika Anda langsung meluncur ke lokasi ODP / rumah pelanggan di luar jangkauan kantor, aktifkan toggle <strong>"Tugas Luar / Dinas Lapangan"</strong> dan isi catatan tugas. Sistem akan mencatat titik GPS dan keterangan dinas Anda.
                </p>
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 space-y-1">
                <p class="font-bold text-slate-800 dark:text-slate-200">3. Pengaman Anti Salah Pencet (Kunci 15 Menit & Modal)</p>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed text-[11px]">
                    Setelah Anda menekan <em>Absen Masuk</em>, tombol <em>Check-Out</em> akan terkunci otomatis selama 15 menit untuk mencegah kepencet ganda. Saat pulang dan menekan Check-Out, sistem akan memunculkan pop-up konfirmasi durasi dinas Anda.
                </p>
            </div>
        </div>

        <a href="{{ route('technician.attendance') }}" class="w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
            <span class="material-symbols-outlined text-sm">fingerprint</span>
            <span>Buka Halaman Absensi</span>
        </a>
    </div>
    @endif

    <!-- TAB 2: TUGAS & TIKET -->
    @if($activeTab === 'jobs')
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 text-xs animate-fadeIn">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 font-bold rounded text-[10px] uppercase">My Jobs</span>
            <h2 class="text-base font-black text-slate-800 dark:text-slate-100 mt-1">SOP Penanganan Tiket & Pasang Baru</h2>
            <p class="text-slate-500 text-[11px] mt-0.5">Panduan alur tiket dari disposisi sampai penutupan.</p>
        </div>

        <div class="space-y-3">
            <div class="flex gap-3 items-start p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white font-black flex items-center justify-center text-xs shrink-0">1</span>
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-200">Terima & Pelajari Tiket</p>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">
                        Buka menu <strong>Tugas Saya</strong>. Periksa nama pelanggan, nomor telepon/WhatsApp, alamat, dan deskripsi keluhan (misal: Redaman tinggi / kabel putus).
                    </p>
                </div>
            </div>

            <div class="flex gap-3 items-start p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white font-black flex items-center justify-center text-xs shrink-0">2</span>
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-200">Update Status Menjadi "In Progress"</p>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">
                        Saat tiba di lokasi atau mulai mengerjakan, ubah status tiket agar admin/helpdesk mengetahui teknisi sedang menangani kendala tersebut.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 items-start p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white font-black flex items-center justify-center text-xs shrink-0">3</span>
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-200">Wajib Lampirkan Bukti Kerja & Material</p>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">
                        Saat menutup tiket (Resolve), lampirkan:
                        <br>• Foto bukti hasil perbaikan / instalasi
                        <br>• Nilai redaman OPM (Contoh: -19.5 dBm)
                        <br>• Material terpakai (Fast connector, kabel, dll) agar stok bagasi terpotong akurat.
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('technician.my-jobs.index') }}" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
            <span class="material-symbols-outlined text-sm">assignment</span>
            <span>Buka Daftar Tugas Saya</span>
        </a>
    </div>
    @endif

    <!-- TAB 3: WIZARD PSB -->
    @if($activeTab === 'psb')
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 text-xs animate-fadeIn">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 font-bold rounded text-[10px] uppercase">7 Langkah Standar</span>
            <h2 class="text-base font-black text-slate-800 dark:text-slate-100 mt-1">Wizard Pemasangan Baru (PSB)</h2>
            <p class="text-slate-500 text-[11px] mt-0.5">Langkah teknis mulai dari ODP hingga aktivasi internet pelanggan.</p>
        </div>

        <div class="space-y-2.5 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Langkah 1: Pilih Order & Verifikasi Pelanggan</span>
                <p>Pilih nama calon pelanggan yang telah lunas administrasi atau ditugaskan kepada Anda.</p>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Langkah 2: Pilih ODP & Port Aktif</span>
                <p>Pilih ODP terdekat dan pastikan nomor port yang digunakan belum terisi pelanggan lain.</p>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Langkah 3: Input Panjang Kabel Dropcore & Redaman</span>
                <p>Masukkan meteran kabel dropcore yang ditarik dan ukur redaman OPM (Standar: antara -16 s/d -23 dBm).</p>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Langkah 4: Scan Nomor Seri (SN) ONT / Modem</span>
                <p>Gunakan kamera untuk scan barcode SN pada punggung ONT yang Anda bawa dari bagasi.</p>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Langkah 5: Auto-Provisioning GenieACS (TR-069)</span>
                <p>Sistem akan mengirim perintah konfigurasi username PPPoE, WiFi SSID & password ke modem secara otomatis tanpa perlu Anda setting manual lewat laptop.</p>
            </div>
        </div>

        <a href="{{ route('technician.installation.wizard') }}" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
            <span class="material-symbols-outlined text-sm">build_circle</span>
            <span>Mulai Wizard Instalasi</span>
        </a>
    </div>
    @endif

    <!-- TAB 4: STOK & MATERIAL -->
    @if($activeTab === 'material')
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 text-xs animate-fadeIn">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold rounded text-[10px] uppercase">Bagasi & Toolbox</span>
            <h2 class="text-base font-black text-slate-800 dark:text-slate-100 mt-1">Stok Saya & Permintaan Material</h2>
            <p class="text-slate-500 text-[11px] mt-0.5">Mengelola barang fisik yang Anda bawa saat bertugas.</p>
        </div>

        <div class="space-y-2.5 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
            <div class="p-3 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/40 space-y-1">
                <p class="font-bold text-indigo-700 dark:text-indigo-300">Ambil Barang Mandiri (Self-Take di Kantor)</p>
                <p>Saat Anda di kantor, buka tab <strong>"Gudang Kantor"</strong> pada menu Stok, lalu klik <strong>"Ambil Barang"</strong> untuk mengambil fast connector, kabel, atau ONT.</p>
            </div>
            <div class="p-3 bg-purple-50/50 dark:bg-purple-950/30 rounded-xl border border-purple-100 dark:border-purple-900/40 space-y-1">
                <p class="font-bold text-purple-700 dark:text-purple-300">Terima Barang dari Reseller (Handover)</p>
                <p>Jika Reseller menyerahkan ONT di lokasi, buka menu <strong>Distribusi & Serah Terima</strong> lalu klik <strong>"Konfirmasi Terima"</strong> agar barang resmi masuk ke bagasi Anda.</p>
            </div>
            <div class="p-3 bg-rose-50/50 dark:bg-rose-950/30 rounded-xl border border-rose-100 dark:border-rose-900/40 space-y-1">
                <p class="font-bold text-rose-700 dark:text-rose-300">Retur / Lapor Rusak</p>
                <p>Jika ada fast connector cacat atau modem tersambar petir saat dicoba, klik tombol <strong>"Retur / Lapor"</strong> pada item terkait agar Anda tidak ditagih selisih stok saat audit.</p>
            </div>
        </div>

        <a href="{{ route('technician.material.index') }}" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
            <span class="material-symbols-outlined text-sm">inventory_2</span>
            <span>Buka Stok Saya</span>
        </a>
    </div>
    @endif

    <!-- TAB 5: ODP & GPS -->
    @if($activeTab === 'odp')
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 text-xs animate-fadeIn">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <span class="px-2 py-0.5 bg-cyan-50 dark:bg-cyan-950/50 text-cyan-600 dark:text-cyan-400 font-bold rounded text-[10px] uppercase">GIS Navigasi</span>
            <h2 class="text-base font-black text-slate-800 dark:text-slate-100 mt-1">Pencarian & Scan Barcode ODP</h2>
            <p class="text-slate-500 text-[11px] mt-0.5">Menemukan titik tiang optik terdekat secara cepat.</p>
        </div>

        <div class="space-y-3 text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1">
                <p class="font-bold text-slate-800 dark:text-slate-200">Cari ODP Terdekat (Nearest ODP)</p>
                <p>Sistem akan mengurutkan tiang ODP dari jarak paling dekat dengan posisi GPS HP Anda beserta estimasi tarikan meter kabel dan jumlah sisa port kosong.</p>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1">
                <p class="font-bold text-slate-800 dark:text-slate-200">Scan QR Code ODP</p>
                <p>Di setiap kotak ODP yang terpasang stiker QR Code, gunakan fitur kamera untuk langsung membuka data detail ODP, splitter, dan port tanpa perlu mengetik kode tiang.</p>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('technician.odp.nearest') }}" class="flex-1 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs flex items-center justify-center gap-1 shadow-sm transition">
                <span class="material-symbols-outlined text-sm">share_location</span>
                <span>ODP Terdekat</span>
            </a>
            <a href="{{ route('technician.odp.search') }}" class="flex-1 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center justify-center gap-1 transition">
                <span class="material-symbols-outlined text-sm">search</span>
                <span>Cari ODP</span>
            </a>
        </div>
    </div>
    @endif

    <!-- TAB 6: TROUBLESHOOTING & FAQ -->
    @if($activeTab === 'troubleshooting')
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 text-xs animate-fadeIn">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded text-[10px] uppercase">Solusi Cepat</span>
            <h2 class="text-base font-black text-slate-800 dark:text-slate-100 mt-1">Tanya Jawab & Kendala Lapangan</h2>
        </div>

        <div class="space-y-2 text-[11px]">
            <details class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 group">
                <summary class="font-bold text-slate-800 dark:text-slate-200 cursor-pointer list-none flex items-center justify-between">
                    <span>Lampu LOS di modem berkedip merah?</span>
                    <span class="material-symbols-outlined text-sm group-open:rotate-180 transition-transform">expand_more</span>
                </summary>
                <p class="text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                    Artinya sinyal optik tidak sampai ke modem. Periksa sambungan fast connector, tekukan kabel dropcore di roset/klem tarik, dan ukur redaman dengan OPM langsung di ujung patchcord.
                </p>
            </details>

            <details class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 group">
                <summary class="font-bold text-slate-800 dark:text-slate-200 cursor-pointer list-none flex items-center justify-between">
                    <span>Provisioning GenieACS gagal / timeout?</span>
                    <span class="material-symbols-outlined text-sm group-open:rotate-180 transition-transform">expand_more</span>
                </summary>
                <p class="text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                    Pastikan redaman OPM di bawah -24 dBm dan modem telah mendapat IP WAN dari OLT. Klik tombol <strong>"Retry Provisioning"</strong> pada halaman detail instalasi.
                </p>
            </details>

            <details class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 group">
                <summary class="font-bold text-slate-800 dark:text-slate-200 cursor-pointer list-none flex items-center justify-between">
                    <span>Menu terkunci (Gembok) tidak bisa diklik?</span>
                    <span class="material-symbols-outlined text-sm group-open:rotate-180 transition-transform">expand_more</span>
                </summary>
                <p class="text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                    Sistem mewajibkan presensi. Silakan buka menu <strong>Absensi</strong> lalu klik tombol <em>Absen Masuk</em> untuk membuka seluruh akses menu tugas.
                </p>
            </details>
        </div>
    </div>
    @endif
</div>
