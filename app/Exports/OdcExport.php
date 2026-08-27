<?php

namespace App\Exports;

use App\Models\ISP\Odc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class OdcExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Odc::with(['olt', 'pop', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode ODC',
            'Nama ODC',
            'Deskripsi',
            'Alamat',
            'Latitude',
            'Longitude',
            'Jumlah Port',
            'Jumlah Port Aktif',
            'OLT',
            'POP',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($odc): array
    {
        return [
            $odc->id,
            $odc->code,
            $odc->name,
            $odc->description,
            $odc->address,
            $odc->latitude,
            $odc->longitude,
            $odc->port_count,
            $odc->active_port_count,
            $odc->olt?->name,
            $odc->pop?->name,
            $odc->status === 'active' ? 'Aktif' : 'Nonaktif',
            $odc->createdBy?->name,
            $odc->updatedBy?->name,
            $odc->created_at?->format('d/m/Y H:i'),
            $odc->updated_at?->format('d/m/Y H:i')
        ];
    }
}
