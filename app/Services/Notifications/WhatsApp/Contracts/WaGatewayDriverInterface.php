<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\Contracts;

use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaIncomingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaSendResult;

/**
 * SSOT: Unified WhatsApp Gateway Driver Interface.
 *
 * Strategy Pattern: Fonnte, MPWA OneSender, Baileys REST, Mekari Qontak.
 */
interface WaGatewayDriverInterface
{
    /**
     * Unique key: fonnte | mpwa | baileys | qontak
     */
    public static function driverKey(): string;

    /**
     * Display name for UI.
     */
    public static function driverLabel(): string;

    /**
     * Inject config dari settings service.
     */
    public function withConfig(array $config): self;

    /**
     * Send single message (text / image / document / button / template).
     */
    public function sendMessage(WaOutgoingMessage $msg): WaSendResult;

    /**
     * Parse incoming webhook payload menjadi WaIncomingMessage DTO.
     * Return null jika payload bukan chat dari user (misal system event / status update).
     */
    public function parseWebhook(string $rawBody, array $headers): ?WaIncomingMessage;

    /**
     * Cek health driver: ping ke API endpoint return latency ms.
     *
     * @return array{ok: bool, latency_ms: float, message: string}
     */
    public function health(): array;
}
