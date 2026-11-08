<div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e3a8a 100%) !important; color: #ffffff !important;">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none" style="color: #ffffff;">
            <span class="material-symbols-outlined text-[240px]">menu_book</span>
        </div>
        <div class="relative z-10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold" style="background-color: rgba(99, 102, 241, 0.25); border: 1px solid rgba(129, 140, 248, 0.4); color: #c7d2fe;">
                        <span class="material-symbols-outlined text-sm">verified</span>
                        <span>Standard Operating Procedure (SOP) Gudang & Logistik v2.0</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #ffffff !important;">
                        Panduan Lengkap Operasional Gudang & Inventaris
                    </h1>
                    <p class="text-xs sm:text-sm max-w-2xl leading-relaxed" style="color: #cbd5e1 !important;">
                        Buku pedoman resmi untuk staf gudang, logistik, dan admin inventaris dalam mengelola siklus persediaan barang, pengawasan hak milik sementara (*Custody Tracking*), serah terima teknisi/reseller, serta audit berkala.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('inventory.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold backdrop-blur-sm border border-white/20 transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                        <span>Dashboard Gudang</span>
                    </a>
                </div>
            </div>

            <!-- Quick Navigation Pills -->
            <div class="flex flex-wrap items-center gap-2 mt-6 pt-4 border-t border-white/10">
                <button type="button" wire:click="setSection('overview')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'overview' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">schema</span>
                    <span>Konsep Dasar</span>
                </button>
                <button type="button" wire:click="setSection('inbound')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'inbound' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">move_to_inbox</span>
                    <span>1. Barang Masuk</span>
                </button>
                <button type="button" wire:click="setSection('dispatch')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'dispatch' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">outbox</span>
                    <span>2. Barang Keluar</span>
                </button>
                <button type="button" wire:click="setSection('custody')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'custody' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">handshake</span>
                    <span>3. Distribusi & Custody</span>
                </button>
                <button type="button" wire:click="setSection('usage')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'usage' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">task_alt</span>
                    <span>4. Pemakaian Lapangan</span>
                </button>
                <button type="button" wire:click="setSection('return')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'return' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">replay</span>
                    <span>5. Retur & Uji QC</span>
                </button>
                <button type="button" wire:click="setSection('opname')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'opname' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">fact_check</span>
                    <span>6. Stok Opname</span>
                </button>
                <button type="button" wire:click="setSection('troubleshoot')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $activeSection === 'troubleshoot' ? 'bg-indigo-500 text-white shadow-md' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}">
                    <span class="material-symbols-outlined text-xs">help</span>
                    <span>FAQ & Solusi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Navigation Menu (Desktop) -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm sticky top-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Daftar Isi SOP</h3>
                <nav class="space-y-1">
                    <button type="button" wire:click="setSection('overview')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'overview' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm">schema</span>
                        <span>Konsep & Arsitektur</span>
                    </button>
                    <button type="button" wire:click="setSection('inbound')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'inbound' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm text-emerald-500">move_to_inbox</span>
                        <span>1. Barang Masuk (Inbound)</span>
                    </button>
                    <button type="button" wire:click="setSection('dispatch')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'dispatch' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm text-blue-500">outbox</span>
                        <span>2. Barang Keluar (Dispatch)</span>
                    </button>
                    <button type="button" wire:click="setSection('custody')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'custody' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm text-purple-500">handshake</span>
                        <span>3. Distribusi & Custody</span>
                    </button>
                    <button type="button" wire:click="setSection('usage')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'usage' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm text-cyan-500">task_alt</span>
                        <span>4. Pemakaian Lapangan</span>
                    </button>
                    <button type="button" wire:click="setSection('return')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'return' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm text-amber-500">replay</span>
                        <span>5. Retur & Uji QC</span>
                    </button>
                    <button type="button" wire:click="setSection('opname')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'opname' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm text-rose-500">fact_check</span>
                        <span>6. Stok Opname & Audit</span>
                    </button>
                    <button type="button" wire:click="setSection('troubleshoot')"
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left flex items-center gap-3 transition-colors {{ $activeSection === 'troubleshoot' ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <span class="material-symbols-outlined text-sm text-slate-500">help</span>
                        <span>FAQ & Masalah Umum</span>
                    </button>
                </nav>

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs text-amber-800 dark:text-amber-300 space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <span>Presensi Harian</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Pastikan melakukan <strong>Absen Masuk</strong> sebelum menjalankan transaksi input data gudang.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Details (Content Display) -->
        <div class="lg:col-span-3 space-y-6">

            {{-- 1. OVERVIEW & ARSITEKTUR --}}
            @if($activeSection === 'overview')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 rounded-lg text-[11px] font-bold uppercase tracking-wider">Arsitektur Dasar</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                        Konsep Dasar Manajemen Persediaan dsBilling
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Memahami perbedaan mendasar antara Stok Fisik Gudang dan Hak Penguasaan Sementara (*Custody Tracking*).
                    </p>
                </div>

                <!-- 2 Pilar Stok -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-5 rounded-2xl bg-blue-50/50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 space-y-3">
                        <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400 font-bold">
                            <span class="material-symbols-outlined">warehouse</span>
                            <h4>1. Stok Fisik Gudang (In-Warehouse)</h4>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Barang yang benar-benar tersimpan secara fisik di rak Gudang Utama atau Gudang Kantor Cabang. Nilai persediaan ini dikelola oleh staf gudang dan dapat dipindahkan lewat menu <strong>Pengeluaran (Dispatch)</strong>.
                        </p>
                        <div class="text-[11px] text-blue-800 dark:text-blue-300 font-mono bg-blue-100/60 dark:bg-blue-900/50 p-2 rounded-lg">
                            Tabel: inventory_stocks (Saldo Fisik)
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-purple-50/50 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 space-y-3">
                        <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold">
                            <span class="material-symbols-outlined">badge</span>
                            <h4>2. Stok Custody / Di Tangan (In-Custody)</h4>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Barang yang sudah keluar dari gudang tetapi <strong>belum terpasang di rumah pelanggan</strong>. Barang ini sedang dibawa di motor teknisi (Bagasi) atau disimpan oleh Mitra Reseller untuk stok area.
                        </p>
                        <div class="text-[11px] text-purple-800 dark:text-purple-300 font-mono bg-purple-100/60 dark:bg-purple-900/50 p-2 rounded-lg">
                            Tabel: inventory_custodies (Penguasaan)
                        </div>
                    </div>
                </div>

                <!-- Tipe Barang: Serial vs Non-Serial -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        Klasifikasi Barang di Sistem
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-indigo-600 dark:text-indigo-400">
                                <span class="material-symbols-outlined text-sm">qr_code</span>
                                <span>Barang Berseri (Serial Number Tracking)</span>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                                Contoh: <strong>ONT Modem GPON, Wireless Router, Switch Hub, SFP</strong>.<br>
                                Wajib di-scan / diinput nomor SN saat masuk. Sistem mencatat pergerakan setiap unit fisik hingga terpasang permanen ke akun pelanggan.
                            </p>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="material-symbols-outlined text-sm">straighten</span>
                                <span>Barang Non-Serial (Bulk / Kuantitas)</span>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                                Contoh: <strong>Kabel Dropcore (meter), Fast Connector (pcs), Roset, S-Clamp, Patchcord</strong>.<br>
                                Dihitung berdasarkan kuantitas / panjang meteran yang berkurang secara bertahap saat instalasi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Siklus Hidup Material Visual -->
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Siklus Hidup Perangkat (Device Lifecycle)
                    </h4>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                        <span class="px-2.5 py-1 bg-slate-200 dark:bg-slate-700 rounded-lg text-slate-700 dark:text-slate-300">Gudang (in_warehouse)</span>
                        <span class="material-symbols-outlined text-slate-400 text-sm">arrow_forward</span>
                        <span class="px-2.5 py-1 bg-purple-100 dark:bg-purple-950/60 rounded-lg text-purple-700 dark:text-purple-300">Reseller / Teknisi (in_custody)</span>
                        <span class="material-symbols-outlined text-slate-400 text-sm">arrow_forward</span>
                        <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/60 rounded-lg text-emerald-700 dark:text-emerald-300">Pelanggan (installed)</span>
                        <span class="material-symbols-outlined text-slate-400 text-sm">arrow_forward</span>
                        <span class="px-2.5 py-1 bg-amber-100 dark:bg-amber-950/60 rounded-lg text-amber-700 dark:text-amber-300">Dismantle / Retur (QC Test)</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. SOP BARANG MASUK (INBOUND) --}}
            @if($activeSection === 'inbound')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 rounded-lg text-[11px] font-bold uppercase tracking-wider">SOP Modul 1</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                            Penerimaan Barang Masuk (Inbound)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Prosedur standar menerima pengiriman barang dari Supplier / Vendor atau Pengadaan Internal.
                        </p>
                    </div>
                    <a href="{{ route('inventory.inbounds.index') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 shrink-0">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span>Buka Menu Inbound</span>
                    </a>
                </div>

                <!-- Step-by-Step Inbound -->
                <div class="space-y-4">
                    <div class="flex gap-4 items-start p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black flex items-center justify-center shrink-0 text-sm">1</div>
                        <div class="space-y-1 text-xs">
                            <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Cek Fisik & Surat Jalan (Surat Pengantar / DO)</h4>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                                Pastikan kardus/kemasan tidak rusak. Cocokkan jumlah fisik barang, tipe barang, dan nomor DO (*Delivery Order*) dari kurir/supplier dengan purchase order kantor.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black flex items-center justify-center shrink-0 text-sm">2</div>
                        <div class="space-y-1 text-xs">
                            <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Buka Menu "Barang Masuk" dan Klik "Tambah Barang Masuk"</h4>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                                Pilih <strong>Gudang Tujuan</strong> (Gudang Utama atau Gudang Cabang), pilih <strong>Nama Vendor</strong>, tanggal penerimaan, dan masukkan Nomor Referensi / Nomor Surat Jalan.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black flex items-center justify-center shrink-0 text-sm">3</div>
                        <div class="space-y-2 text-xs">
                            <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Input Item Barang & Perekaman Serial Number</h4>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                                Pilih nama barang dan masukkan kuantitas barang.
                            </p>
                            <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-amber-800 dark:text-amber-300">
                                <strong>Penting untuk ONT Modem / Router:</strong> Gunakan barcode scanner atau ketik nomor seri pada kolom Serial Number (pisahkan dengan koma atau baris baru). Jumlah baris serial number <strong>wajib sama</strong> dengan kuantitas yang dimasukkan.
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black flex items-center justify-center shrink-0 text-sm">4</div>
                        <div class="space-y-1 text-xs">
                            <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Simpan Transaksi</h4>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                                Klik tombol <strong>Simpan Barang Masuk</strong>. Saldo fisik gudang otomatis bertambah, kartu stok langsung terbit mutasi <code>inbound_receipt</code>, dan seluruh Serial Number berstatus <code>in_warehouse</code>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 3. SOP BARANG KELUAR (DISPATCH) --}}
            @if($activeSection === 'dispatch')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 rounded-lg text-[11px] font-bold uppercase tracking-wider">SOP Modul 2</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                            Pengeluaran Barang Gudang (Dispatch)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Mengeluarkan material dari gudang kepada Teknisi Lapangan atau Mitra Reseller.
                        </p>
                    </div>
                    <a href="{{ route('inventory.dispatches.index') }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 shrink-0">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span>Buka Menu Dispatch</span>
                    </a>
                </div>

                <!-- 2 Jenis Metode Pengeluaran -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                        <div class="font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">two_wheeler</span>
                            <span>A. Ke Teknisi Lapangan (direct_technician)</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                            Dipilih ketika teknisi kantor datang mengambil perangkat/kabel untuk stok dinas di tas/bagasinya. Barang langsung masuk ke tab <strong>"Stok Saya"</strong> di Portal Teknisi bersangkutan.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                        <div class="font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">store</span>
                            <span>B. Ke Mitra Reseller (pickup_reseller)</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                            Dipilih ketika Reseller mengambil kuota perangkat untuk stok areanya. Barang masuk ke <strong>Custody Reseller</strong>. Reseller kemudian dapat menyerahkan ke teknisi atau memasang sendiri.
                        </p>
                    </div>
                </div>

                <!-- Langkah Dispatch -->
                <div class="space-y-3 text-xs">
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Langkah Eksekusi di Sistem:</h4>
                    <ol class="list-decimal list-inside space-y-2 text-slate-600 dark:text-slate-400 leading-relaxed">
                        <li>Buka menu <strong>Inventory ➔ Barang Keluar</strong>, klik tombol <strong>"Keluarkan Barang"</strong>.</li>
                        <li>Pilih <strong>Gudang Asal Barang</strong> yang memiliki sisa stok cukup.</li>
                        <li>Pilih <strong>Tipe Penerima</strong> (<code>Teknisi</code> atau <code>Reseller</code>) dan pilih nama staf/mitra yang menerima fisik barang.</li>
                        <li>Pilih item barang yang dikeluarkan. Jika barang berseri (ONT), <strong>pilih nomor Serial Number</strong> yang tersedia dari dropdown atau ketik nomor SN yang diambil.</li>
                        <li>Klik <strong>Simpan Pengeluaran</strong>. Fisik gudang langsung terpotong, dan sistem otomatis membuat catatan kepemilikan sementara di tabel <code>InventoryCustody</code>.</li>
                    </ol>
                </div>
            </div>
            @endif

            {{-- 4. SOP DISTRIBUSI & CUSTODY (HANDOVER) --}}
            @if($activeSection === 'custody')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 rounded-lg text-[11px] font-bold uppercase tracking-wider">SOP Modul 3</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                            Distribusi & Serah Terima Material (Custody)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Melacak barang yang sedang dipegang oleh Reseller/Teknisi serta mekanisme alih tanggung jawab.
                        </p>
                    </div>
                    <a href="{{ route('inventory.custodies.index') }}" class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 shrink-0">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span>Buka Menu Custody</span>
                    </a>
                </div>

                <!-- Penjelasan 2 Alur Distribusi -->
                <div class="space-y-4">
                    <div class="p-5 rounded-2xl bg-indigo-50/40 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 space-y-3">
                        <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400 font-bold text-sm">
                            <span class="material-symbols-outlined">account_tree</span>
                            <span>Alur A: Gudang ➔ Reseller ➔ Teknisi ➔ Pemakaian Pelanggan</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Digunakan jika Reseller bertindak sebagai pemilik wilayah, namun pemasangan fisik kabel dan modem di rumah pelanggan dikerjakan oleh <strong>Teknisi Lapangan</strong>.
                        </p>
                        <div class="bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-indigo-200 dark:border-indigo-800/60 text-xs space-y-1.5">
                            <p class="font-bold text-slate-800 dark:text-slate-200">Cara Kerja di Sistem:</p>
                            <p class="text-slate-500 dark:text-slate-400">
                                1. Reseller buka menu <strong>Inventory ➔ Distribusi & Serah Terima</strong>.<br>
                                2. Klik tombol ungu <strong>"Serahkan ke Teknisi"</strong> pada ONT/barang yang akan dipasang.<br>
                                3. Pilih Teknisi Penerima & Calon Pelanggan $\rightarrow$ Klik <strong>Kirim</strong>.<br>
                                4. Teknisi membuka tab <strong>"Riwayat & Konfirmasi"</strong> dan klik <strong>"Konfirmasi Terima"</strong>.<br>
                                5. Barang kini resmi pindah ke bagasi teknisi dan siap dipasang di rumah pelanggan.
                            </p>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-emerald-50/40 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 space-y-3">
                        <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-sm">
                            <span class="material-symbols-outlined">bolt</span>
                            <span>Alur B: Gudang ➔ Reseller ➔ Pemakaian Mandiri (Direct Usage)</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Digunakan jika <strong>Reseller memasang sendiri</strong> tanpa melibatkan teknisi kantor (mitra RT-RW Net mandiri).
                        </p>
                        <div class="bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800/60 text-xs space-y-1.5">
                            <p class="font-bold text-slate-800 dark:text-slate-200">Cara Kerja di Sistem:</p>
                            <p class="text-slate-500 dark:text-slate-400">
                                Reseller <strong>tidak perlu</strong> membuat serah terima ke teknisi. Reseller langsung menginput data pelanggan baru di <strong>Reseller Portal</strong>. Sistem otomatis memotong stok custody milik reseller dan menetapkan ONT menjadi <code>installed</code>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 5. SOP PEMAKAIAN LAPANGAN (USAGE) --}}
            @if($activeSection === 'usage')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="px-2.5 py-1 bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-400 rounded-lg text-[11px] font-bold uppercase tracking-wider">SOP Modul 4</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                            Pemakaian Lapangan & Integrasi Tiket (Usage)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Pencatatan material yang telah terpasang permanen di rumah pelanggan atau pemeliharaan jaringan.
                        </p>
                    </div>
                    <a href="{{ route('inventory.usage.index') }}" class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 shrink-0">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span>Buka Menu Pemakaian</span>
                    </a>
                </div>

                <div class="space-y-4 text-xs">
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                        Di dsBilling, pemakaian material dapat tercatat melalui <strong>3 jalur terintegrasi</strong>:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1.5 bg-slate-50/50 dark:bg-slate-800/30">
                            <span class="font-bold text-slate-900 dark:text-slate-100 text-xs">1. Wizard Instalasi PSB</span>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">
                                Saat teknisi menyelesaikan pasang baru di portal teknisi, penginputan meteran kabel dropcore dan SN ONT langsung memotong custody teknisi secara otomatis.
                            </p>
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1.5 bg-slate-50/50 dark:bg-slate-800/30">
                            <span class="font-bold text-slate-900 dark:text-slate-100 text-xs">2. Penutupan Tiket Gangguan</span>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">
                                Pada perbaikan kabel putus atau pergantian modem rusak, teknisi memilih material yang dipakai di menu My Jobs. Terhubung langsung ke nomor tiket pelanggan.
                            </p>
                        </div>

                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1.5 bg-slate-50/50 dark:bg-slate-800/30">
                            <span class="font-bold text-slate-900 dark:text-slate-100 text-xs">3. Catat Pemakaian Manual</span>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">
                                Staf gudang dapat menginputkan manual di menu <code>Inventory ➔ Pemakaian</code> jika pekerjaan dilakukan tanpa tiket sistem atau perbaikan darurat ODP.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 6. SOP RETUR & UJI QC --}}
            @if($activeSection === 'return')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 rounded-lg text-[11px] font-bold uppercase tracking-wider">SOP Modul 5</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                            Retur Barang, Uji QC & Afkir
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Proses penerimaan barang kembali dari teknisi/pelanggan dan pengujian kelayakan.
                        </p>
                    </div>
                    <a href="{{ route('inventory.returns.index') }}" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 shrink-0">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span>Buka Menu Retur</span>
                    </a>
                </div>

                <div class="space-y-4 text-xs">
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                        3 Kategori Hasil Uji Kelayakan (QC Inspection):
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 space-y-2">
                            <span class="font-black text-emerald-700 dark:text-emerald-400 uppercase text-xs">1. Layak Pakai (Good)</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-[11px]">
                                Perangkat bekas bongkaran (*dismantle*) pelanggan isolir yang telah dites normal. Unit dibersihkan dan <strong>dikembalikan ke stok fisik gudang</strong> untuk dapat digunakan kembali.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 space-y-2">
                            <span class="font-black text-amber-700 dark:text-amber-400 uppercase text-xs">2. Rusak / Klaim Garansi</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-[11px]">
                                Perangkat rusak bawaan pabrik atau mati dalam masa garansi. Dicatat ke gudang karantina untuk diajukan RMA (*Return Merchandise Authorization*) ke vendor.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 space-y-2">
                            <span class="font-black text-rose-700 dark:text-rose-400 uppercase text-xs">3. Afkir / Rusak Total</span>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-[11px]">
                                Perangkat terbakar akibat petir atau terkena air yang tidak dapat diperbaiki. Dibuatkan berita acara pemusnahan/afkir dan dihapuskan dari nilai aset.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 7. SOP STOK OPNAME & AUDIT --}}
            @if($activeSection === 'opname')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 rounded-lg text-[11px] font-bold uppercase tracking-wider">SOP Modul 6</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                            Stok Opname & Penyesuaian Saldo (Opname)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Pencocokan fisik barang di gudang dengan catatan saldo sistem secara berkala (bulanan/triwulanan).
                        </p>
                    </div>
                    <a href="{{ route('inventory.opnames.index') }}" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 shrink-0">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span>Buka Stok Opname</span>
                    </a>
                </div>

                <div class="space-y-4 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-2">
                        <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Alur Pelaksanaan Opname:</h4>
                        <ol class="list-decimal list-inside space-y-1.5">
                            <li>Buka menu <strong>Stok Opname</strong>, buat sesi opname baru dan pilih gudang yang akan dihitung.</li>
                            <li>Cetak formulir penghitungan fisik tanpa melihat saldo sistem (*blind count* disarankan).</li>
                            <li>Hitung fisik di rak gudang, scan seluruh serial number perangkat aktif.</li>
                            <li>Masukkan hasil fisik ke sistem. Sistem akan otomatis menghitung <strong>Selisih (Difference)</strong>.</li>
                            <li>Jika terdapat selisih, cantumkan alasan (misal: kabel susut potong, konektor patah).</li>
                            <li>Setelah disetujui Manager/Admin, sistem akan menerbitkan mutasi penyesuaian (*Adjustment*) untuk menyamakan saldo buku dengan saldo fisik riil.</li>
                        </ol>
                    </div>
                </div>
            </div>
            @endif

            {{-- 8. FAQ & PEMECAHAN MASALAH (TROUBLESHOOT) --}}
            @if($activeSection === 'troubleshoot')
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 animate-fadeIn">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 rounded-lg text-[11px] font-bold uppercase tracking-wider">Troubleshooting</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-2">
                        Pertanyaan Umum & Solusi Kendala Lapangan
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Panduan menangani situasi tidak biasa dan kendala teknis dalam pencatatan inventaris.
                    </p>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-1.5">
                        <h4 class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-500">help</span>
                            <span>Bagaimana jika Serial Number (SN) ONT tidak muncul saat akan di-dispatch?</span>
                        </h4>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed pl-6">
                            Pastikan nomor SN tersebut sudah pernah di-input lewat menu <strong>Barang Masuk (Inbound)</strong> pada gudang yang sama dan statusnya masih <code>in_warehouse</code>. Jika unit tersebut pernah di-dispatch ke teknisi lain, cek statusnya di menu <strong>Custody</strong>.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-1.5">
                        <h4 class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-500">help</span>
                            <span>Kenapa saat klik Simpan Barang Masuk / Dispatch muncul pop-up instruksi absen?</span>
                        </h4>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed pl-6">
                            Sistem menerapkan keamanan SOP kehadiran (*EnsureWorkforceCheckedIn*). Staf gudang wajib melakukan <strong>Absen Masuk</strong> terlebih dahulu pada hari tersebut sebelum diizinkan mengeksekusi mutasi barang fisik gudang.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-1.5">
                        <h4 class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-500">help</span>
                            <span>Bagaimana jika teknisi lupa klik "Konfirmasi Terima" serah terima barang?</span>
                        </h4>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed pl-6">
                            Di menu <strong>Distribusi & Serah Terima</strong> pada tab <em>Riwayat & Konfirmasi</em>, Superadmin / Administrator memiliki wewenang untuk membantu mengeklik konfirmasi atas nama teknisi jika teknisi sedang berada di area tanpa sinyal internet.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-1.5">
                        <h4 class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-500">help</span>
                            <span>Apakah kabel dropcore harus diukur presisi per centimeter?</span>
                        </h4>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed pl-6">
                            Tidak perlu sampai centimeter, cukup catat pembulatan meter terdekat (misal: 75 meter). Potongan sisa yang terbuang (*waste margin*) wajar dapat disesuaikan pada sesi <strong>Stok Opname</strong> bulanan.
                        </p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
