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

class GenerateBarcodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $assetId,
        public string $barcodeType = 'CODE128'
    ) {}

    public function handle(): void
    {
        $asset = Asset::find($this->assetId);
        if (!$asset) {
            return;
        }

        // Generate barcode image
        $barcodeData = $asset->code;
        $filename = 'barcodes/' . Str::slug($asset->code) . '.png';

        // Using a barcode library or external service
        // This is a placeholder - actual implementation would use a library like picqer/php-barcode
        $barcodeImage = $this->generateBarcodeImage($barcodeData, $this->barcodeType);

        if ($barcodeImage) {
            Storage::disk('public')->put($filename, $barcodeImage);
            $asset->barcode_path = $filename;
            $asset->save();
        }
    }

    private function generateBarcodeImage(string $data, string $type): ?string
    {
        // Placeholder - actual implementation would generate real barcode
        // In production, use a library like picqer/php-barcode-generator
        return null;
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('GenerateBarcodeJob failed', [
            'asset_id' => $this->assetId,
            'error' => $exception->getMessage()
        ]);
    }
}
