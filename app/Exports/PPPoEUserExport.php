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
            'Username',
            'Password',
            'Nama Pelanggan',
            'No HP',
            'Email',
            'Alamat',
            'Paket Langganan',
            'Siklus Tagihan',
            'Status',
            'Router',
            'Mac Address',
            'Static IP',
            'Reseller',
        ];
    }

    public function map($pppoeUser): array
    {
        return [
            $pppoeUser->username,
            $pppoeUser->password, // Export in plain for migration/backup
            $pppoeUser->customer?->name ?? '-',
            $pppoeUser->customer?->phone ?? '-',
            $pppoeUser->customer?->email ?? '-',
            $pppoeUser->customer?->address ?? '-',
            $pppoeUser->serviceProfile?->name ?? '-',
            $pppoeUser->subscription?->billing_cycle ?? 'monthly',
            $pppoeUser->status ?? 'active',
            $pppoeUser->router?->name ?? '-',
            $pppoeUser->mac_address ?? '-',
            $pppoeUser->static_ip ?? '-',
            $pppoeUser->reseller?->name ?? '-',
        ];
    }
}
