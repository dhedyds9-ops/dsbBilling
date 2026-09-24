<?php

namespace App\Jobs\Inventory;

use App\Models\Inventory\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateQRCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $assetId,
        public int $size = 300
    ) {}

    public function handle(): void
    {
        $asset = Asset::find($this->assetId);
        if (!$asset) {
            return;
        }

        // Generate QR code with asset data
        $qrData = json_encode([
            'asset_code' => $asset->code,
            'serial' => $asset->serial_number,
            'type' => $asset->type,
            'timestamp' => now()->toIso8601String()
        ]);

        $filename = 'qrcodes/' . Str::slug($asset->code) . '.png';

        // Using a QR code library or external service
        $qrImage = $this->generateQRCodeImage($qrData, $this->size);

        if ($qrImage) {
            Storage::disk('public')->put($filename, $qrImage);
            $asset->qrcode_path = $filename;
            $asset->save();
        }
    }

    private function generateQRCodeImage(string $data, int $size): ?string
    {
        // Placeholder - actual implementation would generate real QR code
        // In production, use a library like simple-qrcode/simple-qrcode
        return null;
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('GenerateQRCodeJob failed', [
            'asset_id' => $this->assetId,
            'error' => $exception->getMessage()
        ]);
    }
}
