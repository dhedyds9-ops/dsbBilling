<?php

namespace App\Livewire\Support\Installation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Installation;
use App\Models\Customer\CustomerService;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Services\Workforce\UnifiedQcApprovalService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\WithPagination;

class WorkOrderHistory extends AdminComponent
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = 'all'; // all, qc_approval, installation, repair, maintenance, activation
    public string $statusFilter = 'all'; // all, completed, pending, pending_approval
    public $technicianFilter = '';
    public $resellerFilter = '';
    public $selectedMonth;
    public $selectedYear;

    // QC Inspection Modal Properties
    public bool $showQcModal = false;
    public ?int $selectedWoId = null;
    public ?WorkOrder $selectedWo = null;
    public string $qcNotes = '';
    public int $pendingQcCount = 0;

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
        $this->typeFilter = in_array($type, ['all', 'qc_approval', 'installation', 'repair', 'maintenance', 'activation']) ? $type : 'all';
        $this->resetPage();
    }

    public function openDetailInspection(string $type, int $rawId): void
    {
        $wo = null;

        if ($type === 'qc_approval' || $type === 'wo') {
            $wo = WorkOrder::withoutGlobalScopes()->find($rawId);
        } elseif ($type === 'installation') {
            $wo = WorkOrder::withoutGlobalScopes()->where('installation_id', $rawId)->first();
            if (!$wo) {
                $inst = Installation::withoutGlobalScopes()->with(['customer.reseller', 'technician'])->find($rawId);
                if ($inst) {
                    $wo = WorkOrder::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'company_id' => $inst->customer?->company_id ?? Auth::user()?->company_id,
                        'branch_id' => $inst->customer?->branch_id ?? Auth::user()?->branch_id,
                        'reseller_id' => $inst->customer?->reseller_id,
                        'customer_id' => $inst->customer_id,
                        'installation_id' => $inst->id,
                        'type' => 'installation',
                        'title' => 'Instalasi PSB: ' . ($inst->customer?->name ?? $inst->customer_name),
                        'assigned_to' => $inst->assigned_to ?? $inst->technician_id,
                        'status' => in_array($inst->status, ['verified', 'completed']) ? 'completed' : ($inst->status === 'in_progress' ? 'in_progress' : 'assigned'),
                        'evidence_photos' => is_array($inst->photos) ? $inst->photos : [],
                        'optical_rx_power' => $inst->optical_power ? (float) $inst->optical_power : null,
                        'scheduled_at' => $inst->scheduled_at ?? $inst->created_at,
                        'completed_at' => $inst->completed_at,
                    ]);
                }
            } elseif (empty($wo->evidence_photos)) {
                $inst = Installation::withoutGlobalScopes()->find($rawId);
                if ($inst && !empty($inst->photos)) {
                    $wo->update(['evidence_photos' => $inst->photos]);
                }
            }
        } elseif ($type === 'repair' || $type === 'ticket') {
            $wo = WorkOrder::withoutGlobalScopes()->where('ticket_id', $rawId)->first();
            if (!$wo) {
                $ticket = Ticket::withoutGlobalScopes()->with(['customer.reseller', 'assignedTo'])->find($rawId);
                if ($ticket) {
                    $wo = WorkOrder::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'company_id' => $ticket->company_id ?? Auth::user()?->company_id,
                        'branch_id' => $ticket->branch_id ?: ($ticket->customer?->branch_id ?: Auth::user()?->branch_id),
                        'reseller_id' => $ticket->reseller_id ?? $ticket->customer?->reseller_id,
                        'customer_id' => $ticket->customer_id ?? $ticket->user_id,
                        'ticket_id' => $ticket->id,
                        'type' => $ticket->category ?? 'troubleshooting',
                        'title' => 'Perbaikan: ' . ($ticket->title ?: ($ticket->ticket_number . ' - ' . $ticket->subject)),
                        'description' => $ticket->description ?? '',
                        'assigned_to' => $ticket->assigned_to,
                        'status' => in_array($ticket->status, ['resolved', 'closed']) ? 'completed' : ($ticket->status === 'in_progress' ? 'in_progress' : 'assigned'),
                        'evidence_photos' => is_array($ticket->photos) ? $ticket->photos : [],
                        'scheduled_at' => $ticket->created_at,
                        'completed_at' => $ticket->resolved_at,
                    ]);
                }
            } elseif (empty($wo->evidence_photos)) {
                $ticket = Ticket::withoutGlobalScopes()->find($rawId);
                if ($ticket && !empty($ticket->photos)) {
                    $wo->update(['evidence_photos' => $ticket->photos]);
                }
            }
        } elseif ($type === 'maintenance') {
            $wo = WorkOrder::withoutGlobalScopes()->where('source_type', 'maintenance')->where('source_id', $rawId)->first();
            if (!$wo) {
                $maint = DB::table('support_maintenances')->where('id', $rawId)->first();
                if ($maint) {
                    $wo = WorkOrder::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'company_id' => Auth::user()?->company_id,
                        'branch_id' => $maint->branch_id ?: Auth::user()?->branch_id,
                        'type' => 'maintenance',
                        'source_type' => 'maintenance',
                        'source_id' => $maint->id,
                        'ticket_id' => $maint->ticket_id ?? null,
                        'customer_id' => $maint->customer_id ?? null,
                        'title' => 'Pemeliharaan: ' . ($maint->title ?: ($maint->device_name ?: 'Jaringan')),
                        'description' => $maint->notes ?: 'Jadwal pemeliharaan berkala jaringan ISP.',
                        'assigned_to' => $maint->assigned_to,
                        'status' => $maint->status === 'completed' ? 'completed' : ($maint->status === 'in_progress' ? 'in_progress' : 'assigned'),
                        'scheduled_at' => !empty($maint->scheduled_date) ? \Carbon\Carbon::parse($maint->scheduled_date) : now(),
                        'completed_at' => $maint->completed_at ? \Carbon\Carbon::parse($maint->completed_at) : null,
                    ]);
                }
            }
        }

        if ($wo) {
            $this->openQcInspection($wo->id);
        } else {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'Data detail pekerjaan tidak ditemukan.']);
        }
    }

    public function openQcInspection(int $workOrderId): void
    {
        $this->selectedWoId = $workOrderId;
        $this->selectedWo = WorkOrder::withoutGlobalScopes()->with([
            'customer',
            'technician',
            'branch',
            'reseller',
            'checklists',
            'ticket',
            'installation.survey.prospect',
            'installation.onu',
            'auditLogs.user',
        ])->find($workOrderId);

        if (!$this->selectedWo) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Work Order tidak ditemukan.']);
            return;
        }

        $this->qcNotes = '';
        $this->showQcModal = true;
    }

    public function closeQcModal(): void
    {
        $this->showQcModal = false;
        $this->selectedWoId = null;
        $this->selectedWo = null;
        $this->qcNotes = '';
    }

    public function approveQc(): void
    {
        if (!$this->selectedWoId) return;

        $wo = WorkOrder::withoutGlobalScopes()->find($this->selectedWoId);
        if (!$wo) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Work Order tidak ditemukan.']);
            return;
        }

        try {
            app(UnifiedQcApprovalService::class)->approve($wo, Auth::user(), $this->qcNotes);
            $this->closeQcModal();
            $this->dispatch('alert', ['type' => 'success', 'message' => "QC SPK #{$wo->id} berhasil disetujui (Approved)."]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Gagal menyetujui QC: ' . $e->getMessage()]);
        }
    }

    public function rejectQc(): void
    {
        if (!$this->selectedWoId) return;

        $this->validate([
            'qcNotes' => 'required|string|min:3|max:1000',
        ], [
            'qcNotes.required' => 'Alasan penolakan QC wajib diisi agar teknisi tahu bagian yang perlu diperbaiki.',
            'qcNotes.min' => 'Alasan penolakan minimal 3 karakter.',
        ]);

        $wo = WorkOrder::withoutGlobalScopes()->find($this->selectedWoId);
        if (!$wo) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Work Order tidak ditemukan.']);
            return;
        }

        try {
            app(UnifiedQcApprovalService::class)->reject($wo, Auth::user(), $this->qcNotes);
            $this->closeQcModal();
            $this->dispatch('alert', ['type' => 'success', 'message' => "QC SPK #{$wo->id} ditolak dan dikembalikan ke teknisi untuk perbaikan."]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Gagal menolak QC: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $search = trim($this->search);
        $items = collect();
        $m = $this->selectedMonth;
        $y = $this->selectedYear;

        // Preload WorkOrder mappings untuk mengaitkan SPK & foto eviden secara instan (O(1))
        $instWoMap = WorkOrder::withoutGlobalScopes()->whereNotNull('installation_id')->pluck('id', 'installation_id');
        $ticketWoMap = WorkOrder::withoutGlobalScopes()->whereNotNull('ticket_id')->pluck('id', 'ticket_id');
        $maintWoMap = WorkOrder::withoutGlobalScopes()->where('source_type', 'maintenance')->whereNotNull('source_id')->pluck('id', 'source_id');

        // 0. ANTREAN QC / WORK ORDERS PENDING APPROVAL
        $qcQuery = app(UnifiedQcApprovalService::class)->getPendingQcQuery(Auth::user());
        $this->pendingQcCount = $qcQuery->count();

        if ($this->typeFilter === 'all' || $this->typeFilter === 'qc_approval') {
            $woQ = clone $qcQuery;

            if ($this->technicianFilter) {
                $woQ->where('assigned_to', $this->technicianFilter);
            }

            if ($this->resellerFilter) {
                if ($this->resellerFilter === 'direct') {
                    $woQ->whereNull('reseller_id');
                } else {
                    $woQ->where('reseller_id', $this->resellerFilter);
                }
            }

            if ($search) {
                $woQ->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('uuid', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            $pendingWos = $woQ->get();

            if ($this->typeFilter === 'qc_approval' || $this->statusFilter === 'all' || $this->statusFilter === 'pending' || $this->statusFilter === 'pending_approval') {
                foreach ($pendingWos as $wo) {
                    $prefix = match(strtolower((string)$wo->type)) {
                        'installation', 'psb', 'install' => 'SPK-PSB-',
                        'troubleshooting', 'ticket', 'repair' => 'SPK-TKT-',
                        'maintenance' => 'SPK-MNT-',
                        default => 'SPK-WO-',
                    };
                    $items->push((object)[
                        'id' => 'wo_' . $wo->id,
                        'raw_id' => $wo->id,
                        'work_order_id' => $wo->id,
                        'code' => $prefix . str_pad($wo->id, 4, '0', STR_PAD_LEFT),
                        'type' => 'qc_approval',
                        'type_badge' => 'Menunggu QC',
                        'service_type' => ucfirst($wo->type ?? 'Work Order'),
                        'customer_name' => $wo->customer?->name ?? ($wo->title ?: 'Pelanggan SPK #' . $wo->id),
                        'customer_code' => $wo->customer?->code ?? ('SPK-' . $wo->id),
                        'customer_phone' => $wo->customer?->phone ?? '-',
                        'customer_address' => $wo->customer?->address ?? '-',
                        'technician_name' => $wo->technician?->name ?? 'Teknisi Lapangan',
                        'reseller_id' => $wo->reseller_id,
                        'reseller_name' => $wo->reseller?->name ?? null,
                        'package_name' => $wo->title,
                        'username' => null,
                        'device_info' => !empty($wo->optical_rx_power) ? ("RX: {$wo->optical_rx_power} dBm") : '-',
                        'date' => $wo->completed_at ?? ($wo->started_at ?? $wo->updated_at),
                        'status' => 'pending_approval',
                        'status_label' => 'Menunggu Persetujuan QC',
                        'is_completed' => false,
                        'is_qc_pending' => true,
                        'work_order' => $wo,
                        'sla_due_at' => $wo->sla_due_at,
                        'sla_started_at' => $wo->sla_started_at ?: ($wo->started_at ?: $wo->created_at),
                        'sla_status' => $wo->sla_status ?? 'normal',
                        'priority' => $wo->priority ?? 'normal',
                    ]);
                }
            }
        }

        // 1. INSTALASI PASANG BARU (From CRM Installation Table)
        if ($this->typeFilter === 'all' || $this->typeFilter === 'installation') {
            $instQ = Installation::withoutGlobalScopes()
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'), 'onu.olt', 'assignedTo', 'technician'])
                ->where(function($iq) {
                    $iq->whereNull('customer_id')
                       ->orWhereHas('customer', function($cq) {
                           $cq->withoutGlobalScopes()->where(function($sq) {
                               $sq->whereNull('code')->orWhere('code', 'not like', 'GST-%');
                           });
                       });
                });

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
                    'work_order_id' => $instWoMap[$inst->id] ?? null,
                    'code' => 'SPK-PSB-' . str_pad($inst->id, 4, '0', STR_PAD_LEFT),
                    'type' => 'installation',
                    'type_badge' => 'Pasang Baru (PSB)',
                    'service_type' => 'FTTH / ONT',
                    'customer_name' => $inst->customer?->name ?? $inst->customer_name,
                    'customer_code' => 'PSB #' . $inst->id,
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
                    'sla_due_at' => $inst->sla_due_at,
                    'sla_started_at' => $inst->sla_started_at ?: ($inst->scheduled_at ?: $inst->created_at),
                    'sla_status' => $inst->sla_status ?? 'normal',
                    'priority' => $inst->priority ?? 'normal',
                ]);
            }
        }

        // 2. GANGGUAN / REPAIR TICKETS
        if ($this->typeFilter === 'all' || $this->typeFilter === 'repair') {
            $tQ = Ticket::withoutGlobalScopes()
                ->whereNotIn('category', ['psb', 'installation'])
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'), 'assignedTo', 'reseller'])
                ->where(function($tq) {
                    $tq->whereNull('customer_id')
                       ->orWhereHas('customer', function($cq) {
                           $cq->withoutGlobalScopes()->where(function($sq) {
                               $sq->whereNull('code')->orWhere('code', 'not like', 'GST-%');
                           });
                       });
                });

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
                    'work_order_id' => $ticketWoMap[$ticket->id] ?? null,
                    'code' => 'SPK-TKT-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT),
                    'type' => 'repair',
                    'type_badge' => 'Gangguan (' . strtoupper($ticket->category ?? 'Internet') . ')',
                    'service_type' => 'TIKET',
                    'customer_name' => $ticket->customer?->name ?? 'Pelanggan #' . $ticket->customer_id,
                    'customer_code' => 'TIKET #' . $ticket->id,
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
                    'sla_due_at' => $ticket->sla_due_at,
                    'sla_started_at' => $ticket->sla_started_at ?: $ticket->created_at,
                    'sla_status' => $ticket->sla_status ?? 'normal',
                    'priority' => $ticket->priority ?? 'normal',
                ]);
            }
        }

        // 3. PEMELIHARAAN / MAINTENANCE JARINGAN (support_maintenances)
        if (($this->typeFilter === 'all' || $this->typeFilter === 'maintenance') && Schema::hasTable('support_maintenances')) {
            $mQ = DB::table('support_maintenances')
                ->leftJoin('users as tech_user', 'support_maintenances.assigned_to', '=', 'tech_user.id')
                ->leftJoin('customers', 'support_maintenances.customer_id', '=', 'customers.id')
                ->leftJoin('users as reseller_user', 'customers.reseller_id', '=', 'reseller_user.id')
                ->select([
                    'support_maintenances.*',
                    'tech_user.name as technician_name',
                    'tech_user.email as technician_email',
                    'customers.reseller_id as customer_reseller_id',
                    'reseller_user.name as customer_reseller_name',
                ]);

            // Jika filter 'all', sembunyikan record maintenance turunan dari tiket agar tidak muncul ganda (tiket sudah tampil di section 2)
            if ($this->typeFilter === 'all' && Schema::hasColumn('support_maintenances', 'ticket_id')) {
                $mQ->whereNull('support_maintenances.ticket_id');
            }

            if ($this->technicianFilter) {
                $mQ->where('support_maintenances.assigned_to', $this->technicianFilter);
            }

            if ($this->resellerFilter) {
                if ($this->resellerFilter === 'direct') {
                    $mQ->whereNull('customers.reseller_id');
                } else {
                    $mQ->where('customers.reseller_id', $this->resellerFilter);
                }
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
                    'work_order_id' => $maintWoMap[$maint->id] ?? null,
                    'code' => 'SPK-MNT-' . str_pad($maint->id, 4, '0', STR_PAD_LEFT),
                    'type' => 'maintenance',
                    'type_badge' => 'Maintenance (' . $typeLabel . ')',
                    'service_type' => strtoupper($maint->device_type ?? 'INFRA'),
                    'customer_name' => $maint->customer_name ?? 'Infrastruktur Jaringan',
                    'customer_code' => $maint->ticket_id ? 'TIKET #' . $maint->ticket_id : ('MT #' . $maint->id),
                    'customer_phone' => $maint->customer_phone ?? '-',
                    'customer_address' => $maint->customer_address ?? ($maint->location_name ?? 'Lokasi Perangkat'),
                    'technician_name' => $maint->technician_name ?? 'Teknisi Jaringan',
                    'reseller_id' => $maint->customer_reseller_id,
                    'reseller_name' => $maint->customer_reseller_name ?: ($maint->customer_reseller_id ? 'Reseller #' . $maint->customer_reseller_id : null),
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
                ->with(['customer' => fn($q) => $q->withoutGlobalScopes()->with('reseller'), 'onu.olt', 'serviceProfile', 'service', 'createdBy', 'reseller'])
                ->where(function($cq) {
                    $cq->whereNull('customer_id')
                       ->orWhereHas('customer', function($scq) {
                           $scq->withoutGlobalScopes()->where(function($sq) {
                               $sq->whereNull('code')->orWhere('code', 'not like', 'GST-%');
                           });
                       });
                });

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
            'pendingQcCount' => $this->pendingQcCount,
            'selectedMonthName' => $selectedMonthDate->translatedFormat('F Y'),
        ]);
    }
}
