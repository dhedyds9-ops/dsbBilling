<?php

namespace App\Jobs\Provisioning;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Provisioning\ServiceInstanceRepositoryInterface;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ProvisionRouterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $serviceInstanceId)
    {
    }

    public function handle(
        ServiceInstanceRepositoryInterface $repository,
        EventDispatcherInterface $dispatcher
    ): void {
        $instanceId = Uuid::fromString($this->serviceInstanceId);
        $instance = $repository->findById($instanceId);

        if (!$instance) {
            return;
        }

        // Actual router provisioning logic would go here
        $dispatcher->dispatch(new \Src\Domain\Provisioning\Events\RouterProvisionedEvent($instanceId));
    }

    public function failed(\Throwable $exception): void
    {
        // Handle failure
    }
}
