<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\ValueObjects;

/**
 * SSOT: Normalized Incoming WhatsApp Message dari user (chat ke nomor gateway).
 */
final readonly class WaIncomingMessage
{
    public function __construct(
        public string  $fromPhone,             // 628xxxx
        public string  $text,                  // raw text pesan (sudah lower trimmed)
        public string  $rawText,               // raw original
        public string  $messageId,             // gateway message id (for ack)
        public string  $driverKey,             // fonnte / mpwa / baileys / qontak
        public ?string $quotedMessageId = null,
        public ?string $senderName = null,
        public bool    $isGroup = false,
        public ?string $groupId = null,
        public string  $receivedAtIso = '',
        public array   $rawPayload = [],
    ) {}

    public function commandWord(): string
    {
        $t = trim(mb_strtolower($this->text));
        $t = preg_replace('/\s+/', ' ', $t);
        $parts = explode(' ', $t, 2);
        return trim((string)$parts[0]);
    }

    public function commandArgs(): string
    {
        $t = trim(mb_strtolower($this->text));
        $t = preg_replace('/\s+/', ' ', $t);
        $parts = explode(' ', $t, 2);
        return trim((string)($parts[1] ?? ''));
    }
}
