<?php

namespace App\Exports;

use App\Models\ISP\HotspotUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class HotspotUserExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        $query = HotspotUser::with(['customer', 'serviceProfile', 'createdBy', 'updatedBy']);

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
            'Customer',
            'Service Profile',
            'Status',
            'Dibuat Oleh',
            'Diperbarui Oleh',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->uuid,
            $user->username,
            $user->customer?->name ?? '-',
            $user->serviceProfile?->name ?? '-',
            $this->getStatusText($user->status),
            $user->createdBy?->name ?? '-',
            $user->updatedBy?->name ?? '-',
            $user->created_at?->format('d/m/Y H:i'),
            $user->updated_at?->format('d/m/Y H:i')
        ];
    }

    protected function getStatusText($status): string
    {
        $statuses = [
            'pending' => 'Pending',
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'suspended' => 'Suspended',
            'terminated' => 'Terminated'
        ];

        return $statuses[$status] ?? $status;
    }
}
