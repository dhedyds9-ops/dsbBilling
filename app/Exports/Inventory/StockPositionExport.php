<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockPositionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $warehouseId;
    protected $category;

    public function __construct($warehouseId = '', $category = '')
    {
        $this->warehouseId = $warehouseId;
        $this->category = $category;
    }

    public function collection()
    {
        return InventoryStock::with(['item', 'warehouse'])
            ->when($this->warehouseId, fn($q) => $q->where('warehouse_id', $this->warehouseId))
            ->when($this->category, fn($q) => $q->whereHas('item', fn($i) => $i->where('category', $this->category)))
            ->get();
    }

    public function headings(): array
    {
        return [
            'Gudang',
            'Kode SKU',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Jumlah Stok Fisik',
            'Batas Min. Stok',
            'Status Persediaan',
        ];
    }

    public function map($stock): array
    {
        $qty = (float) $stock->quantity;
        $min = (float) ($stock->item?->min_stock ?? 0);
        $status = $qty <= $min ? 'MENIPIS (LOW)' : 'AMAN';

        return [
            $stock->warehouse?->name ?? '-',
            $stock->item?->sku ?? '-',
            $stock->item?->name ?? '-',
            strtoupper(str_replace('_', ' ', $stock->item?->category ?? '-')),
            $stock->item?->unit ?? 'Unit',
            $qty,
            $min,
            $status,
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
