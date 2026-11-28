<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use App\Models\CRM\Installation;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use App\Models\Workforce\WorkOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.technician-app')]
class Show extends Component
{
    use WithFileUploads;

    public $jobId;
    public array $breadcrumbs = [];

    public string $sourceType = 'work_order';
    public $sourceModel = null;
    public ?array $jobData = null;

    // Form penyelesaian
    public bool $showCompleteModal = false;
    public string $completion_notes = '';
    public string $optical_power = '';

    public function mount($job = null, $id = null)
    {
        $this->jobId = $job ?? $id;
        $this->loadJob();

        $title = $this->jobData['title'] ?? ('#' . $this->jobId);
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => route('technician.dashboard')],
            ['label' => 'Tugas Saya', 'url' => route('technician.my-jobs.index')],
            ['label' => $title, 'url' => '#'],
        ];
    }

    public function loadJob(): void
    {
        $id = $this->jobId;

        // 1. Cek apakah format ID membawa prefix: inst_123, ticket_123, maint_123, wo_123
        $prefix = '';
        $rawId = $id;
        if (is_string($id) && str_contains($id, '_')) {
            [$prefix, $rawId] = explode('_', $id, 2);
        }

        // 2. Cari di WorkOrder terlebih dahulu
        $wo = WorkOrder::withoutGlobalScopes()
            ->with(['customer', 'technician', 'installation', 'ticket', 'branch', 'checklists', 'materialUsages'])
            ->where(function ($q) use ($id, $rawId, $prefix) {
                $q->where('id', $rawId)
                  ->orWhere('uuid', $id);
                if ($prefix === 'wo') {
                    $q->orWhere('id', $rawId);
                }
            })
            ->first();

        if ($wo) {
            $this->authorizeJob($wo->assigned_to, $wo->technician_ids, $wo->branch_id);
            $this->sourceType = 'work_order';
            $this->sourceModel = $wo;
            $this->jobData = $this->formatWorkOrderJob($wo);
            return;
        }

        // 3. Cari di Installation jika prefix 'inst' atau id numerik
        if ($prefix === 'inst' || is_numeric($rawId)) {
            $inst = Installation::withoutGlobalScopes()
                ->with(['customer.reseller', 'survey.prospect', 'onu.olt', 'package', 'branch'])
                ->where('id', $rawId)
                ->first();

            if ($inst) {
                $associatedWo = WorkOrder::withoutGlobalScopes()->where('installation_id', $inst->id)->first();
                $this->authorizeJob($inst->assigned_to ?: $inst->technician_id, $inst->technician_ids, $inst->branch_id);
                $this->sourceType = 'installation';
                $this->sourceModel = $inst;
                $this->jobData = $this->formatInstallationJob($inst, $associatedWo);
                return;
            }
        }

        // 4. Cari di Ticket jika prefix 'ticket' atau id numerik
        if ($prefix === 'ticket' || is_numeric($rawId)) {
            $ticket = Ticket::withoutGlobalScopes()
                ->with(['customer.reseller', 'branch'])
                ->where('id', $rawId)
                ->first();

            if ($ticket) {
                $associatedWo = WorkOrder::withoutGlobalScopes()->where('ticket_id', $ticket->id)->first();
                $this->authorizeJob($ticket->assigned_to, $ticket->technician_ids, $ticket->branch_id);
                $this->sourceType = 'ticket';
                $this->sourceModel = $ticket;
                $this->jobData = $this->formatTicketJob($ticket, $associatedWo);
                return;
            }
        }

        // 5. Cari di SupportMaintenance jika prefix 'maint' atau id numerik
        if ($prefix === 'maint' || is_numeric($rawId)) {
            $maint = SupportMaintenance::withoutGlobalScopes()
                ->with(['technician', 'branch'])
                ->where('id', $rawId)
                ->first();

            if ($maint) {
                $this->authorizeJob($maint->technician_id, null, $maint->branch_id);
                $this->sourceType = 'maintenance';
                $this->sourceModel = $maint;
                $this->jobData = $this->formatMaintenanceJob($maint);
                return;
            }
        }

        abort(404, 'Data pekerjaan lapangan #' . $this->jobId . ' tidak ditemukan.');
    }

    protected function authorizeJob($assignedTo, $technicianIds = null, $branchId = null): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->hasRole(['administrator', 'superadmin', 'admin', 'manager', 'owner', 'root', 'noc', 'helpdesk'])) {
            return;
        }

        $techIds = is_array($technicianIds) ? $technicianIds : [];
        $isAssigned = ($assignedTo && (int)$assignedTo === (int)$user->id) || in_array((int)$user->id, array_map('intval', $techIds), true);
        $sameBranch = $branchId && (int)$branchId === (int)$user->branch_id;

        if (!$isAssigned && !(!$assignedTo && $sameBranch)) {
            abort(403, 'Anda tidak memiliki hak akses untuk pekerjaan ini.');
        }
    }

    public function startJob(): void
    {
        $this->loadJob();
        $user = Auth::user();

        if (empty($this->jobData['can_start'])) {
            session()->flash('error', 'Pekerjaan ini tidak dalam status untuk dapat dimulai.');
            return;
        }

        DB::transaction(function () use ($user) {
            $now = now();

            if ($this->sourceType === 'work_order') {
                /** @var WorkOrder $wo */
                $wo = $this->sourceModel;
                $wo->update([
                    'status' => 'in_progress',
                    'started_at' => $wo->started_at ?: $now,
                    'assigned_to' => $wo->assigned_to ?: $user->id,
                ]);

                if ($wo->installation_id) {
                    Installation::withoutGlobalScopes()
                        ->where('id', $wo->installation_id)
                        ->update(['status' => 'in_progress']);
                }
                if ($wo->ticket_id) {
                    Ticket::withoutGlobalScopes()
                        ->where('id', $wo->ticket_id)
                        ->update(['status' => 'in_progress']);
                }
            } elseif ($this->sourceType === 'installation') {
                /** @var Installation $inst */
                $inst = $this->sourceModel;
                $inst->update([
                    'status' => 'in_progress',
                    'technician_id' => $inst->technician_id ?: $user->id,
                    'assigned_to' => $inst->assigned_to ?: $user->id,
                ]);

                WorkOrder::withoutGlobalScopes()
                    ->where('installation_id', $inst->id)
                    ->update([
                        'status' => 'in_progress',
                        'started_at' => $now,
                    ]);
            } elseif ($this->sourceType === 'ticket') {
                /** @var Ticket $ticket */
                $ticket = $this->sourceModel;
                $ticket->update([
                    'status' => 'in_progress',
                    'assigned_to' => $ticket->assigned_to ?: $user->id,
                ]);

                WorkOrder::withoutGlobalScopes()
                    ->where('ticket_id', $ticket->id)
                    ->update([
                        'status' => 'in_progress',
                        'started_at' => $now,
                    ]);

                // Sinkronkan status Work Order Maintenance terkait
                if (class_exists(SupportMaintenance::class)) {
                    try {
                        SupportMaintenance::withoutGlobalScopes()
                            ->where(function ($q) use ($ticket) {
                                $q->where('ticket_id', $ticket->id);
                                if (!empty($ticket->maintenance_wo_id)) {
                                    $q->orWhere('id', $ticket->maintenance_wo_id);
                                }
                            })
                            ->where('status', 'scheduled')
                            ->update([
                                'status' => 'in_progress',
                                'technician_id' => $user->id,
                                'started_at' => $now,
                            ]);
                    } catch (\Throwable) {}
                }
            } elseif ($this->sourceType === 'maintenance') {
                /** @var SupportMaintenance $maint */
                $maint = $this->sourceModel;
                $maint->update([
                    'status' => 'in_progress',
                    'technician_id' => $maint->technician_id ?: $user->id,
                    'started_at' => $maint->started_at ?: $now,
                ]);

                // Sinkronkan status tiket jika maintenance ini terikat tiket
                if (!empty($maint->ticket_id)) {
                    try {
                        Ticket::withoutGlobalScopes()
                            ->where('id', $maint->ticket_id)
                            ->where('status', 'open')
                            ->update(['status' => 'in_progress']);
                    } catch (\Throwable) {}
                }
            }
        });

        // Dispatch Field Service Operational Event
        if ($this->sourceType === 'work_order') {
            \App\Events\FieldService\WorkOrderOperationalEvent::dispatch('started', $this->sourceModel, $user->id);
        } elseif ($this->sourceType === 'installation') {
            \App\Events\FieldService\InstallationOperationalEvent::dispatch('completed', $this->sourceModel, $user->id);
        } elseif ($this->sourceType === 'maintenance') {
            \App\Events\FieldService\MaintenanceOperationalEvent::dispatch('started', $this->sourceModel, $user->id);
        }

        session()->flash('success', 'Pekerjaan berhasil dimulai! Status saat ini: Dalam Pengerjaan.');
        $this->loadJob();
    }

    public function openCompleteModal(): void
    {
        $this->showCompleteModal = true;
    }

    public function closeCompleteModal(): void
    {
        $this->showCompleteModal = false;
    }

    public function completeJob(): void
    {
        $this->loadJob();
        $user = Auth::user();

        if (empty($this->jobData['can_complete'])) {
            session()->flash('error', 'Pekerjaan harus dalam status pengerjaan (In Progress) sebelum dapat diselesaikan.');
            return;
        }

        $this->validate([
            'completion_notes' => 'nullable|string|max:1000',
            'optical_power' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($user) {
            $now = now();
            $optRx = is_numeric($this->optical_power) ? (float) $this->optical_power : null;

            if ($this->sourceType === 'work_order') {
                /** @var WorkOrder $wo */
                $wo = $this->sourceModel;
                $wo->update([
                    'status' => 'completed',
                    'completed_at' => $now,
                    'notes' => $this->completion_notes ?: $wo->notes,
                    'optical_rx_power' => $optRx ?: $wo->optical_rx_power,
                ]);

                if ($wo->installation_id) {
                    $updateData = [
                        'status' => 'completed',
                        'completed_at' => $now,
                        'completed_by' => $user->id,
                    ];
                    if ($this->optical_power) {
                        $updateData['optical_power'] = $this->optical_power;
                    }
                    if ($this->completion_notes) {
                        $updateData['notes'] = $this->completion_notes;
                    }
                    Installation::withoutGlobalScopes()
                        ->where('id', $wo->installation_id)
                        ->update($updateData);
                }

                if ($wo->ticket_id) {
                    Ticket::withoutGlobalScopes()
                        ->where('id', $wo->ticket_id)
                        ->update([
                            'status' => 'resolved',
                            'resolved_at' => $now,
                            'resolved_by' => $user->id,
                            'resolution_notes' => $this->completion_notes ?: 'Diselesaikan oleh teknisi ' . $user->name,
                        ]);
                }
            } elseif ($this->sourceType === 'installation') {
                /** @var Installation $inst */
                $inst = $this->sourceModel;
                $updateData = [
                    'status' => 'completed',
                    'completed_at' => $now,
                    'completed_by' => $user->id,
                ];
                if ($this->optical_power) {
                    $updateData['optical_power'] = $this->optical_power;
                }
                if ($this->completion_notes) {
                    $updateData['notes'] = $this->completion_notes;
                }
                $inst->update($updateData);

                WorkOrder::withoutGlobalScopes()
                    ->where('installation_id', $inst->id)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => $now,
                        'optical_rx_power' => $optRx,
                        'notes' => $this->completion_notes ?: null,
                    ]);
            } elseif ($this->sourceType === 'ticket') {
                /** @var Ticket $ticket */
                $ticket = $this->sourceModel;
                $ticket->update([
                    'status' => 'resolved',
                    'resolved_at' => $now,
                    'resolved_by' => $user->id,
                    'resolution_notes' => $this->completion_notes ?: 'Diselesaikan oleh teknisi ' . $user->name,
                ]);

                WorkOrder::withoutGlobalScopes()
                    ->where('ticket_id', $ticket->id)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => $now,
                        'notes' => $this->completion_notes ?: null,
                    ]);

                // Sinkronkan penyelesaian ke Work Order Maintenance terkait
                if (class_exists(SupportMaintenance::class)) {
                    try {
                        SupportMaintenance::withoutGlobalScopes()
                            ->where(function ($q) use ($ticket) {
                                $q->where('ticket_id', $ticket->id);
                                if (!empty($ticket->maintenance_wo_id)) {
                                    $q->orWhere('id', $ticket->maintenance_wo_id);
                                }
                            })
                            ->update([
                                'status' => 'completed',
                                'completed_at' => $now,
                                'resolution_notes' => $this->completion_notes ?: 'Diselesaikan melalui tiket oleh ' . $user->name,
                                'updated_by' => $user->id,
                            ]);
                    } catch (\Throwable) {}
                }
            } elseif ($this->sourceType === 'maintenance') {
                /** @var SupportMaintenance $maint */
                $maint = $this->sourceModel;
                $maint->update([
                    'status' => 'completed',
                    'completed_at' => $now,
                    'resolution_notes' => $this->completion_notes ?: 'Maintenance diselesaikan oleh teknisi ' . $user->name,
                ]);

                // Sinkronkan penyelesaian ke Tiket terkait jika ada
                if (!empty($maint->ticket_id)) {
                    try {
                        $linkedTicket = Ticket::withoutGlobalScopes()->find($maint->ticket_id);
                        if ($linkedTicket && !in_array($linkedTicket->status, ['resolved', 'closed'])) {
                            $linkedTicket->update([
                                'status' => 'resolved',
                                'resolved_at' => $now,
                                'resolved_by' => $user->id,
                                'resolution_notes' => $this->completion_notes ?: 'Diselesaikan melalui maintenance oleh ' . $user->name,
                            ]);

                            \Illuminate\Support\Facades\Event::dispatch(
                                \Src\Domain\Support\Events\TicketResolvedEvent::create(
                                    (string) $linkedTicket->id,
                                    (string) $user->id,
                                    $this->completion_notes ?: 'Diselesaikan melalui maintenance'
                                )
                            );
                        }
                    } catch (\Throwable) {}
                }
            }
        });

        // Dispatch Field Service Operational Event
        if ($this->sourceType === 'work_order') {
            \App\Events\FieldService\WorkOrderOperationalEvent::dispatch('completed', $this->sourceModel, $user->id);
        } elseif ($this->sourceType === 'installation') {
            \App\Events\FieldService\InstallationOperationalEvent::dispatch('completed', $this->sourceModel, $user->id);
        } elseif ($this->sourceType === 'ticket') {
            \App\Events\FieldService\TicketOperationalEvent::dispatch('resolved', $this->sourceModel, $user->id);
        } elseif ($this->sourceType === 'maintenance') {
            \App\Events\FieldService\MaintenanceOperationalEvent::dispatch('completed', $this->sourceModel, $user->id);
        }

        $this->showCompleteModal = false;
        session()->flash('success', 'Pekerjaan berhasil diselesaikan!');
        $this->loadJob();
    }

    protected function formatWorkOrderJob(WorkOrder $wo): array
    {
        $status = strtolower($wo->status ?? 'pending');
        $isCompleted = in_array($status, ['completed', 'verified', 'approved']);
        $canStart = in_array($status, ['pending', 'scheduled', 'assigned']);
        $canComplete = in_array($status, ['in_progress', 'pending_approval']);

        $customer = $wo->customer;
        $custName = $customer?->name ?? 'Pelanggan #' . $wo->customer_id;
        $phone = $customer?->phone ?? '-';
        $address = $customer?->address ?? 'Alamat tidak tersedia';
        $lat = $customer?->latitude;
        $lng = $customer?->longitude;

        return [
            'id' => $wo->id,
            'uuid' => $wo->uuid ?? ('WO-' . $wo->id),
            'type' => $wo->type ?? 'work_order',
            'type_label' => strtoupper($wo->type ?: 'Work Order'),
            'badge_class' => $this->getTypeBadgeClass($wo->type),
            'title' => $wo->title ?: ('SPK: ' . $custName),
            'description' => $wo->description,
            'notes' => $wo->notes,
            'status' => $status,
            'status_label' => $this->getStatusLabel($status),
            'status_badge' => $this->getStatusBadgeClass($status),
            'priority' => $wo->priority ?? 'medium',
            'customer_name' => $custName,
            'customer_phone' => $phone,
            'customer_address' => $address,
            'customer_code' => $customer?->code ?? '-',
            'customer_id' => $wo->customer_id,
            'latitude' => $lat,
            'longitude' => $lng,
            'scheduled_at' => $wo->scheduled_at,
            'started_at' => $wo->started_at,
            'completed_at' => $wo->completed_at,
            'technician_name' => $wo->technician?->name ?? '-',
            'optical_power' => $wo->optical_rx_power ? ($wo->optical_rx_power . ' dBm') : null,
            'sla_due_at' => $wo->sla_due_at,
            'sla_started_at' => $wo->sla_started_at ?: ($wo->started_at ?: $wo->created_at),
            'sla_status' => $wo->sla_status ?? 'normal',
            'can_start' => $canStart,
            'can_complete' => $canComplete,
            'is_completed' => $isCompleted,
            'wizard_url' => $wo->installation_id ? route('technician.installation.wizard', ['installation_id' => $wo->installation_id, 'customer_id' => $wo->customer_id]) : null,
            'diagnostic_url' => $wo->customer_id ? route('technician.diagnostic.index', ['customer_id' => $wo->customer_id]) : null,
        ];
    }

    protected function formatInstallationJob(Installation $inst, ?WorkOrder $wo): array
    {
        $status = strtolower($inst->status ?? 'pending');
        $isCompleted = in_array($status, ['completed', 'verified', 'active']);
        $canStart = in_array($status, ['pending', 'scheduled', 'assigned']);
        $canComplete = in_array($status, ['in_progress']);

        $customer = $inst->customer;
        $custName = !empty($inst->customer_name) ? $inst->customer_name : ($customer?->name ?? ($inst->survey?->prospect?->name ?? 'Pelanggan #' . $inst->customer_id));
        $phone = $inst->customer_phone ?: ($customer?->phone ?? ($inst->survey?->prospect?->phone ?? '-'));
        $address = $inst->installation_address ?: ($customer?->address ?? ($inst->survey?->prospect?->address ?? 'Alamat tidak tersedia'));
        $lat = $inst->latitude ?: ($customer?->latitude ?? $inst->survey?->latitude);
        $lng = $inst->longitude ?: ($customer?->longitude ?? $inst->survey?->longitude);

        return [
            'id' => $inst->id,
            'uuid' => $inst->uuid ?? ('INST-' . $inst->id),
            'type' => 'installation',
            'type_label' => 'Instalasi Pasang Baru',
            'badge_class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300',
            'title' => 'PSB: ' . $custName,
            'description' => $inst->notes,
            'notes' => $inst->notes,
            'status' => $status,
            'status_label' => $this->getStatusLabel($status),
            'status_badge' => $this->getStatusBadgeClass($status),
            'priority' => $inst->priority ?? 'medium',
            'customer_name' => $custName,
            'customer_phone' => $phone,
            'customer_address' => $address,
            'customer_code' => $customer?->code ?? '-',
            'customer_id' => $inst->customer_id,
            'latitude' => $lat,
            'longitude' => $lng,
            'scheduled_at' => $inst->scheduled_at,
            'started_at' => $wo?->started_at,
            'completed_at' => $inst->completed_at,
            'technician_name' => $inst->technician?->name ?? ($inst->assignedTo?->name ?? '-'),
            'optical_power' => $inst->optical_power ?: null,
            'sla_due_at' => $inst->sla_due_at ?: $wo?->sla_due_at,
            'sla_started_at' => $inst->sla_started_at ?: ($inst->scheduled_at ?: $inst->created_at),
            'sla_status' => $inst->sla_status ?: ($wo?->sla_status ?? 'normal'),
            'can_start' => $canStart,
            'can_complete' => $canComplete,
            'is_completed' => $isCompleted,
            'wizard_url' => route('technician.installation.wizard', ['installation_id' => $inst->id, 'customer_id' => $inst->customer_id]),
            'diagnostic_url' => $inst->customer_id ? route('technician.diagnostic.index', ['customer_id' => $inst->customer_id]) : null,
        ];
    }

    protected function formatTicketJob(Ticket $ticket, ?WorkOrder $wo): array
    {
        $status = strtolower($ticket->status ?? 'open');
        $isCompleted = in_array($status, ['resolved', 'closed']);
        $canStart = in_array($status, ['open', 'assigned']);
        $canComplete = in_array($status, ['in_progress']);

        $customer = $ticket->customer;
        $custName = $customer?->name ?? 'Pelanggan #' . $ticket->customer_id;
        $phone = $customer?->phone ?? '-';
        $address = $customer?->address ?? 'Alamat tidak tersedia';

        return [
            'id' => $ticket->id,
            'uuid' => $ticket->ticket_number ?? ('TIKET-' . $ticket->id),
            'type' => 'ticket',
            'type_label' => 'Tiket Gangguan (' . ucfirst($ticket->category ?? 'Umum') . ')',
            'badge_class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300',
            'title' => $ticket->title ?: ($ticket->subject ?? ('Tiket #' . $ticket->id)),
            'description' => $ticket->description,
            'notes' => $ticket->resolution_notes,
            'status' => $status,
            'status_label' => $this->getStatusLabel($status),
            'status_badge' => $this->getStatusBadgeClass($status),
            'priority' => $ticket->priority ?? 'medium',
            'customer_name' => $custName,
            'customer_phone' => $phone,
            'customer_address' => $address,
            'customer_code' => $customer?->code ?? '-',
            'customer_id' => $ticket->customer_id,
            'latitude' => $customer?->latitude,
            'longitude' => $customer?->longitude,
            'scheduled_at' => $ticket->created_at,
            'started_at' => $wo?->started_at,
            'completed_at' => $ticket->resolved_at,
            'technician_name' => $ticket->assignedTo?->name ?? '-',
            'optical_power' => null,
            'sla_due_at' => $ticket->sla_due_at ?: $wo?->sla_due_at,
            'sla_started_at' => $ticket->sla_started_at ?: $ticket->created_at,
            'sla_status' => $ticket->sla_status ?: ($wo?->sla_status ?? 'normal'),
            'can_start' => $canStart,
            'can_complete' => $canComplete,
            'is_completed' => $isCompleted,
            'wizard_url' => in_array(strtolower($ticket->category ?? ''), ['psb', 'installation']) ? route('technician.installation.wizard', ['ticket' => $ticket->id, 'customer_id' => $ticket->customer_id]) : null,
            'diagnostic_url' => $ticket->customer_id ? route('technician.diagnostic.index', ['customer_id' => $ticket->customer_id]) : null,
        ];
    }

    protected function formatMaintenanceJob(SupportMaintenance $maint): array
    {
        $status = strtolower($maint->status ?? 'scheduled');
        $isCompleted = in_array($status, ['completed']);
        $canStart = in_array($status, ['scheduled', 'pending']);
        $canComplete = in_array($status, ['in_progress']);

        return [
            'id' => $maint->id,
            'uuid' => 'MAINT-' . $maint->id,
            'type' => 'maintenance',
            'type_label' => 'Maintenance Jaringan',
            'badge_class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300',
            'title' => $maint->title ?? 'Pemeliharaan Jaringan',
            'description' => $maint->description,
            'notes' => $maint->resolution_notes,
            'status' => $status,
            'status_label' => $this->getStatusLabel($status),
            'status_badge' => $this->getStatusBadgeClass($status),
            'priority' => $maint->priority ?? 'medium',
            'customer_name' => '-',
            'customer_phone' => '-',
            'customer_address' => $maint->location ?? 'Lokasi Infrastruktur',
            'customer_code' => '-',
            'customer_id' => null,
            'latitude' => null,
            'longitude' => null,
            'scheduled_at' => $maint->scheduled_date,
            'started_at' => $maint->started_at,
            'completed_at' => $maint->completed_at,
            'technician_name' => $maint->technician?->name ?? '-',
            'optical_power' => null,
            'can_start' => $canStart,
            'can_complete' => $canComplete,
            'is_completed' => $isCompleted,
            'wizard_url' => null,
            'diagnostic_url' => null,
        ];
    }

    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Penugasan',
            'open' => 'Tiket Terbuka',
            'scheduled' => 'Terjadwal',
            'assigned' => 'Ditugaskan',
            'in_progress' => 'Sedang Dikerjakan',
            'pending_approval' => 'Menunggu Verifikasi',
            'completed', 'resolved', 'verified', 'active' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'pending', 'open' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'scheduled', 'assigned' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'in_progress' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
            'pending_approval' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-950/40 dark:text-yellow-300 dark:border-yellow-800',
            'completed', 'resolved', 'verified', 'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        };
    }

    protected function getTypeBadgeClass(?string $type): string
    {
        return match (strtolower($type ?? '')) {
            'installation', 'psb' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300',
            'ticket', 'troubleshooting' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300',
            'maintenance' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public function render()
    {
        return view('livewire.isp.technician.my-jobs.show');
    }
}
