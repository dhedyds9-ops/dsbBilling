<?php

namespace App\Services\Provisioning;

use App\Models\Provisioning\ResourceReservation;
use App\Models\Provisioning\ServiceInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Domain\Provisioning\Events\ResourcesReservedEvent;
use Src\Domain\Provisioning\Events\ResourceAllocatedEvent;
use Src\Domain\Provisioning\Events\ResourcesReleasedEvent;

class ResourceReservationService
{
    public function reserveResources(
        int $serviceInstanceId,
        array $resources,
        int $userId
    ): array {
        return DB::transaction(function () use ($serviceInstanceId, $resources, $userId) {
            $reservations = [];

            foreach ($resources as $resource) {
                $reservation = ResourceReservation::create([
                    'uuid' => (string)Str::uuid(),
                    'service_instance_id' => $serviceInstanceId,
                    'resource_type' => $resource['type'],
                    'resource_id' => $resource['id'],
                    'status' => 'reserved',
                    'reserved_at' => now(),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $reservations[] = $reservation;
            }

            Event::dispatch(new ResourcesReservedEvent(
                ServiceInstance::find($serviceInstanceId)->uuid
            ));

            return $reservations;
        });
    }

    public function allocateResource(int $reservationId, int $userId): ResourceReservation
    {
        return DB::transaction(function () use ($reservationId, $userId) {
            $reservation = ResourceReservation::findOrFail($reservationId);
            $reservation->update([
                'status' => 'allocated',
                'allocated_at' => now(),
                'updated_by' => $userId,
            ]);

            Event::dispatch(new ResourceAllocatedEvent(
                $reservation->uuid,
                $reservation->service_instance_id,
                $reservation->resource_type,
                $reservation->resource_id
            ));

            return $reservation;
        });
    }

    public function releaseResources(int $serviceInstanceId, int $userId): void
    {
        DB::transaction(function () use ($serviceInstanceId, $userId) {
            ResourceReservation::where('service_instance_id', $serviceInstanceId)
                ->whereIn('status', ['reserved', 'allocated'])
                ->update([
                    'status' => 'released',
                    'released_at' => now(),
                    'updated_by' => $userId,
                ]);

            Event::dispatch(new ResourcesReleasedEvent(
                ServiceInstance::find($serviceInstanceId)->uuid
            ));
        });
    }

    public function expireReservation(int $reservationId, int $userId): ResourceReservation
    {
        return DB::transaction(function () use ($reservationId, $userId) {
            $reservation = ResourceReservation::findOrFail($reservationId);
            $reservation->update([
                'status' => 'expired',
                'expired_at' => now(),
                'updated_by' => $userId,
            ]);

            return $reservation;
        });
    }
}
