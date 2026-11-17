<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $search;
    protected $category;
    protected $status;

    public function __construct($search = '', $category = '', $status = '')
    {
        $this->search = trim($search);
        $this->category = $category;
        $this->status = $status;
    }

    public function collection()
    {
        $q = $this->search;

        return Item::with(['stocks.warehouse'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('brand', 'like', "%{$q}%")
                        ->orWhere('model', 'like', "%{$q}%");
                });
            })
            ->when(!empty($this->category), function ($b) {
                $b->where('category', $this->category);
            })
            ->when(!empty($this->status), function ($b) {
                if ($this->status === 'active') {
                    $b->where('is_active', true);
                } elseif ($this->status === 'serialized') {
                    $b->where('is_serialized', true);
                } elseif ($this->status === 'low_stock') {
                    $b->whereHas('stocks', function ($sq) {
                        $sq->havingRaw('SUM(quantity) <= min_stock');
                    });
                }
            })
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'SKU',
            'Nama Barang',
            'Kategori',
            'Merk',
            'Model',
            'Satuan',
            'Tracking Serial',
            'Stok Minimum',
            'Total Stok Fisik',
            'Harga Beli Terakhir',
            'Status',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->sku,
            $item->name,
            strtoupper(str_replace('_', ' ', $item->category ?? '-')),
            $item->brand ?? '-',
            $item->model ?? '-',
            $item->unit ?? 'Unit',
            $item->is_serialized ? 'Ya (Serial)' : 'Non-Serial',
            (float) ($item->min_stock ?? 0),
            (float) $item->stocks->sum('quantity'),
            (float) ($item->last_purchase_price ?? 0),
            $item->is_active ? 'Aktif' : 'Non-Aktif',
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
