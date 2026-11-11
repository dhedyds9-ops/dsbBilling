<?php

namespace App\Exports;

use App\Models\ISP\ServiceProfile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServiceProfileExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
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
        $query = ServiceProfile::with(['owner', 'serviceProfileType']);

        if (!empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        } else {
            if (!empty($this->filters['search'])) {
                $query->where('name', 'like', '%' . $this->filters['search'] . '%');
            }

            if (!empty($this->filters['service_type'])) {
                $query->where('service_type', $this->filters['service_type']);
            }

            if (!empty($this->filters['status'])) {
                $query->where('status', $this->filters['status']);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Paket',
            'Nama Paket',
            'Deskripsi',
            'Jenis Layanan',
            'Tipe Paket',
            'Download Speed',
            'Upload Speed',
            'Harga Dasar',
            'Harga Owner',
            'Harga Reseller',
            'Status',
            'Owner',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($profile): array
    {
        return [
            $profile->id,
            $profile->code,
            $profile->name,
            $profile->description,
            $profile->service_type,
            $profile->package_type,
            $profile->download_speed,
            $profile->upload_speed,
            $profile->base_price,
            $profile->owner_price,
            $profile->reseller_price,
            $profile->status,
            $profile->owner?->name ?? 'Pusat',
            $profile->created_at?->format('d/m/Y H:i'),
            $profile->updated_at?->format('d/m/Y H:i')
        ];
    }
}
