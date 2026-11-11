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
    protected $filters;

    public function __construct($selectedIds = null, array $filters = [])
    {
        $this->selectedIds = $selectedIds;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Voucher::with(['voucherPool', 'serviceProfile', 'nasDevice', 'reseller', 'createdBy', 'updatedBy']);

        if (!empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        } else {
            if (!empty($this->filters['search'])) {
                $query->where('code', 'like', '%' . $this->filters['search'] . '%');
            }

            if (!empty($this->filters['status'])) {
                $query->where('status', $this->filters['status']);
            }

            if (!empty($this->filters['voucher_pool_id'])) {
                $query->where('voucher_pool_id', $this->filters['voucher_pool_id']);
            }

            if (!empty($this->filters['reseller_id'])) {
                $query->where('reseller_id', $this->filters['reseller_id']);
            }
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
            'Reseller',
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
            $voucher->voucherPool?->name ?? '-',
            $voucher->serviceProfile?->name ?? '-',
            $voucher->nasDevice?->name ?? '-',
            $voucher->reseller?->name ?? 'Pusat',
            $voucher->status,
            $voucher->type ?? 'hotspot',
            $voucher->fee_seller,
            $voucher->activated_at?->format('d/m/Y H:i'),
            $voucher->expires_at?->format('d/m/Y H:i'),
            $voucher->createdBy?->name ?? '-',
            $voucher->updatedBy?->name ?? '-',
            $voucher->created_at?->format('d/m/Y H:i'),
            $voucher->updated_at?->format('d/m/Y H:i')
        ];
    }
}
