<?php

namespace App\Jobs\Inventory;

use App\Models\Inventory\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StockSynchronizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $warehouseId = null
    ) {}

    public function handle(): void
    {
        Log::info('StockSynchronizationJob started', [
            'warehouse_id' => $this->warehouseId
        ]);

        $query = InventoryItem::query();

        if ($this->warehouseId) {
            $query->where('warehouse_id', $this->warehouseId);
        }

        $items = $query->get();
        $syncedCount = 0;

        foreach ($items as $item) {
            try {
                $this->syncItem($item);
                $syncedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to sync item', [
                    'item_id' => $item->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('StockSynchronizationJob completed', [
            'warehouse_id' => $this->warehouseId,
            'synced_count' => $syncedCount
        ]);
    }

    private function syncItem(InventoryItem $item): void
    {
        // Sync with external inventory systems
        // This could be ERP, accounting software, etc.
        
        // Example: Sync to external monitoring system
        // Http::post('https://external-api.com/inventory/sync', [
        //     'sku' => $item->sku,
        //     'quantity' => $item->quantity,
        //     'warehouse' => $item->warehouse_id
        // ]);

        // Update last sync timestamp
        $item->last_synced_at = now();
        $item->save();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('StockSynchronizationJob failed', [
            'warehouse_id' => $this->warehouseId,
            'error' => $exception->getMessage()
        ]);
    }
}
