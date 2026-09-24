<?php

namespace App\Exports;

use App\Models\ISP\Olt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class OltExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Olt::with(['pop', 'vendor', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode OLT',
            'Nama OLT',
            'Deskripsi',
            'Model',
            'Serial Number',
            'IP Address',
            'Port Count',
            'Active Port Count',
            'POP',
            'Vendor',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($olt): array
    {
        return [
            $olt->id,
            $olt->code,
            $olt->name,
            $olt->description,
            $olt->model,
            $olt->serial_number,
            $olt->ip_address,
            $olt->port_count,
            $olt->active_port_count,
            $olt->pop?->name,
            $olt->vendor?->name,
            $olt->status === 'active' ? 'Aktif' : 'Nonaktif',
            $olt->createdBy?->name,
            $olt->updatedBy?->name,
            $olt->created_at?->format('d/m/Y H:i'),
            $olt->updated_at?->format('d/m/Y H:i')
        ];
    }
}
