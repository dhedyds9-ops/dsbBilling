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
    protected $filters;

    public function __construct($selectedIds = null, array $filters = [])
    {
        $this->selectedIds = $selectedIds;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = PPPoEUser::with(['customer', 'customerService.customer', 'serviceProfile', 'router', 'reseller', 'createdBy', 'updatedBy']);

        if (!empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        } else {
            if (!empty($this->filters['search'])) {
                $search = $this->filters['search'];
                $query->where(function($sq) use ($search) {
                    $sq->where('username', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function($cq) use ($search) {
                            $cq->where('name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                        });
                });
            }

            if (!empty($this->filters['status'])) {
                $status = $this->filters['status'];
                if ($status === 'online') {
                    $query->where('status', 'active')->whereIn('username', function($sq) {
                        $sq->select('user')->from('pppoe_active_sessions');
                    });
                } elseif ($status === 'offline') {
                    $query->where('status', 'active')->whereNotIn('username', function($sq) {
                        $sq->select('user')->from('pppoe_active_sessions');
                    });
                } else {
                    $query->where('status', $status);
                }
            }

            if (!empty($this->filters['reseller_id'])) {
                $rid = $this->filters['reseller_id'];
                if ($rid === 'owner' || $rid === 'pusat') {
                    $query->where(function($sq) {
                        $sq->whereNull('pppoe_users.reseller_id')
                           ->whereDoesntHave('customer', fn($cq) => $cq->whereNotNull('customers.reseller_id'));
                    });
                } elseif ($rid === 'reseller') {
                    $query->where(function($sq) {
                        $sq->whereNotNull('pppoe_users.reseller_id')
                           ->orWhereHas('customer', fn($cq) => $cq->whereNotNull('customers.reseller_id'));
                    });
                } else {
                    $query->where(function($sq) use ($rid) {
                        $sq->where('pppoe_users.reseller_id', $rid)
                           ->orWhereHas('customer', fn($cq) => $cq->where('customers.reseller_id', $rid));
                    });
                }
            }
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
        $customer = $pppoeUser->customerService?->customer ?? $pppoeUser->customer;

        return [
            $pppoeUser->username,
            $pppoeUser->password, // Export in plain for migration/backup
            $customer?->name ?? '-',
            $customer?->phone ?? '-',
            $customer?->email ?? '-',
            $customer?->address ?? '-',
            $pppoeUser->serviceProfile?->name ?? '-',
            $pppoeUser->subscription?->billing_cycle ?? 'monthly',
            $pppoeUser->status,
            $pppoeUser->router?->name ?? '-',
            $pppoeUser->mac_address ?? '-',
            $pppoeUser->static_ip ?? '-',
            $pppoeUser->reseller?->name ?? ($customer?->reseller?->name ?? 'Pusat'),
        ];
    }
}
