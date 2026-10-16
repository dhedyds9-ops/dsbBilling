<x-layouts.public-legal title="Syarat & Ketentuan Layanan">
    <div class="space-y-6">
        <section>
            <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-cyan-400">1.</span> Ketentuan Umum
            </h2>
            <p>
                Selamat datang di situs resmi <strong>{{ $companyName }}</strong>. Dengan mengakses situs web kami, mendaftar, atau menggunakan layanan internet (PPPoE, Hotspot, Voucher, atau layanan terkait lainnya) yang kami sediakan, Anda menyatakan telah membaca, memahami, dan menyetujui untuk terikat oleh Syarat dan Ketentuan ini.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-cyan-400">2.</span> Pendaftaran & Aktivasi Layanan
            </h2>
            <ul class="list-disc pl-5 space-y-2">
                <li>Calon pelanggan wajib memberikan informasi identitas yang benar, akurat, dan dapat diverifikasi (Nama Lengkap, Nomor WhatsApp/Telepon aktif, serta Alamat Pemasangan yang jelas).</li>
                <li>Pemasangan instalasi fisik kabel fiber optic dan perangkat modem/ONT akan dijadwalkan oleh teknisi kami setelah konfirmasi ketersediaan jalur jaringan (Optical Distribution Point / ODP).</li>
                <li>Perangkat modem/ONT yang dipinjamkan selama masa berlangganan merupakan aset milik penyedia layanan dan wajib dijaga dengan baik oleh pelanggan.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-cyan-400">3.</span> Penagihan & Pembayaran
            </h2>
            <ul class="list-disc pl-5 space-y-2">
                <li>Tagihan biaya berlangganan diterbitkan secara berkala setiap bulan sesuai dengan paket layanan yang dipilih oleh pelanggan.</li>
                <li>Pelanggan dapat melakukan pembayaran melalui berbagai kanal pembayaran resmi yang kami sediakan melalui mitra Payment Gateway resmi berizin Bank Indonesia (antara lain: QRIS, Transfer Bank / Virtual Account BCA, Mandiri, BNI, BRI, Permata, E-Wallet, dan Gerai Retail).</li>
                <li>Pelanggan bertanggung jawab untuk melunasi tagihan sebelum tanggal jatuh tempo yang tertera pada faktur/invoice.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-cyan-400">4.</span> Penangguhan & Pemutusan Layanan (Isolir)
            </h2>
            <ul class="list-disc pl-5 space-y-2">
                <li>Apabila pembayaran tagihan belum diterima hingga melewati tanggal jatuh tempo, sistem akan secara otomatis melakukan penangguhan sementara (isolir) terhadap akses internet pelanggan.</li>
                <li>Pelanggan yang terisolir tetap dapat mengakses halaman pembayaran resmi untuk melakukan pelunasan tagihan. Layanan akan kembali aktif secara otomatis (real-time) segera setelah pembayaran berhasil diverifikasi oleh sistem.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-cyan-400">5.</span> Penggunaan Layanan yang Wajar
            </h2>
            <p>
                Pelanggan dilarang keras menggunakan jaringan kami untuk aktivitas yang melanggar hukum Republik Indonesia, termasuk namun tidak terbatas pada: penyebaran malware/virus, peretasan (hacking/DDoS), penipuan online, penyebaran materi terlarang, atau memperjualbelikan kembali bandwidth tanpa izin tertulis dari manajemen penyedia layanan.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-cyan-400">6.</span> Batasan Tanggung Jawab (Force Majeure)
            </h2>
            <p>
                Penyedia layanan tidak bertanggung jawab atas gangguan atau penurunan kualitas layanan yang diakibatkan oleh kejadian di luar kendali wajar (Force Majeure), seperti bencana alam, kebakaran, pemadaman listrik massal dari PLN, pemutusan kabel serat optik utama (backbone/kabel laut) oleh pihak ketiga, atau gangguan infrastruktur nasional. Namun demikian, tim teknis kami akan selalu berupaya semaksimal mungkin untuk memulihkan koneksi dalam waktu sesingkat-singkatnya.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-cyan-400">7.</span> Kontak Layanan Pelanggan
            </h2>
            <p>
                Jika Anda memiliki pertanyaan mengenai Syarat & Ketentuan ini, silakan hubungi pusat layanan kami melalui email <strong>{{ $companyEmail }}</strong> atau WhatsApp <strong>{{ $companyPhone }}</strong>.
            </p>
        </section>
    </div>
</x-layouts.public-legal>
