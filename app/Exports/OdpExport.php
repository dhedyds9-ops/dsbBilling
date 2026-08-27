<?php

namespace App\Exports;

use App\Models\ISP\Odp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class OdpExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Odp::with(['odc', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode ODP',
            'Nama ODP',
            'Deskripsi',
            'Alamat',
            'Latitude',
            'Longitude',
            'Jumlah Port',
            'Jumlah Port Aktif',
            'ODC',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($odp): array
    {
        return [
            $odp->id,
            $odp->code,
            $odp->name,
            $odp->description,
            $odp->address,
            $odp->latitude,
            $odp->longitude,
            $odp->port_count,
            $odp->active_port_count,
            $odp->odc?->name,
            $odp->status === 'active' ? 'Aktif' : 'Nonaktif',
            $odp->createdBy?->name,
            $odp->updatedBy?->name,
            $odp->created_at?->format('d/m/Y H:i'),
            $odp->updated_at?->format('d/m/Y H:i')
        ];
    }
}
