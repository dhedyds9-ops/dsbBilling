<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryReturn;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReturnExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $search;
    protected $status;
    protected $warehouseId;

    public function __construct($search = '', $status = '', $warehouseId = '')
    {
        $this->search = trim($search);
        $this->status = $status;
        $this->warehouseId = $warehouseId;
    }

    public function collection()
    {
        $q = $this->search;

        return InventoryReturn::with(['returner', 'warehouse', 'inspector', 'items.item', 'items.itemSerial'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('return_number', 'like', "%{$q}%")
                        ->orWhereHas('returner', fn($sq) => $sq->where('name', 'like', "%{$q}%"));
                });
            })
            ->when(!empty($this->status), function ($b) {
                $b->where('status', $this->status);
            })
            ->when(!empty($this->warehouseId), function ($b) {
                $b->where('warehouse_id', $this->warehouseId);
            })
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Retur',
            'Tanggal Retur',
            'Pihak Pengembali',
            'Tipe Pengembali',
            'Gudang Tujuan',
            'Rincian Barang & SN',
            'Total Kuantitas',
            'Status',
            'Pemeriksa QC',
            'Tanggal QC',
            'Catatan',
        ];
    }

    public function map($ret): array
    {
        $itemsList = [];
        $totalQty = 0;

        foreach ($ret->items as $ri) {
            $qty = (float) $ri->quantity;
            $totalQty += $qty;
            $snPart = $ri->itemSerial ? ' [SN: ' . $ri->itemSerial->serial_number . ']' : '';
            $cond = ' (' . ($ri->condition ?? 'good') . ')';
            $itemsList[] = ($ri->item?->name ?? 'Item') . $snPart . ' - ' . $qty . ' ' . ($ri->item?->unit ?? 'unit') . $cond;
        }

        return [
            $ret->return_number,
            $ret->created_at ? $ret->created_at->format('d/m/Y H:i') : '-',
            $ret->returner?->name ?? '-',
            strtoupper($ret->returner_type ?: 'TEKNISI'),
            $ret->warehouse?->name ?? '-',
            implode('; ', $itemsList),
            $totalQty,
            strtoupper(str_replace('_', ' ', $ret->status ?? 'RECEIVED')),
            $ret->inspector?->name ?? '-',
            $ret->inspected_at ? $ret->inspected_at->format('d/m/Y H:i') : '-',
            $ret->notes ?? '-',
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
