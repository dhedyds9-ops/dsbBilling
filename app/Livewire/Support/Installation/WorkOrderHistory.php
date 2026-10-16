<?php

namespace App\Livewire\Support\Installation;

use App\Livewire\AdminComponent;
use App\Models\Customer\CustomerService;
use App\Models\Support\Ticket;
use App\Models\User;
use Livewire\WithPagination;

class WorkOrderHistory extends AdminComponent
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = 'all'; // all, installation, repair
    public string $statusFilter = 'all'; // all, completed, pending
    public $technicianFilter = '';
    public $resellerFilter = '';
    public $selectedMonth;
    public $selectedYear;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'support';
        $this->activePage = 'support.work-orders.history';
        
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;

        $this->breadcrumbs = [
            ['label' => 'Admin Portal', 'url' => '#'],
            ['label' => 'CRM & Support', 'url' => '#'],
            ['label' => 'Riwayat Instalasi & Kerja Teknisi', 'url' => route('support.work-orders.history')],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTypeFilter(string $type)
    {
        $this->typeFilter = in_array($type, ['all', 'installation', 'repair']) ? $type : 'all';
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);
        $items = collect();

        // 1. Installations (CustomerService)
        if ($this->typeFilter === 'all' || $this->typeFilter === 'installation') {
            $csQ = CustomerService::withoutGlobalScopes()
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes(), 'onu.olt', 'serviceProfile', 'service', 'createdBy']);

            if ($this->technicianFilter) {
                $csQ->where('customer_services.created_by', $this->technicianFilter);
            }

            if ($this->resellerFilter) {
                if ($this->resellerFilter === 'direct') {
                    $csQ->whereNull('customer_services.reseller_id');
                } else {
                    $csQ->where('customer_services.reseller_id', $this->resellerFilter);
                }
            }

            if ($this->statusFilter === 'completed') {
                $csQ->whereIn('customer_services.status', ['active', 'completed', 'approved']);
            } elseif ($this->statusFilter === 'pending') {
                $csQ->whereIn('customer_services.status', ['pending', 'provisioning', 'in_progress']);
            }

            if ($this->selectedMonth && $this->selectedYear) {
                $m = $this->selectedMonth;
                $y = $this->selectedYear;
                $csQ->where(function($q) use ($m, $y) {
                    $q->whereMonth('customer_services.activated_at', $m)->whereYear('customer_services.activated_at', $y)
                      ->orWhere(function($sq) use ($m, $y) {
                          $sq->whereNull('customer_services.activated_at')->whereMonth('customer_services.created_at', $m)->whereYear('customer_services.created_at', $y);
                      });
                });
            }

            if ($search) {
                $csQ->where(function($q) use ($search) {
                    $q->where('customer_services.username', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where(function($sq) use ($search) {
                              $sq->where('customers.name', 'like', "%{$search}%")
                                 ->orWhere('customers.phone', 'like', "%{$search}%")
                                 ->orWhere('customers.address', 'like', "%{$search}%")
                                 ->orWhere('customers.code', 'like', "%{$search}%");
                          });
                      })
                      ->orWhereHas('onu', function($oq) use ($search) {
                          $oq->where('serial_number', 'like', "%{$search}%");
                      })
                      ->orWhereHas('createdBy', function($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $customerServices = $csQ->latest('activated_at')->latest('created_at')->get();

            foreach ($customerServices as $cs) {
                $serviceType = strtoupper($cs->serviceProfile?->service_type ?? ($cs->service?->type ?? 'PPPOE'));
                $serviceName = $cs->serviceProfile?->name ?? ($cs->service?->name ?? 'Layanan Internet');
                $isCompleted = in_array($cs->status, ['active', 'completed', 'approved']);

                $items->push((object)[
                    'id' => 'cs_' . $cs->id,
                    'raw_id' => $cs->id,
                    'type' => 'installation',
                    'type_badge' => 'Pasang Baru (' . $serviceType . ')',
                    'service_type' => $serviceType,
                    'customer_name' => $cs->customer?->name ?? 'Pelanggan #' . $cs->customer_id,
                    'customer_code' => $cs->customer?->code ?? '-',
                    'customer_phone' => $cs->customer?->phone ?? '-',
                    'customer_address' => $cs->customer?->address ?? 'Alamat tidak tersedia',
                    'technician_name' => $cs->createdBy?->name ?? 'Teknisi Lapangan',
                    'reseller_id' => $cs->reseller_id,
                    'package_name' => $serviceName,
                    'username' => $cs->username,
                    'device_info' => $cs->onu ? 'ONU ' . ($cs->onu->model ?? '') . ' (SN: ' . $cs->onu->serial_number . ')' : '-',
                    'date' => $cs->activated_at ?? $cs->created_at,
                    'status' => $cs->status,
                    'status_label' => $isCompleted ? 'Selesai / Aktif' : 'Proses Pemasangan',
                    'is_completed' => $isCompleted,
                    'latitude' => $cs->customer?->latitude,
                    'longitude' => $cs->customer?->longitude,
                ]);
            }
        }

        // 2. Repair Tickets
        if ($this->typeFilter === 'all' || $this->typeFilter === 'repair') {
            $tQ = Ticket::withoutGlobalScopes()
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes(), 'assignedTo']);

            if ($this->technicianFilter) {
                $tQ->where('tickets.assigned_to', $this->technicianFilter);
            }

            if ($this->resellerFilter) {
                if ($this->resellerFilter === 'direct') {
                    $tQ->whereNull('tickets.reseller_id');
                } else {
                    $tQ->where('tickets.reseller_id', $this->resellerFilter);
                }
            }

            if ($this->statusFilter === 'completed') {
                $tQ->whereIn('tickets.status', ['resolved', 'closed']);
            } elseif ($this->statusFilter === 'pending') {
                $tQ->whereIn('tickets.status', ['open', 'in_progress', 'pending']);
            }

            if ($this->selectedMonth && $this->selectedYear) {
                $m = $this->selectedMonth;
                $y = $this->selectedYear;
                $tQ->where(function($q) use ($m, $y) {
                    $q->whereMonth('tickets.resolved_at', $m)->whereYear('tickets.resolved_at', $y)
                      ->orWhere(function($sq) use ($m, $y) {
                          $sq->whereNull('tickets.resolved_at')->whereMonth('tickets.created_at', $m)->whereYear('tickets.created_at', $y);
                      });
                });
            }

            if ($search) {
                $tQ->where(function($q) use ($search) {
                    $q->where('tickets.title', 'like', "%{$search}%")
                      ->orWhere('tickets.description', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where(function($sq) use ($search) {
                              $sq->where('customers.name', 'like', "%{$search}%")
                                 ->orWhere('customers.phone', 'like', "%{$search}%")
                                 ->orWhere('customers.address', 'like', "%{$search}%");
                          });
                      })
                      ->orWhereHas('assignedTo', function($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $tickets = $tQ->latest('resolved_at')->latest('created_at')->get();

            foreach ($tickets as $ticket) {
                $isCompleted = in_array($ticket->status, ['resolved', 'closed']);

                $items->push((object)[
                    'id' => 'ticket_' . $ticket->id,
                    'raw_id' => $ticket->id,
                    'type' => 'repair',
                    'type_badge' => 'Perbaikan / Gangguan',
                    'service_type' => 'TIKET',
                    'customer_name' => $ticket->customer?->name ?? 'Pelanggan #' . $ticket->customer_id,
                    'customer_code' => 'TIKET #' . $ticket->id,
                    'customer_phone' => $ticket->customer?->phone ?? '-',
                    'customer_address' => $ticket->customer?->address ?? 'Alamat tidak tersedia',
                    'technician_name' => $ticket->assignedTo?->name ?? 'Teknisi Lapangan',
                    'reseller_id' => $ticket->reseller_id,
                    'package_name' => $ticket->title,
                    'username' => null,
                    'device_info' => $ticket->description ? substr($ticket->description, 0, 80) : '-',
                    'date' => $ticket->resolved_at ?? $ticket->created_at,
                    'status' => $ticket->status,
                    'status_label' => $isCompleted ? 'Selesai Diperbaiki' : 'Sedang Ditangani',
                    'is_completed' => $isCompleted,
                    'latitude' => $ticket->customer?->latitude,
                    'longitude' => $ticket->customer?->longitude,
                ]);
            }
        }

        // Sort by date desc
        $sortedItems = $items->sortByDesc(fn($i) => $i->date ? \Carbon\Carbon::parse($i->date)->timestamp : 0);

        // Stats
        $totalInstallations = CustomerService::withoutGlobalScopes()->count();
        $monthInstallations = CustomerService::withoutGlobalScopes()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $totalRepairs = Ticket::withoutGlobalScopes()->count();
        $monthRepairs = Ticket::withoutGlobalScopes()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Technician & Reseller Dropdowns
        $technicians = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['technician', 'teknisi', 'administrator', 'manager']);
        })->get();

        $resellers = User::whereHas('roles', function($q) {
            $q->where('name', 'reseller');
        })->get();

        $page = $this->getPage();
        $perPage = 15;
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedItems->forPage($page, $perPage),
            $sortedItems->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $selectedMonthDate = \Carbon\Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);

        return view('livewire.support.installation.work-order-history', [
            'items' => $paginatedItems,
            'totalInstallations' => $totalInstallations,
            'monthInstallations' => $monthInstallations,
            'totalRepairs' => $totalRepairs,
            'monthRepairs' => $monthRepairs,
            'technicians' => $technicians,
            'resellers' => $resellers,
            'selectedMonthName' => $selectedMonthDate->translatedFormat('F Y'),
        ]);
    }
}
