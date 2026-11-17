<?php

namespace App\Exports\Inventory;

use App\Models\Inventory\InventoryDispatch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DispatchExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $search;
    protected $warehouseId;
    protected $distributionMethod;

    public function __construct($search = '', $warehouseId = '', $distributionMethod = '')
    {
        $this->search = trim($search);
        $this->warehouseId = $warehouseId;
        $this->distributionMethod = $distributionMethod;
    }

    public function collection()
    {
        $q = $this->search;

        return InventoryDispatch::with(['warehouse', 'recipient', 'dispatcher', 'customer', 'items.item'])
            ->when(!empty($q), function ($b) use ($q) {
                $b->where(function ($sub) use ($q) {
                    $sub->where('dispatch_number', 'like', "%{$q}%")
                        ->orWhereHas('recipient', fn($sq) => $sq->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', "%{$q}%"));
                });
            })
            ->when(!empty($this->warehouseId), function ($b) {
                $b->where('warehouse_id', $this->warehouseId);
            })
            ->when(!empty($this->distributionMethod), function ($b) {
                $b->where('distribution_method', $this->distributionMethod);
            })
            ->orderByDesc('dispatched_at')
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Surat Jalan',
            'Tanggal Pengeluaran',
            'Gudang Asal',
            'Metode Distribusi',
            'Penerima Barang',
            'Tipe Penerima',
            'Tujuan / Pelanggan',
            'Rincian Barang',
            'Daftar Serial Number (SN)',
            'Total Kuantitas',
            'Status',
            'Petugas Dispatcher',
            'Catatan',
        ];
    }

    public function map($dispatch): array
    {
        $itemsList = [];
        $serialsList = [];
        $totalQty = 0;

        foreach ($dispatch->items as $di) {
            $qty = (float) $di->quantity;
            $totalQty += $qty;
            $itemsList[] = ($di->item?->name ?? 'Item') . ' (' . $qty . ' ' . ($di->item?->unit ?? 'unit') . ')';

            $sns = is_array($di->serials) ? $di->serials : json_decode($di->serials ?? '[]', true);
            if (!empty($sns)) {
                $serialsList = array_merge($serialsList, $sns);
            }
        }

        $destination = $dispatch->customer ? $dispatch->customer->name : ($dispatch->installation ? 'PSB #' . $dispatch->installation->installation_number : ($dispatch->ticket ? 'Tiket #' . $dispatch->ticket->ticket_number : 'Stok Operasional'));

        return [
            $dispatch->dispatch_number,
            $dispatch->dispatched_at ? $dispatch->dispatched_at->format('d/m/Y H:i') : '-',
            $dispatch->warehouse?->name ?? '-',
            $dispatch->distribution_method === 'pickup_reseller' ? 'Metode A (Reseller Ambil)' : 'Metode B (Teknisi Langsung)',
            $dispatch->recipient?->name ?? '-',
            strtoupper($dispatch->recipient_type ?: 'TEKNISI'),
            $destination,
            implode('; ', $itemsList),
            implode(', ', $serialsList),
            $totalQty,
            strtoupper($dispatch->status ?? 'DISPATCHED'),
            $dispatch->dispatcher?->name ?? 'System',
            $dispatch->notes ?? '-',
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
