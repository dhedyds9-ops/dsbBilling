<?php

namespace App\Exports;

use App\Models\ISP\Voucher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class VoucherExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Voucher::with(['voucherPool', 'serviceProfile', 'nasDevice', 'owner', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Voucher',
            'Voucher Pool',
            'Service Profile',
            'NAS Device',
            'Owner',
            'Status',
            'Tipe',
            'Fee Seller',
            'Tanggal Aktif',
            'Tanggal Kadaluarsa',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($voucher): array
    {
        return [
            $voucher->id,
            $voucher->code,
            $voucher->voucherPool?->name,
            $voucher->serviceProfile?->name,
            $voucher->nasDevice?->name,
            $voucher->owner?->name,
            $voucher->status,
            $voucher->type,
            $voucher->fee_seller,
            $voucher->activated_at?->format('d/m/Y H:i'),
            $voucher->expires_at?->format('d/m/Y H:i'),
            $voucher->createdBy?->name,
            $voucher->updatedBy?->name,
            $voucher->created_at?->format('d/m/Y H:i'),
            $voucher->updated_at?->format('d/m/Y H:i')
        ];
    }
}
