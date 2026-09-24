<?php

namespace App\Exports;

use App\Models\ISP\Pop;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class PopExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Pop::with(['tower', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode POP',
            'Nama POP',
            'Deskripsi',
            'Alamat',
            'Provinsi',
            'Kota',
            'Kecamatan',
            'Desa',
            'Latitude',
            'Longitude',
            'Tower',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($pop): array
    {
        return [
            $pop->id,
            $pop->code,
            $pop->name,
            $pop->description,
            $pop->address,
            $pop->province,
            $pop->city,
            $pop->district,
            $pop->village,
            $pop->latitude,
            $pop->longitude,
            $pop->tower?->name,
            $pop->status === 'active' ? 'Aktif' : 'Nonaktif',
            $pop->createdBy?->name,
            $pop->updatedBy?->name,
            $pop->created_at?->format('d/m/Y H:i'),
            $pop->updated_at?->format('d/m/Y H:i')
        ];
    }
}
