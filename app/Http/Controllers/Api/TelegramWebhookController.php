<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Telegram\TelegramService;
use App\Models\ACS\ACSDevice;
use App\Services\Adapters\Monitoring\GenieACSDriver;

class TelegramWebhookController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle(Request $request)
    {
        $data = $request->all();

        // Check if this is a message
        if (isset($data['message']['text'])) {
            $text = $data['message']['text'];
            $chatId = $data['message']['chat']['id'];

            if (str_starts_with($text, '/cek')) {
                $this->handleCheckCommand($text, $chatId);
            } elseif ($text === '/start') {
                $this->telegram->sendMessage("Halo! Saya adalah Bot dsBilling. Anda bisa mengecek status modem dengan perintah: `/cek <SerialNumber>`", $chatId);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleCheckCommand(string $text, string $chatId)
    {
        // format: /cek SN12345
        $parts = explode(' ', trim($text));
        if (count($parts) < 2) {
            $this->telegram->sendMessage("⚠️ Format salah.\nGunakan: `/cek <SerialNumber>`", $chatId);
            return;
        }

        $sn = $parts[1];
        $device = ACSDevice::where('serial_number', $sn)->first();

        if (!$device) {
            $this->telegram->sendMessage("❌ Perangkat dengan SN `{$sn}` tidak ditemukan di sistem.", $chatId);
            return;
        }

        // Coba tarik status terbaru dari GenieACS
        $acs = new GenieACSDriver();
        try {
            $signal = $acs->getDeviceSignal($device->uuid);
            
            $statusText = $signal['online'] ? '🟢 ONLINE' : '🔴 OFFLINE';
            $rxPower = $signal['rx_power_dbm'] ?? '-';
            
            $msg = "📊 *Status Perangkat*\n\n";
            $msg .= "SN: `{$sn}`\n";
            $msg .= "IP: `{$device->ip_address}`\n";
            $msg .= "Status: {$statusText}\n";
            $msg .= "RX Power: `{$rxPower} dBm`\n";
            $msg .= "Model: {$device->model}\n";
            
            $this->telegram->sendMessage($msg, $chatId);
            
        } catch (\Exception $e) {
            $this->telegram->sendMessage("⚠️ Gagal menghubungi server GenieACS untuk SN `{$sn}`. Server error atau timeout.", $chatId);
        }
    }
}
