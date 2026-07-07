<?php

namespace App\Services\Workforce;

use App\Models\Workforce\WorkOrder;
use App\Models\Workforce\GPSLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Src\Domain\Workforce\WorkOrderStatus;

class WorkforceService
{
    public function createWorkOrder(array $data): WorkOrder
    {
        return WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'type' => $data['type'],
            'ticket_id' => $data['ticket_id'],
            'customer_id' => $data['customer_id'],
            'status' => WorkOrderStatus::PENDING->value,
            'title' => $data['title'],
            'description' => $data['description'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
        ]);
    }

    public function assignWorkOrder(int $workOrderId, int $technicianId): WorkOrder
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $workOrder->update([
            'assigned_to' => $technicianId,
            'status' => WorkOrderStatus::ASSIGNED->value,
        ]);
        return $workOrder;
    }

    public function startWorkOrder(int $workOrderId): WorkOrder
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $workOrder->update([
            'status' => WorkOrderStatus::IN_PROGRESS->value,
            'started_at' => now(),
        ]);
        return $workOrder;
    }

    public function completeWorkOrder(int $workOrderId): WorkOrder
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $workOrder->update([
            'status' => WorkOrderStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);
        return $workOrder;
    }

    public function logGPSLocation(float $latitude, float $longitude, ?float $accuracy = null): GPSLog
    {
        return GPSLog::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => Auth::id(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'logged_at' => now(),
        ]);
    }
}
