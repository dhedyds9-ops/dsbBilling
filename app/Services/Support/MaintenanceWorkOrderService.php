<?php

namespace App\Services\Support;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Support\SupportMaintenance;
use App\Models\Support\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Support\Events\TicketResolvedEvent;

class MaintenanceWorkOrderService
{
    /**
     * Tentukan apakah tiket membutuhkan kunjungan teknisi ke lapangan.
     */
    public function requiresFieldVisit(Ticket $ticket): bool
    {
        if ($ticket->requires_field_visit) {
            return true;
        }

        $category = strtolower(trim((string) $ticket->category));
        $fieldCategories = [
            'internet', 'los', 'hardware', 'cable', 'maintenance', 
            'installation', 'psb', 'optical', 'gangguan', 'kabel_putus', 'ont_rusak'
        ];

        return in_array($category, $fieldCategories, true);
    }

    /**
     * Sinkronkan Work Order Maintenance dari Tiket secara otomatis (Idempotent).
     * CS cukup input 1x di menu Ticket, sistem mengelola WO di backend.
     */
    public function syncFromTicket(Ticket $ticket, int $userId): ?SupportMaintenance
    {
        if (!$ticket->assigned_to || !$this->requiresFieldVisit($ticket)) {
            return null;
        }

        return DB::transaction(function () use ($ticket, $userId) {
            $customer = $ticket->customer;
            $customerService = null;
            if ($customer) {
                $customerService = CustomerService::withoutGlobalScopes()
                    ->with('onu')
                    ->where('customer_id', $customer->id)
                    ->latest()
                    ->first();
            }

            $deviceName = $customerService?->onu?->serial_number 
                ?? $customerService?->username 
                ?? ($customer?->code ? 'ONT-' . $customer->code : 'ONT Pelanggan');

            $existingWo = SupportMaintenance::withoutGlobalScopes()
                ->where('ticket_id', $ticket->id)
                ->first();

            $scheduledDate = $ticket->due_date ? $ticket->due_date->toDateString() : now()->toDateString();

            if ($existingWo) {
                // Update penugasan tanpa membuat duplikat
                $existingWo->update([
                    'assigned_to' => $ticket->assigned_to,
                    'priority' => $ticket->priority ?? $existingWo->priority,
                    'customer_id' => $ticket->customer_id ?? $existingWo->customer_id,
                    'customer_name' => $customer?->name ?? $existingWo->customer_name,
                    'customer_phone' => $customer?->phone ?? $existingWo->customer_phone,
                    'customer_address' => $customer?->address ?? $existingWo->customer_address,
                    'scheduled_date' => $scheduledDate,
                    'notes' => $ticket->description ?: $existingWo->notes,
                    'updated_by' => $userId,
                ]);

                if (!$ticket->maintenance_wo_id || $ticket->maintenance_wo_id !== $existingWo->id) {
                    $ticket->update([
                        'maintenance_wo_id' => $existingWo->id,
                        'requires_field_visit' => true,
                    ]);
                }

                Log::info("MaintenanceWorkOrderService: Updated WO #{$existingWo->id} for Ticket #{$ticket->id}");
                return $existingWo;
            }

            // Buat Work Order Maintenance baru yang terikat dengan Tiket
            $wo = SupportMaintenance::create([
                'ticket_id' => $ticket->id,
                'customer_id' => $ticket->customer_id,
                'customer_name' => $customer?->name ?? 'Pelanggan #' . $ticket->customer_id,
                'customer_phone' => $customer?->phone,
                'customer_address' => $customer?->address,
                'branch_id' => $customer?->branch_id ?? auth()->user()?->branch_id,
                'title' => 'Perbaikan Gangguan: ' . $ticket->title,
                'type' => 'corrective',
                'source' => 'ticket',
                'status' => 'scheduled',
                'priority' => $ticket->priority ?? 'medium',
                'device_type' => 'onu',
                'device_name' => $deviceName,
                'location_name' => $customer?->address ?? 'Lokasi Pelanggan',
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => '09:00',
                'assigned_to' => $ticket->assigned_to,
                'notes' => $ticket->description ?? '',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $ticket->update([
                'maintenance_wo_id' => $wo->id,
                'requires_field_visit' => true,
            ]);

            Log::info("MaintenanceWorkOrderService: Created auto-WO #{$wo->id} for Ticket #{$ticket->id}");
            return $wo;
        });
    }

    /**
     * Ketika teknisi menyelesaikan maintenance di lapangan, sinkronkan ke tiket terkait.
     */
    public function resolveMaintenanceAndTicket(int $maintenanceId, ?string $resolutionNotes, int $userId): void
    {
        DB::transaction(function () use ($maintenanceId, $resolutionNotes, $userId) {
            $wo = SupportMaintenance::findOrFail($maintenanceId);
            $wo->update([
                'status' => 'completed',
                'completed_at' => now(),
                'resolution_notes' => $resolutionNotes,
                'updated_by' => $userId,
            ]);

            if ($wo->ticket_id) {
                $ticket = Ticket::find($wo->ticket_id);
                if ($ticket && !in_array($ticket->status, ['resolved', 'closed'])) {
                    $ticket->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                        'resolution_notes' => $resolutionNotes ?: $ticket->resolution_notes,
                    ]);

                    \Illuminate\Support\Facades\Event::dispatch(
                        TicketResolvedEvent::create(
                            (string) $ticket->id,
                            (string) $ticket->assigned_to,
                            (string) $userId,
                            $resolutionNotes ?? 'Diselesaikan melalui pekerjaan lapangan'
                        )
                    );
                    Log::info("MaintenanceWorkOrderService: Auto-resolved Ticket #{$ticket->id} from WO #{$wo->id}");
                }
            }
        });
    }

    /**
     * Jika tiket dibatalkan, batalkan juga WO lapangan yang belum selesai.
     */
    public function cancelMaintenanceForTicket(Ticket $ticket, int $userId): void
    {
        $wos = SupportMaintenance::withoutGlobalScopes()
            ->where('ticket_id', $ticket->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        foreach ($wos as $wo) {
            $wo->update([
                'status' => 'cancelled',
                'notes' => ($wo->notes ? $wo->notes . "\n" : '') . '[DIBATALKAN KARENA TIKET DIBATALKAN]',
                'updated_by' => $userId,
            ]);
            Log::info("MaintenanceWorkOrderService: Cancelled WO #{$wo->id} due to Ticket #{$ticket->id} cancellation");
        }
    }
}
