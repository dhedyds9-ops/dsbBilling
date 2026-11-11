<?php

namespace App\Console\Commands\Inventory;

use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSerial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanDummyInventoryCommand extends Command
{
    protected $signature = 'inventory:clean-dummy 
                            {--delete-items : Hapus master data barang dummy seeder sepenuhnya dari katalog}
                            {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Bersihkan data dummy saldo awal inventaris dan master barang seeder agar bersih';

    public function handle(): int
    {
        $this->info('=== PEMBERSIHAN DATA DUMMY INVENTARIS & GUDANG ===');

        $deleteItems = $this->option('delete-items');
        $force = $this->option('force');

        $dummySkus = [
            'MAT-CLAMP-S',
            'MAT-DC-1C',
            'MAT-PC-SC-1.5M',
            'MAT-ADAPT-SC',
            'MAT-FC-SC',
            'MAT-ROSET-1C',
            'DEV-ONT-ZTE-F609',
            'DEV-ONT-HW-8245H',
        ];

        if (!$force) {
            $action = $deleteItems ? 'menghapus 8 Master Barang bawaan seeder dan menolkan stoknya' : 'menolkan seluruh saldo awal fisik bawaan seeder';
            if (!$this->confirm("Apakah Anda yakin ingin {$action}?")) {
                $this->warn('Pembersihan dibatalkan.');
                return Command::SUCCESS;
            }
        }

        DB::transaction(function () use ($dummySkus, $deleteItems) {
            $items = Item::withoutGlobalScopes()->whereIn('sku', $dummySkus)->get();

            $this->line("Ditemukan {$items->count()} master barang bawaan seeder.");

            foreach ($items as $item) {
                // 1. Bersihkan saldo fisik di inventory_stocks
                $affectedStocks = InventoryStock::withoutGlobalScopes()
                    ->where('item_id', $item->id)
                    ->update([
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'updated_at' => now(),
                    ]);

                // 2. Bersihkan mutasi bertipe initial_stock
                $deletedMutations = InventoryStockMutation::withoutGlobalScopes()
                    ->where('item_id', $item->id)
                    ->where('reference_type', 'initial_stock')
                    ->delete();

                // 3. Bersihkan serial numbers di gudang yang belum pernah terpasang
                $deletedSerials = ItemSerial::withoutGlobalScopes()
                    ->where('item_id', $item->id)
                    ->where('status', 'in_warehouse')
                    ->delete();

                $this->line("  → [{$item->sku}] {$item->name}: Stok di-reset ke 0 (Mutasi awal dibersihkan: {$deletedMutations}, Serial dihapus: {$deletedSerials})");

                // 4. Jika opsi --delete-items aktif, hapus item dari katalog
                if ($deleteItems) {
                    // Cek apakah pernah ada mutasi riil (selain initial_stock)
                    $hasRealMutations = InventoryStockMutation::withoutGlobalScopes()
                        ->where('item_id', $item->id)
                        ->where('reference_type', '!=', 'initial_stock')
                        ->exists();

                    if (!$hasRealMutations) {
                        InventoryStock::withoutGlobalScopes()->where('item_id', $item->id)->delete();
                        $item->forceDelete();
                        $this->line("    ✓ Item '{$item->sku}' berhasil dihapus dari Master Barang.");
                    } else {
                        $this->warn("    ! Item '{$item->sku}' memiliki riwayat transaksi lain, hanya stok yang dinolkan.");
                    }
                }
            }

            // Bersihkan juga saldo awal umum lainnya jika ada di inventory_stock_mutations
            $otherInitialMutations = InventoryStockMutation::withoutGlobalScopes()
                ->where('reference_type', 'initial_stock')
                ->delete();

            // Bersihkan tabel legacy support_maintenance_materials jika ada
            if (Schema::hasTable('support_maintenance_materials')) {
                DB::table('support_maintenance_materials')->truncate();
                $this->line("  → Tabel legacy support_maintenance_materials berhasil dikosongkan.");
            }
        });

        $this->info('✓ Pembersihan data dummy inventaris berhasil diselesaikan!');
        $this->info('Stok fisik di Master Barang sekarang sudah bersih / 0.');
        $this->info('Anda dapat mencatat penerimaan barang baru riil melalui menu Barang Masuk (Inbound).');

        return Command::SUCCESS;
    }
}
