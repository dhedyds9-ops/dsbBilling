<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp;

/**
 * SSOT: WhatsApp Template Engine.
 *
 * Semua template billing harus di-render dari sini TIDAK dari inline string di berbagai listener.
 * Prinsip: Single Source of Truth untuk teks WA pelanggan ISP Indonesia.
 *
 * Output = STRING (plain text WhatsApp, support emote dan new line).
 *
 * Placeholder convention: array key-value pas ke dalam each function.
 */
final class WaTemplateEngine
{
    private const BRAND = 'dsBilling Enterprise';
    private const SUPPORT_WA = '08xx-xxxx-xxxx (CS)';

    public function __construct(
        private readonly ?string $brandName = null,
        private readonly ?string $supportWa = null,
    ) {}

    /** 1. Notifikasi TAGIHAN BARU dibuat (hari H = tanggal generate) */
    public function invoiceCreated(array $d): string
    {
        $invNo = $this->e($d['invoice_number'] ?? '-');
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $periode = $this->e($d['period'] ?? date('F Y'));
        $total = $this->rp((float)($d['total_amount'] ?? 0));
        $due = $this->e($d['due_date'] ?? '-');
        $paket = $this->e($d['package_name'] ?? '');
        return "📩 *NOTIFIKASI TAGIHAN BARU*\n\n"
            . "Yth. *{$nama}*\n\n"
            . "Tagihan Internet Anda untuk periode *{$periode}* sudah tersedia.\n\n"
            . "📄 No. Invoice : *{$invNo}*\n"
            . ($paket ? "📦 Paket      : {$paket}\n" : "")
            . "💵 Tagihan    : *{$total}*\n"
            . "⏳ Jatuh Tempo: *{$due}*\n\n"
            . "⚠️ Segera lakukan pembayaran sebelum jatuh tempo untuk menghindari pembatasan akses.\n\n"
            . "Pembayaran:\n"
            . "  • Transfer Bank: " . ($this->e($d['bank_accounts_text'] ?? 'Lihat di portal') . "\n")
            . "  • Link Bayar   : " . ($this->e($d['payment_link'] ?? 'Lihat portal pelanggan') . "\n\n")
            . "Ketik *\"bayar\"* tanpa tanda kutip untuk link pembayaran cepat.\n\n"
            . "Terima kasih 🙏\n— " . $this->brand();
    }

    /** 2. Reminder tagihan H-3 / H-1 sebelum jatuh tempo */
    public function invoiceReminder(array $d, string $stage = 'h-1'): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $invNo = $this->e($d['invoice_number'] ?? '-');
        $total = $this->rp((float)($d['total_amount'] ?? 0));
        $due = $this->e($d['due_date'] ?? '-');
        $sisa = (int)($d['days_remaining'] ?? 0);

        if ($stage === 'h+1') {
            return "🔴 *PEMBERITAHUAN: TAGIHAN SUDAH LEWAT JATUH TEMPO*\n\n"
                . "Yth. *{$nama}*\n\n"
                . "Tagihan Anda (No. Invoice: *{$invNo}*) sudah memasuki *HARI KE-1 SETELAH JATUH TEMPO*.\n\n"
                . "💵 Tagihan  : *{$total}*\n"
                . "📅 Jatuh Tempo: {$due} (1 hari lalu)\n\n"
                . "⚠️ *AKSI DIBUTUHKAN:* Jika tidak segera dibayar hari ini, akses Internet Anda akan otomatis dibatasi (isolir) pada pukul 18:00 WIB.\n\n"
                . "Ketik *\"bayar\"* untuk link pembayaran cepat.\n\n"
                . "Mohon maaf atas ketidaknyamanannya.\nTerima kasih.\n— " . $this->brand();
        }

        if ($stage === 'h-3') {
            return "🟡 *REMINDER TAGIHAN H-3 JATUH TEMPO*\n\n"
                . "Yth. *{$nama}*\n\n"
                . "Tagihan No. *{$invNo}* akan jatuh tempo dalam *{$sisa} hari* ({$due}).\n\n"
                . "💵 Total: *{$total}*\n\n"
                . "Segera lakukan pembayaran untuk kenyamanan layanan Anda.\n"
                . "Ketik *\"bayar\"* untuk link pembayaran.\n\n"
                . "Terima kasih.\n— " . $this->brand();
        }

        // Default: H-1
        return "🔴 *REMINDER TAGIHAN H-1 JATUH TEMPO*\n\n"
            . "Yth. *{$nama}*\n\n"
            . "⏰ Tagihan No. *{$invNo}* akan jatuh tempo *BESOK ({$due})*.\n\n"
            . "💵 Total: *{$total}*\n\n"
            . "⚠️ Lewati batas waktu besok = akses Internet OTOMATIS dibatasi mulai jam 00:00 WIB.\n\n"
            . "Ketik *\"bayar\"* untuk bayar sekarang via QRIS/VA/E-Wallet dalam 1 tap.\n\n"
            . "Terima kasih 🙏\n— " . $this->brand();
    }

    /** 3. Konfirmasi PEMBAYARAN SUKSES (sudah diimplementasi di InvoicePaidListener — jaga konsistensi) */
    public function paymentSuccess(array $d): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $invNo = $this->e($d['invoice_number'] ?? '-');
        $paidAmount = $this->rp((float)($d['paid_amount'] ?? 0));
        $tanggal = $this->e($d['paid_at'] ?? now()->format('d M Y H:i'));
        $resi = $this->e($d['payment_receipt'] ?? '-');
        return "✅ *PEMBAYARAN DITERIMA*\n\n"
            . "Yth. *{$nama}*\n\n"
            . "Terima kasih, pembayaran Anda sudah diverifikasi otomatis ✅\n\n"
            . "🧾 No. Resi  : *{$resi}*\n"
            . "📄 Invoice    : *{$invNo}*\n"
            . "💵 Dibayar    : *{$paidAmount}*\n"
            . "🕒 Waktu      : {$tanggal}\n\n"
            . "🚀 Layanan Anda sedang dalam proses *reaktivasi otomatis* (maks. 3 menit).\n"
            . "Jika masih belum bisa akses, silakan restart ONU/Modem Anda.\n\n"
            . "Simpan resi ini sebagai bukti pembayaran resmi.\n\n"
            . "Terima kasih atas kepercayaan Anda 🙏\n— " . $this->brand();
    }

    /** 4. Notifikasi ISOLIR karena UNPAID */
    public function serviceSuspended(array $d): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $invNo = $this->e($d['invoice_number'] ?? '-');
        $sisa = $this->rp((float)($d['outstanding_amount'] ?? 0));
        return "⛔ *LAYANAN DIBATASI (ISOLIR)*\n\n"
            . "Yth. *{$nama}*\n\n"
            . "Kami informasikan akses Internet Anda saat ini *DIBATASI (128kbps)* karena tagihan belum lunas.\n\n"
            . "📄 Invoice : *{$invNo}*\n"
            . "💵 Tunggakan: *{$sisa}*\n\n"
            . "⏱️ Setelah pembayaran diverifikasi (maks. 3 menit), layanan kembali NORMAL OTOMATIS.\n\n"
            . "Ketik *\"bayar\"* untuk link pembayaran sekarang.\n"
            . "Untuk bantuan ketik *\"bantuan\"* atau hubungi CS: {$this->wa()}.\n\n"
            . "Mohon maaf atas ketidaknyamanannya.\n— " . $this->brand();
    }

    /** 5. Notifikasi AKTIVASI ULANG setelah bayar */
    public function serviceReactivated(array $d): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $package = $this->e($d['package_name'] ?? 'Layanan');
        $speed = $this->e($d['bandwidth_text'] ?? '');
        return "🎯 *LAYANAN BERHASIL DI-AKTIVASI KEMBALI*\n\n"
            . "Yth. *{$nama}*\n\n"
            . "Selamat! Layanan Anda *SUDAH KEMBALI NORMAL*.✅\n\n"
            . "📦 Paket    : {$package}\n"
            . ($speed ? "📶 Bandwidth: {$speed}\n" : "")
            . "🕒 Diverifikasi pada: " . now()->format('d M Y H:i') . "\n\n"
            . "Jika masih lambat, silakan restart ONU/Modem (cabut colokan listrik 10 detik, pasang kembali).\n\n"
            . "Happy browsing! 🚀\n— " . $this->brand();
    }

    /** 6. Broadcast Info GANGGUAN / MAINTENANCE */
    public function networkAnnouncement(array $d, string $type = 'maintenance'): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan Yth.');
        $lokasi = $this->e($d['affected_area'] ?? 'Area tertentu');
        $waktu = $this->e($d['schedule_text'] ?? '');
        $estimasi = $this->e($d['eta_text'] ?? '-');
        $deskripsi = $this->e($d['description'] ?? '-');
        if ($type === 'outage') {
            return "⚠️ *INFO GANGGUAN TERDETEKSI*\n\n"
                . "Yth. *{$nama}*\n\n"
                . "Sistem otomatis mendeteksi gangguan pada layanan Anda.\n\n"
                . "📍 Area terdampak: {$lokasi}\n"
                . "🕒 Waktu mulai   : {$waktu}\n"
                . "🛠️  Deskripsi     : {$deskripsi}\n"
                . "⏳ Estimasi selesai: *{$estimasi}*\n\n"
                . "Tim teknisi kami sudah sedang bekerja untuk pemulihan segera.\n\n"
                . "Lapor gangguan lain ketik *\"gangguan\"* (buat tiket otomatis).\n"
                . "Status koneksi Anda: ketik *\"status\"*.\n\n"
                . "Mohon doa dan kesabarannya.\n— " . $this->brand();
        }
        return "🔧 *JADWAL MAINTENANCE BERKALA*\n\n"
            . "Yth. *{$nama}*\n\n"
            . "Kami informasikan akan ada kegiatan maintenance / peningkatan jaringan:\n\n"
            . "📍 Area        : {$lokasi}\n"
            . "🕒 Waktu       : {$waktu}\n"
            . "📋 Tujuan      : {$deskripsi}\n"
            . "⏳ Durasi      : *{$estimasi}*\n\n"
            . "Selama maintenance, layanan Anda akan terputus / tidak stabil (flapping).\n"
            . "Kami upayakan selesai sesuai estimasi.\n\n"
            . "Mohon maaf atas ketidaknyamanannya.\n— " . $this->brand();
    }

    /** --- TEMPLATES BOT INTERAKTIF (Command Response) --- */

    public function botResponseTagihan(array $d): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $items = (array)($d['invoices'] ?? []);
        if (count($items) === 0) {
            return "🎉 *{$nama}*\n\nTIDAK ADA TAGIHAN AKTIF. Semua tagihan Anda dalam status LUNAS.\n\nUntuk paket aktif: ketik *\"status\"*.\n— " . $this->brand();
        }
        $total = 0;
        $rows = [];
        foreach ($items as $inv) {
            $amount = (float)($inv['total_amount'] ?? 0) - (float)($inv['paid_amount'] ?? 0);
            $total += $amount;
            $rows[] = "📄 No. *" . ($this->e($inv['invoice_number'] ?? '')) . "*\n"
                    . "   📅 Jth.Tempo: " . ($this->e($inv['due_date'] ?? '-')) . "\n"
                    . "   💵 Sisa: *" . $this->rp($amount) . "*";
        }
        return "📋 *DAFTAR TAGIHAN AKTIF* - {$nama}\n\n"
            . implode("\n\n", $rows)
            . "\n\n━━━━━━━━━━━━━━━━\n💵 *TOTAL TAGIHAN: {$this->rp($total)}*\n━━━━━━━━━━━━━━━━\n\n"
            . "Ketik *\"bayar\"* untuk langsung ke link pembayaran semua tagihan.\n"
            . "Ketik *\"status\"* untuk cek status koneksi Internet Anda.\n\n"
            . "Terima kasih 🙏\n— " . $this->brand();
    }

    public function botResponseStatus(array $d): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $status = $this->e($d['service_status'] ?? 'unknown');
        $statusBadge = match (strtolower($status)) {
            'active', 'online', 'connected' => '✅ *TERKONEKSI*',
            'suspended', 'isolir', 'suspend' => '⛔ *TERBATASI / ISOLIR*',
            'disconnected', 'offline' => '⚠️ *OFFLINE*',
            default => 'ℹ️ *' . strtoupper($status) . '*',
        };
        $paket = $this->e($d['package_name'] ?? '-');
        $bw = $this->e($d['bandwidth'] ?? '-');
        $ip = $this->e($d['ip_address'] ?? '-');
        $lastSeen = $this->e($d['last_online_at'] ?? '-');
        $pppoe = $this->e($d['pppoe_username'] ?? '-');

        return "🛜 *STATUS LAYANAN* - {$nama}\n\n"
            . "Status : {$statusBadge}\n\n"
            . "📦 Paket    : {$paket}\n"
            . "📶 Bandwidth: {$bw}\n"
            . "👤 PPPoE    : {$pppoe}\n"
            . "🌐 IP Publik: {$ip}\n"
            . "🕒 Terakhir online : {$lastSeen}\n\n"
            . "Jika status masih *OFFLINE* 10+ menit, ketik *\"gangguan\"* untuk lapor tiket otomatis.\n\n"
            . "— " . $this->brand();
    }

    public function botResponseBayar(array $d): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $total = $this->rp((float)($d['total_outstanding'] ?? 0));
        $link = $this->e($d['payment_link'] ?? '#');
        $bankText = $this->e($d['bank_accounts_text'] ?? 'Lihat di portal pelanggan');

        return "💳 *LINK PEMBAYARAN CEPAT* - {$nama}\n\n"
            . "Total yang harus dibayar: *{$total}*\n\n"
            . "━━━━━━━━━━━━━━━\n"
            . "👉 BAYAR SEKARANG:\n"
            . "   🔗 {$link}\n"
            . "━━━━━━━━━━━━━━━\n\n"
            . "Klik link di atas untuk bayar via:\n"
            . "   • QRIS (scan langsung dari HP)\n"
            . "   • Virtual Account semua Bank (BCA/BRI/BNI/Mandiri)\n"
            . "   • E-Wallet: GoPay/DANA/OVO/ShopeePay\n"
            . "   • Retail: Indomaret/Alfamart\n\n"
            . "💼 Transfer Manual:\n{$bankText}\n\n"
            . "Setelah transfer, verifikasi OTOMATIS maks. 1 menit.\n"
            . "Jika >10 menit belum terverifikasi, kirim bukti transfer ke CS: {$this->wa()}.\n\n"
            . "Terima kasih 🙏\n— " . $this->brand();
    }

    public function botResponseGangguan(array $d): string
    {
        $nama = $this->e($d['customer_name'] ?? 'Pelanggan');
        $tiket = $this->e($d['ticket_number'] ?? '-');
        $judul = $this->e($d['ticket_title'] ?? 'Gangguan Layanan');
        $kategori = $this->e($d['category'] ?? 'Gangguan Internet');
        $estimasi = $this->e($d['eta_response_text'] ?? 'Maks. 2 jam');

        return "🎫 *TIKET PENGADUAN DIBUAT*\n\n"
            . "Terima kasih *{$nama}*, laporan Anda sudah kami terima.\n\n"
            . "🧾 No. Tiket : *{$tiket}*\n"
            . "📋 Judul     : {$judul}\n"
            . "🏷️  Kategori  : {$kategori}\n"
            . "⏱️ Respon Maks: Teknisi akan menghubungi Anda dalam *{$estimasi}*\n\n"
            . "Untuk bantuan darurat, hubungi CS: {$this->wa()}.\n"
            . "Anda akan mendapat update status tiket via WhatsApp ini otomatis.\n\n"
            . "Mohon kesabarannya 🙏\n— " . $this->brand();
    }

    public function botHelp(): string
    {
        return "🤖 *BANTUAN BOT dsBilling*\n\n"
            . "Berikut perintah yang tersedia:\n\n"
            . "  🏷️  *tagihan*  → Daftar tagihan aktif\n"
            . "  🛜 *status*   → Status paket & koneksi\n"
            . "  💳 *bayar*    → Link pembayaran cepat\n"
            . "  🎫 *gangguan* → Buat tiket laporan gangguan\n"
            . "  🆘 *bantuan*  → Menu bantuan (menu ini)\n\n"
            . "Ketik perintah di atas tanpa tanda kutip (case-insensitive).\n\n"
            . "CS 24/7: {$this->wa()}\n— " . $this->brand();
    }

    public function botGreeting(?string $nama = null): string
    {
        $sapaan = $nama ? "Halo *{$this->e($nama)}*! 👋" : "Halo! 👋";
        return "{$sapaan}\nSelamat datang di *Bot Layanan " . $this->brand() . "*.\n\n"
            . "Saya bisa bantu Anda untuk:\n\n"
            . "  📋 Lihat daftar tagihan\n"
            . "  🛜 Cek status koneksi\n"
            . "  💳 Bayar via QRIS/VA/E-Wallet\n"
            . "  🎫 Lapor gangguan (buat tiket)\n\n"
            . "Ketik salah satu perintah: *tagihan* • *status* • *bayar* • *gangguan*\n\n"
            . "— " . $this->brand();
    }

    // ---------- helpers ----------
    private function rp(float $amount): string
    {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }
    private function e(mixed $v): string { if ($v === null) return ''; return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8', false); }
    private function brand(): string { return $this->brandName ?? self::BRAND; }
    private function wa(): string { return $this->supportWa ?? self::SUPPORT_WA; }
}
