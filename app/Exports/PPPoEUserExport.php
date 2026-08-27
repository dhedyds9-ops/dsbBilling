<?php

namespace App\Exports;

use App\Models\ISP\PPPoEUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class PPPoEUserExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = PPPoEUser::with(['customer', 'serviceProfile', 'createdBy', 'updatedBy']);

        if ($this->selectedIds && count($this->selectedIds) > 0) {
            $query->whereIn('id', $this->selectedIds);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Username',
            'Nama Pelanggan',
            'Paket Langganan',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($pppoeUser): array
    {
        return [
            $pppoeUser->id,
            $pppoeUser->uuid,
            $pppoeUser->username,
            $pppoeUser->customer?->name ?? '-',
            $pppoeUser->serviceProfile?->name ?? '-',
            $this->getStatusText($pppoeUser->status),
            $pppoeUser->createdBy?->name ?? '-',
            $pppoeUser->updatedBy?->name ?? '-',
            $pppoeUser->created_at?->format('d/m/Y H:i'),
            $pppoeUser->updated_at?->format('d/m/Y H:i')
        ];
    }

    protected function getStatusText($status): string
    {
        return match($status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'suspended' => 'Suspended',
            'terminated' => 'Terminated',
            'pending' => 'Pending',
            default => $status
        };
    }
}
