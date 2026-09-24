<?php

namespace App\Exports;

use App\Models\ISP\Onu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class OnuExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Onu::with(['olt', 'vendor', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode ONU',
            'Nama ONU',
            'Deskripsi',
            'Model',
            'Serial Number',
            'MAC Address',
            'PON Port',
            'OLT',
            'Vendor',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($onu): array
    {
        return [
            $onu->id,
            $onu->code,
            $onu->name,
            $onu->description,
            $onu->model,
            $onu->serial_number,
            $onu->mac_address,
            $onu->pon_port,
            $onu->olt?->name,
            $onu->vendor?->name,
            $onu->status === 'active' ? 'Aktif' : 'Nonaktif',
            $onu->createdBy?->name,
            $onu->updatedBy?->name,
            $onu->created_at?->format('d/m/Y H:i'),
            $onu->updated_at?->format('d/m/Y H:i')
        ];
    }
}
