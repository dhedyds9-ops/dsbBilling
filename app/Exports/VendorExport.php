<?php

namespace App\Exports;

use App\Models\ISP\Vendor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class VendorExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Vendor::with(['createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Vendor',
            'Nama Vendor',
            'Deskripsi',
            'Telepon',
            'Email',
            'Alamat',
            'Kontak Person',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($vendor): array
    {
        return [
            $vendor->id,
            $vendor->code,
            $vendor->name,
            $vendor->description,
            $vendor->phone,
            $vendor->email,
            $vendor->address,
            $vendor->contact_person,
            $vendor->status === 'active' ? 'Aktif' : 'Nonaktif',
            $vendor->createdBy?->name,
            $vendor->updatedBy?->name,
            $vendor->created_at?->format('d/m/Y H:i'),
            $vendor->updated_at?->format('d/m/Y H:i')
        ];
    }
}
