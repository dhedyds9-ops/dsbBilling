<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ResourceReservationService
{
    public function __construct(
        private ResourceReservationRepositoryInterface $repository,
        private CapacityManagementRepositoryInterface $capacityRepository
    ) {}

    public function reserveResource(
        Uuid $serviceInstanceId,
        ResourceType $resourceType,
        Uuid $resourceId,
        ?\DateTimeImmutable $expiresAt = null
    ): ResourceReservation {
        // Check capacity
        $capacity = $this->capacityRepository->findByResource($resourceType, $resourceId);
        if (!$capacity || !$capacity->hasAvailableCapacity()) {
            throw new \RuntimeException("No available capacity for resource");
        }

        $reservation = ResourceReservation::create(
            Uuid::generate(),
            $serviceInstanceId,
            $resourceType,
            $resourceId,
            $expiresAt
        );

        $capacity->incrementReserved();
        $this->capacityRepository->save($capacity);

        $this->repository->save($reservation);
        return $reservation;
    }

    public function releaseReservation(Uuid $reservationId): void
    {
        $reservation = $this->repository->findById($reservationId);
        if (!$reservation) {
            throw new \InvalidArgumentException("Reservation not found");
        }

        $reservation->release();

        // Update capacity
        $capacity = $this->capacityRepository->findByResource($reservation->resourceType, $reservation->resourceId);
        if ($capacity) {
            $capacity->decrementReserved();
            $this->capacityRepository->save($capacity);
        }

        $this->repository->save($reservation);
    }

    public function markAsAssigned(Uuid $reservationId): ResourceAssignment
    {
        $reservation = $this->repository->findById($reservationId);
        if (!$reservation) {
            throw new \InvalidArgumentException("Reservation not found");
        }

        $reservation->markAssigned();
        $this->repository->save($reservation);

        $assignment = ResourceAssignment::create(
            Uuid::generate(),
            $reservation->serviceInstanceId,
            $reservation->resourceType,
            $reservation->resourceId,
            $reservation->id
        );

        // Update capacity
        $capacity = $this->capacityRepository->findByResource($reservation->resourceType, $reservation->resourceId);
        if ($capacity) {
            $capacity->decrementReserved();
            $capacity->incrementUsed();
            $this->capacityRepository->save($capacity);
        }

        return $assignment;
    }

    public function expireReservations(): void
    {
        $expired = $this->repository->findExpired();
        foreach ($expired as $reservation) {
            $this->releaseReservation($reservation->id);
        }
    }
}
