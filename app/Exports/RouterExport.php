<?php

namespace App\Exports;

use App\Models\ISP\Router;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class RouterExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = Router::with(['pop', 'vendor', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Router',
            'Nama Router',
            'Deskripsi',
            'Model',
            'Serial Number',
            'IP Address',
            'API Port',
            'Use SSL',
            'Timeout',
            'Username',
            'RouterOS Version',
            'POP',
            'Vendor',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($router): array
    {
        return [
            $router->id,
            $router->code,
            $router->name,
            $router->description,
            $router->model,
            $router->serial_number,
            $router->ip_address,
            $router->api_port,
            $router->use_ssl ? 'Ya' : 'Tidak',
            $router->timeout,
            $router->username,
            $router->routeros_version,
            $router->pop?->name,
            $router->vendor?->name,
            $router->status === 'active' ? 'Aktif' : 'Nonaktif',
            $router->createdBy?->name,
            $router->updatedBy?->name,
            $router->created_at?->format('d/m/Y H:i'),
            $router->updated_at?->format('d/m/Y H:i')
        ];
    }
}
