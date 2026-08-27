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
    public function __construct(
        public string  $toPhone,               // format: 628123456789 (tanpa 0 / +)
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
        $this->toPhone = self::normalizePhone($this->toPhone);
    }

    public static function normalizePhone(string $phone): string
    {
        $p = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($p, '62')) return $p;
        if (str_starts_with($p, '0')) return '62' . substr($p, 1);
        if (str_starts_with($p, '8')) return '62' . $p;
        return $p;
    }

    public function isPhoneValid(): bool
    {
        return (bool)preg_match('/^628[0-9]{8,14}$/', $this->toPhone);
    }
}
