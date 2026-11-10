<?php

namespace App\Livewire\Support\Installation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Installation;
use App\Models\Customer\CustomerService;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\WithPagination;

class WorkOrderHistory extends AdminComponent
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = 'all'; // all, installation, repair, maintenance, activation
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
            ['label' => 'Support', 'url' => route('support.ticket')],
            ['label' => 'Riwayat WO (Work Order History)', 'url' => route('support.work-orders.history')],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTypeFilter(string $type)
    {
        $this->typeFilter = in_array($type, ['all', 'installation', 'repair', 'maintenance', 'activation']) ? $type : 'all';
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);
        $items = collect();
        $m = $this->selectedMonth;
        $y = $this->selectedYear;

        // 1. INSTALASI PASANG BARU (From CRM Installation Table)
        if ($this->typeFilter === 'all' || $this->typeFilter === 'installation') {
            $instQ = Installation::withoutGlobalScopes()
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'), 'onu.olt', 'assignedTo', 'technician']);

            if ($this->technicianFilter) {
                $techId = (int) $this->technicianFilter;
                $instQ->where(function($q) use ($techId) {
                    $q->where('assigned_to', $techId)->orWhere('technician_id', $techId);
                });
            }

            if ($this->statusFilter === 'completed') {
                $instQ->whereIn('status', ['completed', 'verified', 'active']);
            } elseif ($this->statusFilter === 'pending') {
                $instQ->whereIn('status', ['pending', 'scheduled', 'in_progress', 'assigned']);
            }

            if ($m && $y) {
                $instQ->where(function($q) use ($m, $y) {
                    $q->whereMonth('completed_at', $m)->whereYear('completed_at', $y)
                      ->orWhere(function($sq) use ($m, $y) {
                          $sq->whereNull('completed_at')->whereMonth('created_at', $m)->whereYear('created_at', $y);
                      });
                });
            }

            if ($search) {
                $instQ->where(function($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                      ->orWhere('onu_serial_number', 'like', "%{$search}%")
                      ->orWhere('router_serial_number', 'like', "%{$search}%")
                      ->orWhere('uuid', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            $installations = $instQ->latest('completed_at')->latest('created_at')->get();

            foreach ($installations as $inst) {
                $isCompleted = in_array($inst->status, ['completed', 'verified', 'active']);
                $techName = $inst->assignedTo?->name ?? ($inst->technician?->name ?? 'Teknisi Lapangan');
                $resellerId = $inst->customer?->reseller_id;
                $resellerName = $inst->customer?->reseller?->name ?? null;

                if ($this->resellerFilter) {
                    if ($this->resellerFilter === 'direct' && !empty($resellerId)) continue;
                    if ($this->resellerFilter !== 'direct' && (string)$resellerId !== (string)$this->resellerFilter) continue;
                }

                $deviceInfo = $inst->onu_serial_number ? ('SN: ' . $inst->onu_serial_number) : ($inst->onu ? ('SN: ' . $inst->onu->serial_number) : '-');
                if (!empty($inst->optical_power)) {
                    $deviceInfo .= " (" . $inst->optical_power . " dBm)";
                }

                $items->push((object)[
                    'id' => 'inst_' . $inst->id,
                    'raw_id' => $inst->id,
                    'code' => $inst->uuid ? substr($inst->uuid, 0, 8) : ('INST-' . $inst->id),
                    'type' => 'installation',
                    'type_badge' => 'Pasang Baru (PSB)',
                    'service_type' => 'FTTH / ONT',
                    'customer_name' => $inst->customer?->name ?? $inst->customer_name,
                    'customer_code' => $inst->customer?->code ?? ('SPK-' . $inst->id),
                    'customer_phone' => $inst->customer?->phone ?? '-',
                    'customer_address' => $inst->customer?->address ?? ($inst->installation_address ?? 'Alamat pemasangan'),
                    'technician_name' => $techName,
                    'reseller_id' => $resellerId,
                    'reseller_name' => $resellerName,
                    'package_name' => 'Instalasi Perangkat ONT',
                    'username' => null,
                    'device_info' => $deviceInfo,
                    'date' => $inst->completed_at ?? ($inst->scheduled_at ?? $inst->created_at),
                    'status' => $inst->status,
                    'status_label' => $isCompleted ? 'Selesai Terpasang' : ucfirst($inst->status),
                    'is_completed' => $isCompleted,
                ]);
            }
        }

        // 2. GANGGUAN / REPAIR TICKETS
        if ($this->typeFilter === 'all' || $this->typeFilter === 'repair') {
            $tQ = Ticket::withoutGlobalScopes()
                ->whereNotIn('category', ['psb', 'installation'])
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'), 'assignedTo', 'reseller']);

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

            if ($m && $y) {
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
                      ->orWhere('tickets.uuid', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('address', 'like', "%{$search}%");
                      });
                });
            }

            $tickets = $tQ->latest('resolved_at')->latest('created_at')->get();

            foreach ($tickets as $ticket) {
                $isCompleted = in_array($ticket->status, ['resolved', 'closed']);
                $resellerId = $ticket->reseller_id ?? $ticket->customer?->reseller_id;
                $resellerName = $ticket->reseller?->name ?? ($ticket->customer?->reseller?->name ?? null);

                $items->push((object)[
                    'id' => 'ticket_' . $ticket->id,
                    'raw_id' => $ticket->id,
                    'code' => $ticket->uuid ? ('TKT-' . substr(str_replace('-', '', $ticket->uuid), 0, 6)) : ('TIKET-' . $ticket->id),
                    'type' => 'repair',
                    'type_badge' => 'Gangguan (' . strtoupper($ticket->category ?? 'Internet') . ')',
                    'service_type' => 'TIKET',
                    'customer_name' => $ticket->customer?->name ?? 'Pelanggan #' . $ticket->customer_id,
                    'customer_code' => $ticket->customer?->code ?? ('TIKET #' . $ticket->id),
                    'customer_phone' => $ticket->customer?->phone ?? '-',
                    'customer_address' => $ticket->customer?->address ?? 'Alamat tidak tersedia',
                    'technician_name' => $ticket->assignedTo?->name ?? 'Teknisi Lapangan',
                    'reseller_id' => $resellerId,
                    'reseller_name' => $resellerName,
                    'package_name' => $ticket->title,
                    'username' => null,
                    'device_info' => $ticket->description ? substr($ticket->description, 0, 80) : '-',
                    'date' => $ticket->resolved_at ?? $ticket->created_at,
                    'status' => $ticket->status,
                    'status_label' => $isCompleted ? 'Selesai Diperbaiki' : 'Sedang Ditangani',
                    'is_completed' => $isCompleted,
                ]);
            }
        }

        // 3. PEMELIHARAAN / MAINTENANCE JARINGAN (support_maintenances)
        if (($this->typeFilter === 'all' || $this->typeFilter === 'maintenance') && Schema::hasTable('support_maintenances')) {
            $mQ = DB::table('support_maintenances')
                ->leftJoin('users', 'support_maintenances.assigned_to', '=', 'users.id')
                ->select(['support_maintenances.*', 'users.name as technician_name', 'users.email as technician_email']);

            if ($this->technicianFilter) {
                $mQ->where('support_maintenances.assigned_to', $this->technicianFilter);
            }

            if ($this->statusFilter === 'completed') {
                $mQ->where('support_maintenances.status', 'completed');
            } elseif ($this->statusFilter === 'pending') {
                $mQ->whereIn('support_maintenances.status', ['scheduled', 'in_progress']);
            }

            if ($m && $y) {
                $mQ->where(function($q) use ($m, $y) {
                    $q->whereMonth('support_maintenances.completed_at', $m)->whereYear('support_maintenances.completed_at', $y)
                      ->orWhere(function($sq) use ($m, $y) {
                          $sq->whereNull('support_maintenances.completed_at')
                             ->whereMonth('support_maintenances.created_at', $m)->whereYear('support_maintenances.created_at', $y);
                      });
                });
            }

            if ($search) {
                $mQ->where(function($q) use ($search) {
                    $q->where('support_maintenances.title', 'like', "%{$search}%")
                      ->orWhere('support_maintenances.customer_name', 'like', "%{$search}%")
                      ->orWhere('support_maintenances.device_name', 'like', "%{$search}%")
                      ->orWhere('support_maintenances.location_name', 'like', "%{$search}%");
                });
            }

            $maintenances = $mQ->orderByDesc('completed_at')->orderByDesc('created_at')->get();

            foreach ($maintenances as $maint) {
                $isCompleted = ($maint->status === 'completed');
                $typeLabel = ucfirst($maint->type ?? 'preventive');

                $items->push((object)[
                    'id' => 'maint_' . $maint->id,
                    'raw_id' => $maint->id,
                    'code' => 'MT-' . str_pad($maint->id, 4, '0', STR_PAD_LEFT),
                    'type' => 'maintenance',
                    'type_badge' => 'Maintenance (' . $typeLabel . ')',
                    'service_type' => strtoupper($maint->device_type ?? 'INFRA'),
                    'customer_name' => $maint->customer_name ?? 'Infrastruktur Jaringan',
                    'customer_code' => $maint->ticket_id ? 'TIKET #' . $maint->ticket_id : ('MT #' . $maint->id),
                    'customer_phone' => $maint->customer_phone ?? '-',
                    'customer_address' => $maint->customer_address ?? ($maint->location_name ?? 'Lokasi Perangkat'),
                    'technician_name' => $maint->technician_name ?? 'Teknisi Jaringan',
                    'reseller_id' => null,
                    'reseller_name' => 'Internal ISP',
                    'package_name' => $maint->title,
                    'username' => null,
                    'device_info' => ($maint->device_name ? $maint->device_type . ': ' . $maint->device_name : 'Perangkat Jaringan'),
                    'date' => $maint->completed_at ?? ($maint->scheduled_date ?? $maint->created_at),
                    'status' => $maint->status,
                    'status_label' => $isCompleted ? 'Selesai Dikerjakan' : ucfirst($maint->status),
                    'is_completed' => $isCompleted,
                ]);
            }
        }

        // 4. AKTIVASI LAYANAN (CustomerService)
        if ($this->typeFilter === 'all' || $this->typeFilter === 'activation') {
            $csQ = CustomerService::withoutGlobalScopes()
                ->whereNotNull('activated_at')
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'), 'onu.olt', 'serviceProfile', 'service', 'createdBy', 'reseller']);

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
                $csQ->whereIn('customer_services.status', ['active', 'completed']);
            } elseif ($this->statusFilter === 'pending') {
                $csQ->whereIn('customer_services.status', ['pending', 'provisioning']);
            }

            if ($m && $y) {
                $csQ->whereMonth('customer_services.activated_at', $m)->whereYear('customer_services.activated_at', $y);
            }

            if ($search) {
                $csQ->where(function($q) use ($search) {
                    $q->where('customer_services.username', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            $customerServices = $csQ->latest('activated_at')->get();

            foreach ($customerServices as $cs) {
                $serviceType = strtoupper($cs->serviceProfile?->service_type ?? ($cs->service?->type ?? 'PPPOE'));
                $serviceName = $cs->serviceProfile?->name ?? ($cs->service?->name ?? 'Paket Internet');
                $isCompleted = ($cs->status === 'active');
                $resellerId = $cs->reseller_id ?? $cs->customer?->reseller_id;
                $resellerName = $cs->reseller?->name ?? ($cs->customer?->reseller?->name ?? null);

                $items->push((object)[
                    'id' => 'act_' . $cs->id,
                    'raw_id' => $cs->id,
                    'code' => 'ACT-' . $cs->id,
                    'type' => 'activation',
                    'type_badge' => 'Aktivasi (' . $serviceType . ')',
                    'service_type' => $serviceType,
                    'customer_name' => $cs->customer?->name ?? 'Pelanggan #' . $cs->customer_id,
                    'customer_code' => $cs->customer?->code ?? '-',
                    'customer_phone' => $cs->customer?->phone ?? '-',
                    'customer_address' => $cs->customer?->address ?? 'Alamat tidak tersedia',
                    'technician_name' => $cs->createdBy?->name ?? 'NOC / Dispatcher',
                    'reseller_id' => $resellerId,
                    'reseller_name' => $resellerName,
                    'package_name' => $serviceName . ' (' . $cs->username . ')',
                    'username' => $cs->username,
                    'device_info' => $cs->onu ? 'ONU ' . ($cs->onu->model ?? '') . ' (SN: ' . $cs->onu->serial_number . ')' : '-',
                    'date' => $cs->activated_at,
                    'status' => $cs->status,
                    'status_label' => $isCompleted ? 'Aktif Terprovisi' : ucfirst($cs->status),
                    'is_completed' => $isCompleted,
                ]);
            }
        }

        // Sort all aggregated items by date desc
        $sortedItems = $items->sortByDesc(fn($i) => $i->date ? Carbon::parse($i->date)->timestamp : 0);

        // Calculate KPI Statistics
        $totalInstallations = Installation::withoutGlobalScopes()->count();
        $monthInstallations = Installation::withoutGlobalScopes()
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        $totalRepairs = Ticket::withoutGlobalScopes()->whereNotIn('category', ['psb', 'installation'])->count();
        $monthRepairs = Ticket::withoutGlobalScopes()
            ->whereNotIn('category', ['psb', 'installation'])
            ->whereMonth('resolved_at', now()->month)
            ->whereYear('resolved_at', now()->year)
            ->count();

        $totalMaintenances = Schema::hasTable('support_maintenances') ? DB::table('support_maintenances')->count() : 0;
        $totalActivations = CustomerService::withoutGlobalScopes()->whereNotNull('activated_at')->count();

        // Dropdowns
        $technicians = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['technician', 'teknisi', 'administrator', 'manager', 'dispatcher']);
        })->get();

        $resellers = User::whereHas('roles', function($q) {
            $q->where('name', 'reseller');
        })->get();

        $page = $this->getPage();
        $perPage = 15;
        $paginatedItems = new LengthAwarePaginator(
            $sortedItems->forPage($page, $perPage),
            $sortedItems->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $selectedMonthDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);

        return view('livewire.support.installation.work-order-history', [
            'items' => $paginatedItems,
            'totalInstallations' => $totalInstallations,
            'monthInstallations' => $monthInstallations,
            'totalRepairs' => $totalRepairs,
            'monthRepairs' => $monthRepairs,
            'totalMaintenances' => $totalMaintenances,
            'totalActivations' => $totalActivations,
            'technicians' => $technicians,
            'resellers' => $resellers,
            'selectedMonthName' => $selectedMonthDate->translatedFormat('F Y'),
        ]);
    }
}
