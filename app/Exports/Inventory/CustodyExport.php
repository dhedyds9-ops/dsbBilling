<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryCustody;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustodyExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $search;
    protected $holderId;
    protected $status;

    public function __construct($search = '', $holderId = '', $status = '')
    {
        $this->search = trim($search);
        $this->holderId = $holderId;
        $this->status = $status;
    }

    public function collection()
    {
        $q = $this->search;

        return InventoryCustody::with(['item', 'itemSerial', 'holder', 'customer'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->whereHas('item', fn($sq) => $sq->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"))
                        ->orWhereHas('itemSerial', fn($sq) => $sq->where('serial_number', 'like', "%{$q}%"))
                        ->orWhereHas('holder', fn($sq) => $sq->where('name', 'like', "%{$q}%"));
                });
            })
            ->when(!empty($this->holderId), function ($b) {
                $b->where('holder_id', $this->holderId);
            })
            ->when(!empty($this->status), function ($b) {
                if ($this->status === 'active') {
                    $b->where('status', 'in_custody')->whereRaw('(quantity - used_quantity - returned_quantity) > 0');
                } else {
                    $b->where('status', $this->status);
                }
            })
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Pemegang (Holder)',
            'Tipe Pemegang',
            'Kode SKU',
            'Nama Barang',
            'Serial Number (SN)',
            'Qty Diterima',
            'Qty Terpakai',
            'Qty Dikembalikan (Retur)',
            'Sisa Fisik di Tangan',
            'Satuan',
            'Status',
            'Peruntukan Pelanggan',
            'Tanggal Distribusi',
            'Catatan',
        ];
    }

    public function map($custody): array
    {
        $remaining = (float) $custody->quantity - (float) $custody->used_quantity - (float) $custody->returned_quantity;

        return [
            $custody->id,
            $custody->holder?->name ?? '-',
            strtoupper($custody->holder_type ?: 'TEKNISI'),
            $custody->item?->sku ?? '-',
            $custody->item?->name ?? '-',
            $custody->itemSerial?->serial_number ?? '-',
            (float) $custody->quantity,
            (float) $custody->used_quantity,
            (float) $custody->returned_quantity,
            max(0, $remaining),
            $custody->item?->unit ?? 'unit',
            strtoupper(str_replace('_', ' ', $custody->status ?? 'IN_CUSTODY')),
            $custody->customer?->name ?? '-',
            $custody->created_at ? $custody->created_at->format('d/m/Y H:i') : '-',
            $custody->notes ?? '-',
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
