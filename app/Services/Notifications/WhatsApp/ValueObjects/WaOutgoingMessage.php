<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\ValueObjects;

/**
 * SSOT: Normalized Outgoing WhatsApp Message DTO.
 *
 * Tipe: text | image | document | audio | video | template | button | list
 */
final readonly class WaOutgoingMessage
{
    public string $toPhone;

    public function __construct(
        string $toPhone,               // format: 628123456789 (tanpa 0 / +)
        public string  $type = 'text',         // text | image | document | audio | video | template | button | list
        public string  $text = '',             // message content / caption for media
        public string  $mediaUrl = '',         // image/video/document/audio url (public)
        public string  $fileName = '',         // untuk document
        public string  $templateName = '',     // untuk Qontak / official template
        public array   $templateComponents = [],// untuk template parameters
        public array   $buttons = [],          // [['id' => 'cmd_1', 'title' => 'Cek Tagihan'], ...]
        public array   $listSections = [],     // [['title' => 'Menu', 'rows' => [['id','title','description']]]]
        public string  $category = 'general',  // billing | reminder | info | marketing | support | otp
        public bool    $priorityHigh = false,  // true = bypass rate limit (otp / critical)
        public ?string $idempotencyKey = null, // prevent double-send, hash(to+type+category+message)
    ) {
        $this->toPhone = self::normalizePhone($toPhone);
    }

    public static function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        // Pertahankan format ID Grup WhatsApp (contoh: 1203630xxx@g.us, grup fonnte, dll)
        if (str_contains($phone, '@g.us') || str_contains($phone, '@broadcast') || str_contains($phone, '-')) {
            return $phone;
        }

        $p = preg_replace('/[^0-9]/', '', $phone);
        // Jika ID numerik panjang (umumnya ID grup 15+ digit tanpa awalan 0/62)
        if (strlen($p) >= 15 && !str_starts_with($p, '62') && !str_starts_with($p, '0') && !str_starts_with($p, '8')) {
            return $p;
        }

        if (str_starts_with($p, '62')) return $p;
        if (str_starts_with($p, '0')) return '62' . substr($p, 1);
        if (str_starts_with($p, '8')) return '62' . $p;
        return $p;
    }

    public function isPhoneValid(): bool
    {
        // Target adalah grup WhatsApp
        if (str_ends_with($this->toPhone, '@g.us') || str_ends_with($this->toPhone, '@broadcast') || str_contains($this->toPhone, '-')) {
            return true;
        }
        if (preg_match('/^[0-9]{15,35}$/', $this->toPhone)) {
            return true;
        }

        // Target adalah nomor telepon personal Indonesia
        return (bool)preg_match('/^628[0-9]{8,14}$/', $this->toPhone);
    }
}
