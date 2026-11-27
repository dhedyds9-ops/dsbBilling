<?php

namespace App\Services\Support;

class CustomerFieldSanitizer
{
    /**
     * Membersihkan dan memvalidasi nomor telepon / WhatsApp.
     * Mempertahankan leading zero (misal '081234567890') atau format internasional (+62...).
     * Mengembalikan null jika kosong, hanya '-' atau tidak valid (kurang dari 8 digit).
     */
    public static function cleanPhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim($phone);
        if ($trimmed === '' || $trimmed === '-' || $trimmed === '--' || strcasecmp($trimmed, 'null') === 0) {
            return null;
        }

        // Simpan tanda '+' jika ada di awal
        $hasLeadingPlus = str_starts_with($trimmed, '+');
        $digits = preg_replace('/[^0-9]/', '', $trimmed);

        if (strlen($digits) < 8) {
            return null;
        }

        // Normalisasi format: jika berawalan 628 -> 08 (atau pertahankan leading plus)
        if ($hasLeadingPlus) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '62') && strlen($digits) >= 10) {
            return '0' . substr($digits, 2);
        }

        if (!str_starts_with($digits, '0') && strlen($digits) >= 9) {
            return '0' . $digits;
        }

        return $digits;
    }

    /**
     * Ekstrak nomor telepon dari teks bebas (deskripsi/notes/alamat).
     */
    public static function extractPhone(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        // Pola dengan label (Telp, Phone, WA, WhatsApp, HP, Kontak)
        if (preg_match('/(?:Telp|Phone|WA|WhatsApp|Kontak|No\.?\s*HP|HP)\s*[:=\s]*([0-9+\s\-]{8,20})(?:\s*\||\n|\r|$)/i', $text, $m)) {
            $cleaned = self::cleanPhone($m[1]);
            if ($cleaned) {
                return $cleaned;
            }
        }

        // Pola nomor telepon Indonesia standalone (+62, 62, atau 08)
        if (preg_match('/(?:\b|\D)((?:\+62|62|08)[0-9\s\-]{8,16})(?:\b|\D)/', $text, $m)) {
            $cleaned = self::cleanPhone($m[1]);
            if ($cleaned) {
                return $cleaned;
            }
        }

        return null;
    }

    /**
     * Ekstrak koordinat Latitude & Longitude dari teks bebas.
     * Mengembalikan array ['latitude' => float|null, 'longitude' => float|null].
     */
    public static function extractCoordinates(?string $text): array
    {
        if (empty($text)) {
            return ['latitude' => null, 'longitude' => null];
        }

        // Format umum: Titik: -6.xxx, 106.xxx atau GPS: -6.xxx, 106.xxx atau Koordinat: -6.xxx, 106.xxx
        // Atau kurung siku [ -6.xxx, 106.xxx ] atau pasangan desimal eksplisit (>= 4 desimal)
        if (preg_match('/(?:GPS|Koordinat|Titik(?:\s*koordinat)?|Location)[:\s\[]+([+-]?\d{1,2}\.\d+)[,\s]+([+-]?\d{1,3}\.\d+)[\]]?/i', $text, $m) ||
            preg_match('/\[\s*([+-]?\d{1,2}\.\d+)[,\s]+([+-]?\d{1,3}\.\d+)\s*\]/', $text, $m) ||
            preg_match('/(?:\b)([+-]?\d{1,2}\.\d{4,})[,\s]+([+-]?\d{1,3}\.\d{4,})(?:\b)/', $text, $m)) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
            // Validasi range koordinat Indonesia/bumi (-90 <= lat <= 90, -180 <= lng <= 180)
            if ($lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0) {
                if (abs($lat) > 0.0001 || abs($lng) > 0.0001) {
                    return ['latitude' => $lat, 'longitude' => $lng];
                }
            }
        }

        return ['latitude' => null, 'longitude' => null];
    }

    /**
     * Membersihkan teks alamat dari kontaminasi nomor telepon, koordinat GPS, dan tag metadata.
     */
    public static function cleanAddress(?string $address): ?string
    {
        if ($address === null) {
            return null;
        }

        $clean = trim($address);
        if ($clean === '' || $clean === '-' || $clean === '--' || strcasecmp($clean, 'null') === 0) {
            return null;
        }

        // 1. Bersihkan tag bracket Calon Pelanggan / Objek Jaringan jika seluruh teks tercopy
        if (preg_match('/\[(?:Calon Pelanggan|Objek Jaringan):.*?Alamat:\s*(.+?)(?:\||\])/is', $clean, $m)) {
            $clean = $m[1];
        } else {
            $clean = preg_replace('/\[(?:Calon Pelanggan|Objek Jaringan):.*?\]/is', '', $clean);
        }

        // 2. Bersihkan pola koordinat dalam kurung siku, misal "[Titik: -6.xxx, 106.xxx]" atau "[Koordinat: ...]"
        $clean = preg_replace('/\[(?:Titik|Koordinat|GPS):[^\]]+\]/i', '', $clean);

        // 3. Bersihkan pasangan koordinat desimal numeric (minimal 3 digit desimal)
        $clean = preg_replace('/(?:GPS|Koordinat|Titik(?:\s*koordinat)?|Titik|Location)?[:\s]*[+-]?\d{1,2}\.\d{3,}[,\s]+[+-]?\d{1,3}\.\d{3,}/i', '', $clean);

        // 4. Bersihkan nomor telepon dengan label atau format nomor HP Indonesia (minimal 8 digit)
        $clean = preg_replace('/(?:Telp|Phone|WA|WhatsApp|Kontak|No\.?\s*HP|HP)\s*[:=\s]*[0-9+\s\-]{8,20}/i', '', $clean);
        $clean = preg_replace('/(?:\b|\D)(?:\+62|62|08)[0-9]{8,13}(?:\b|\D)/', ' ', $clean);

        // 5. Bersihkan kata kunci koordinat yang berdiri sendiri (JANGAN hapus kata 'titik' biasa seperti 'titik kumpul')
        $clean = preg_replace('/\b(?:titik\s+koordinat|titik\s+lokasi|titik\s+gps|titik\s+acuan\s+gps|koordinat\s+gps)\b/i', '', $clean);
        $clean = preg_replace('/\b(?:titik|koordinat|gps)\s*[:=]\s*/i', '', $clean);

        // 6. Bersihkan prefix label alamat jika tersisa di awal
        $clean = preg_replace('/^(?:Alamat|Address|Lokasi)\s*[:=-]\s*/i', '', $clean);

        // 7. Bersihkan delimiter dangling dan spasi berlebih
        $clean = preg_replace('/\s*\|\s*/', ' - ', $clean);
        $clean = preg_replace('/\s+-\s+/', ' - ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean, " \t\n\r\0\x0B|,-:;");

        if ($clean === '' || $clean === '-' || $clean === '--') {
            return null;
        }

        return $clean;
    }

    /**
     * Disentangles input fields to ensure:
     * - WhatsApp/Phone only contains clean phone (never '-')
     * - Address only contains pure address text
     * - Coordinates are separated into latitude and longitude
     */
    public static function sanitize(
        ?string $name,
        ?string $phone,
        ?string $address,
        ?string $notesOrDesc = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): array {
        // 1. Ekstrak nama bersih
        $cleanName = trim((string) $name);
        if (preg_match('/\[Calon Pelanggan:\s*(.+?)\s*\|/i', (string) $notesOrDesc, $mn)) {
            if ($cleanName === '' || $cleanName === 'Pelanggan Baru' || $cleanName === 'Pelanggan') {
                $cleanName = trim($mn[1]);
            }
        }
        if ($cleanName === '' || $cleanName === '-') {
            $cleanName = 'Pelanggan Baru';
        }

        // 2. Ekstrak telepon/WhatsApp bersih
        $cleanPhone = self::cleanPhone($phone);
        if (!$cleanPhone) {
            $cleanPhone = self::extractPhone($address) ?: self::extractPhone($notesOrDesc);
        }

        // 3. Ekstrak koordinat jika belum tersedia
        $lat = $latitude;
        $lng = $longitude;
        if ($lat === null || $lng === null) {
            $coords = self::extractCoordinates($address);
            if ($coords['latitude'] === null && !empty($notesOrDesc)) {
                $coords = self::extractCoordinates($notesOrDesc);
            }
            if ($coords['latitude'] !== null) {
                $lat = $coords['latitude'];
                $lng = $coords['longitude'];
            }
        }

        // 4. Bersihkan alamat
        $cleanAddress = self::cleanAddress($address);
        if (!$cleanAddress && !empty($notesOrDesc)) {
            // Coba ambil bagian alamat dari notes jika ada label Alamat:
            if (preg_match('/(?:Alamat|Address|Lokasi):\s*([^|\n\r]+?)(?:\s*\||\n|\r|$)/i', $notesOrDesc, $ma)) {
                $cleanAddress = self::cleanAddress($ma[1]);
            }
        }

        // 5. Cek ambiguitas (lebih dari 1 nomor telepon kandidat berbeda tanpa penentu jelas)
        $isAmbiguous = false;
        if (empty($phone)) {
            preg_match_all('/(?:\b|\D)((?:\+62|62|08)[0-9]{8,13})(?:\b|\D)/', (string) $address . ' ' . (string) $notesOrDesc, $phoneMatches);
            if (!empty($phoneMatches[1])) {
                $candidates = array_unique(array_filter(array_map([self::class, 'cleanPhone'], $phoneMatches[1])));
                if (count($candidates) > 1) {
                    $isAmbiguous = true;
                }
            }
        }

        return [
            'name' => $cleanName,
            'phone' => $cleanPhone,
            'whatsapp' => $cleanPhone,
            'address' => $cleanAddress ?: 'Alamat belum tercatat',
            'latitude' => $lat,
            'longitude' => $lng,
            'is_ambiguous' => $isAmbiguous,
        ];
    }
}
