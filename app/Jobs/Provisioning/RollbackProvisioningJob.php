<?php

namespace App\Jobs\Provisioning;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Provisioning\ResourceReservationRepositoryInterface;
use Src\Domain\Provisioning\ServiceInstanceRepositoryInterface;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RollbackProvisioningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $serviceInstanceId)
    {
    }

    public function handle(
        ServiceInstanceRepositoryInterface $instanceRepository,
        ResourceReservationRepositoryInterface $reservationRepository,
        EventDispatcherInterface $dispatcher
    ): void {
        $instanceId = Uuid::fromString($this->serviceInstanceId);
        $instance = $instanceRepository->findById($instanceId);

        if (!$instance) {
            return;
        }

        $dispatcher->dispatch(new \Src\Domain\Provisioning\Events\RollbackStartedEvent($instanceId));

        // Rollback logic: release all resources
        $reservations = $reservationRepository->findByServiceInstanceId($instanceId);
        foreach ($reservations as $reservation) {
            $reservation->release();
            $reservationRepository->save($reservation);
        }

        $instance->cancel();
        $instanceRepository->save($instance);

        $dispatcher->dispatch(new \Src\Domain\Provisioning\Events\RollbackCompletedEvent($instanceId));
    }

    public function failed(\Throwable $exception): void
    {
        // Handle failure
    }
}
