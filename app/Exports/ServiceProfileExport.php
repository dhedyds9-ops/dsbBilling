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

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = ServiceProfile::with(['owner', 'serviceProfileType']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
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
            'Burst Download',
            'Burst Upload',
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
            $profile->download_speed . ' Mbps',
            $profile->upload_speed . ' Mbps',
            $profile->burst_limit_download ? $profile->burst_limit_download . ' Mbps' : '-',
            $profile->burst_limit_upload ? $profile->burst_limit_upload . ' Mbps' : '-',
            $profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : '-',
            $profile->owner_price ? 'Rp ' . number_format($profile->owner_price, 0, ',', '.') : '-',
            $profile->reseller_price ? 'Rp ' . number_format($profile->reseller_price, 0, ',', '.') : '-',
            $profile->status === 'active' ? 'Aktif' : 'Nonaktif',
            $profile->reseller ? $profile->reseller->name : '-',
            $profile->created_at ? $profile->created_at->format('d/m/Y H:i') : '-',
            $profile->updated_at ? $profile->updated_at->format('d/m/Y H:i') : '-'
        ];
    }
}
