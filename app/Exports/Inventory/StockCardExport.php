<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryStockMutation;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockCardExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $warehouseId;
    protected $itemId;
    protected $typeFilter;
    protected $dateFrom;
    protected $dateTo;
    protected $runningBalance = 0.0;

    public function __construct($warehouseId, $itemId, $typeFilter = '', $dateFrom = '', $dateTo = '')
    {
        $this->warehouseId = $warehouseId;
        $this->itemId = $itemId;
        $this->typeFilter = $typeFilter;
        $this->dateFrom = $dateFrom ?: date('Y-m-01');
        $this->dateTo = $dateTo ?: date('Y-m-d');

        // Hitung opening balance sebelum dateFrom
        if ($this->warehouseId && $this->itemId) {
            $openingIn = (float) InventoryStockMutation::where('warehouse_id', $this->warehouseId)
                ->where('item_id', $this->itemId)
                ->whereDate('created_at', '<', $this->dateFrom)
                ->sum('qty_in');

            $openingOut = (float) InventoryStockMutation::where('warehouse_id', $this->warehouseId)
                ->where('item_id', $this->itemId)
                ->whereDate('created_at', '<', $this->dateFrom)
                ->sum('qty_out');

            $this->runningBalance = $openingIn - $openingOut;
        }
    }

    public function collection()
    {
        if (!$this->warehouseId || !$this->itemId) {
            return collect();
        }

        $query = InventoryStockMutation::with(['actor', 'itemSerial', 'item', 'warehouse'])
            ->where('warehouse_id', $this->warehouseId)
            ->where('item_id', $this->itemId)
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        if (!empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }

        return $query->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Waktu Transaksi',
            'No. Referensi',
            'Gudang',
            'Nama Barang',
            'Tipe Mutasi',
            'Serial Number (SN)',
            'Masuk (In)',
            'Keluar (Out)',
            'Saldo Berjalan',
            'Petugas / Operator',
            'Catatan / Keterangan',
        ];
    }

    public function map($mut): array
    {
        $in = (float) $mut->qty_in;
        $out = (float) $mut->qty_out;
        $this->runningBalance += ($in - $out);

        return [
            $mut->created_at ? $mut->created_at->format('d/m/Y H:i') : '-',
            $mut->reference_number ?: '-',
            $mut->warehouse?->name ?? '-',
            $mut->item?->name ?? '-',
            strtoupper(str_replace('_', ' ', $mut->type ?? 'MUTATION')),
            $mut->itemSerial?->serial_number ?: ($mut->notes && str_contains($mut->notes, 'SN') ? $mut->notes : '-'),
            $in > 0 ? $in : 0,
            $out > 0 ? $out : 0,
            $this->runningBalance,
            $mut->actor?->name ?? 'System',
            $mut->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E293B'],
                ],
            ],
        ];
    }
}
