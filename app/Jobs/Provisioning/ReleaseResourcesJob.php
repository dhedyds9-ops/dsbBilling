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

class ReleaseResourcesJob implements ShouldQueue
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

        // Release active reservations
        $reservations = $reservationRepository->findByServiceInstanceId($instanceId);
        foreach ($reservations as $reservation) {
            if ($reservation->status === \Src\Domain\Provisioning\ReservationStatus::ACTIVE) {
                $reservation->release();
                $reservationRepository->save($reservation);
            }
        }

        $instance->activate();
        $instanceRepository->save($instance);

        $dispatcher->dispatch(new \Src\Domain\Provisioning\Events\ResourcesReleasedEvent($instanceId));
        $dispatcher->dispatch(new \Src\Domain\Provisioning\Events\ProvisioningCompletedEvent($instanceId));
    }

    public function failed(\Throwable $exception): void
    {
        // Handle failure
    }
}
