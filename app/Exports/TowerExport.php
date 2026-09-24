<?php

namespace App\Exports;

use App\Models\ISP\Tower;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class TowerExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Tower::with(['createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Tower',
            'Nama Tower',
            'Deskripsi',
            'Alamat',
            'Provinsi',
            'Kota',
            'Kecamatan',
            'Desa',
            'Latitude',
            'Longitude',
            'Tinggi',
            'Tipe',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($tower): array
    {
        return [
            $tower->id,
            $tower->code,
            $tower->name,
            $tower->description,
            $tower->address,
            $tower->province,
            $tower->city,
            $tower->district,
            $tower->village,
            $tower->latitude,
            $tower->longitude,
            $tower->height,
            $tower->type,
            $tower->status === 'active' ? 'Aktif' : 'Nonaktif',
            $tower->createdBy?->name,
            $tower->updatedBy?->name,
            $tower->created_at?->format('d/m/Y H:i'),
            $tower->updated_at?->format('d/m/Y H:i')
        ];
    }
}