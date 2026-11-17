<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryInbound;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InboundExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $search;
    protected $warehouseId;
    protected $status;

    public function __construct($search = '', $warehouseId = '', $status = '')
    {
        $this->search = trim($search);
        $this->warehouseId = $warehouseId;
        $this->status = $status;
    }

    public function collection()
    {
        $q = $this->search;

        return InventoryInbound::with(['warehouse', 'receiver', 'items.item'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('inbound_number', 'like', "%{$q}%")
                        ->orWhere('supplier_name', 'like', "%{$q}%")
                        ->orWhere('reference_doc', 'like', "%{$q}%");
                });
            })
            ->when(!empty($this->warehouseId), function ($b) {
                $b->where('warehouse_id', $this->warehouseId);
            })
            ->when(!empty($this->status), function ($b) {
                $b->where('status', $this->status);
            })
            ->orderByDesc('received_date')
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No Inbound',
            'Tanggal Masuk',
            'Gudang Tujuan',
            'Sumber Pengadaan',
            'Supplier / Vendor',
            'No. Ref / Dokumen',
            'Rincian Barang',
            'Total Qty',
            'Total Nilai (Rp)',
            'Status',
            'Petugas Penerima',
            'Catatan',
        ];
    }

    public function map($inbound): array
    {
        $itemsSummary = $inbound->items->map(function ($it) {
            return ($it->item?->name ?? 'Item') . ' (' . (float)$it->quantity . ' ' . ($it->item?->unit ?? 'unit') . ')';
        })->implode(', ');

        $totalQty = $inbound->items->sum('quantity');
        $totalCost = $inbound->items->sum('subtotal');

        return [
            $inbound->inbound_number,
            $inbound->received_date ? $inbound->received_date->format('d/m/Y') : '-',
            $inbound->warehouse?->name ?? '-',
            strtoupper(str_replace('_', ' ', $inbound->source_type ?? 'SUPPLIER')),
            $inbound->supplier_name ?? '-',
            $inbound->reference_doc ?? '-',
            $itemsSummary ?: '-',
            (float) $totalQty,
            (float) $totalCost,
            strtoupper($inbound->status ?? 'RECEIVED'),
            $inbound->receiver?->name ?? '-',
            $inbound->notes ?? '-',
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
