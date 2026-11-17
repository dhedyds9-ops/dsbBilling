<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryRequisition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RequisitionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $search;
    protected $status;
    protected $urgency;

    public function __construct($search = '', $status = '', $urgency = '')
    {
        $this->search = trim($search);
        $this->status = $status;
        $this->urgency = $urgency;
    }

    public function collection()
    {
        $q = $this->search;

        return InventoryRequisition::with(['warehouse', 'requester', 'approver', 'customer', 'items.item'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('requisition_number', 'like', "%{$q}%")
                        ->orWhereHas('requester', fn($sq) => $sq->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', "%{$q}%"));
                });
            })
            ->when(!empty($this->status), function ($b) {
                $b->where('status', $this->status);
            })
            ->when(!empty($this->urgency), function ($b) {
                $b->where('urgency', $this->urgency);
            })
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Permintaan',
            'Tanggal Pengajuan',
            'Tipe Permintaan',
            'Tingkat Urgensi',
            'Nama Pemohon',
            'Role Pemohon',
            'Gudang Tujuan',
            'Tujuan Pelanggan / Proyek',
            'Daftar Barang Diminta',
            'Status Persetujuan',
            'Disetujui Oleh',
            'Tanggal Persetujuan',
            'Catatan / Alasan',
        ];
    }

    public function map($req): array
    {
        $itemsList = $req->items->map(function ($it) {
            $qtyReq = (float) $it->quantity_requested;
            $qtyApp = $it->quantity_approved !== null ? (float) $it->quantity_approved : $qtyReq;
            return ($it->item?->name ?? 'Item') . ' (Req: ' . $qtyReq . ', App: ' . $qtyApp . ' ' . ($it->item?->unit ?? 'unit') . ')';
        })->implode('; ');

        $destination = $req->customer ? $req->customer->name : ($req->installation ? 'PSB #' . $req->installation->installation_number : ($req->ticket ? 'Tiket #' . $req->ticket->ticket_number : 'Stok Operasional'));

        return [
            $req->requisition_number,
            $req->created_at ? $req->created_at->format('d/m/Y H:i') : '-',
            strtoupper(str_replace('_', ' ', $req->requisition_type ?? 'INSTALLATION')),
            strtoupper($req->urgency ?? 'NORMAL'),
            $req->requester?->name ?? '-',
            strtoupper($req->requester_type ?: 'TEKNISI'),
            $req->warehouse?->name ?? '-',
            $destination,
            $itemsList ?: '-',
            strtoupper(str_replace('_', ' ', $req->status ?? 'PENDING')),
            $req->approver?->name ?? '-',
            $req->approved_at ? $req->approved_at->format('d/m/Y H:i') : '-',
            $req->notes ?: ($req->rejection_reason ?? '-'),
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
