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
    protected $filters;

    public function __construct($selectedIds = null, array $filters = [])
    {
        $this->selectedIds = $selectedIds;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = HotspotUser::with(['customer', 'customerService.customer', 'serviceProfile', 'router', 'reseller', 'createdBy', 'updatedBy']);

        if (!empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        } else {
            // Terapkan filter jika tidak ada baris yang dipilih spesifik
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
                        $sq->select('user')->from('hotspot_active_sessions');
                    });
                } elseif ($status === 'offline') {
                    $query->where('status', 'active')->whereNotIn('username', function($sq) {
                        $sq->select('user')->from('hotspot_active_sessions');
                    });
                } else {
                    $query->where('status', $status);
                }
            }

            if (!empty($this->filters['reseller_id'])) {
                $rid = $this->filters['reseller_id'];
                if ($rid === 'owner' || $rid === 'pusat') {
                    $query->where(function($sq) {
                        $sq->whereNull('hotspot_users.reseller_id')
                           ->whereDoesntHave('customer', fn($cq) => $cq->whereNotNull('customers.reseller_id'));
                    });
                } elseif ($rid === 'reseller') {
                    $query->where(function($sq) {
                        $sq->whereNotNull('hotspot_users.reseller_id')
                           ->orWhereHas('customer', fn($cq) => $cq->whereNotNull('customers.reseller_id'));
                    });
                } else {
                    $query->where(function($sq) use ($rid) {
                        $sq->where('hotspot_users.reseller_id', $rid)
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
            'ID',
            'Username',
            'Customer',
            'No HP',
            'Service Profile',
            'Status',
            'Router',
            'MAC Address',
            'Static IP',
            'Reseller',
            'Dibuat Pada',
        ];
    }

    public function map($user): array
    {
        $customer = $user->customerService?->customer ?? $user->customer;

        return [
            $user->id,
            $user->username,
            $customer?->name ?? '-',
            $customer?->phone ?? '-',
            $user->serviceProfile?->name ?? '-',
            $this->getStatusText($user->status),
            $user->router?->name ?? '-',
            $user->mac_address ?? '-',
            $user->static_ip ?? '-',
            $user->reseller?->name ?? ($customer?->reseller?->name ?? 'Pusat'),
            $user->created_at?->format('d/m/Y H:i'),
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
