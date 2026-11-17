<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryStockOpname;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpnameExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $search;
    protected $warehouseFilter;
    protected $statusFilter;

    public function __construct($search = '', $warehouseFilter = '', $statusFilter = '')
    {
        $this->search = trim($search);
        $this->warehouseFilter = $warehouseFilter;
        $this->statusFilter = $statusFilter;
    }

    public function collection()
    {
        try {
            $query = InventoryStockOpname::with(['warehouse', 'creator', 'approver', 'items.item'])
                ->latest('id');

            if ($this->search) {
                $query->where('opname_number', 'like', "%{$this->search}%");
            }
            if ($this->warehouseFilter) {
                $query->where('warehouse_id', $this->warehouseFilter);
            }
            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            return $query->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'No. Opname',
            'Tanggal Opname',
            'Gudang',
            'Status',
            'Jumlah Item Dihitung',
            'Pembuat Sesi',
            'Disetujui Oleh',
            'Waktu Persetujuan',
            'Catatan Opname',
        ];
    }

    public function map($opn): array
    {
        return [
            $opn->opname_number,
            $opn->opname_date ? $opn->opname_date->format('d/m/Y') : '-',
            $opn->warehouse?->name ?? '-',
            match($opn->status) {
                'completed' => 'Selesai (Adjusted)',
                'in_progress' => 'Sedang Dihitung',
                'draft' => 'Draft',
                default => ucfirst($opn->status)
            },
            $opn->items ? $opn->items->count() : 0,
            $opn->creator?->name ?? 'System',
            $opn->approver?->name ?? '-',
            $opn->approved_at ? $opn->approved_at->format('d/m/Y H:i') : '-',
            $opn->notes ?? '-',
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
