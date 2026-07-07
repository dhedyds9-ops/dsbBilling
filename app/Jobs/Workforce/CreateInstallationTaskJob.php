<?php

namespace App\Jobs\Workforce;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

readonly class CreateInstallationTaskJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $workOrderId,
        public string $assignmentId,
        public string $customerId,
        public ?string $notes = null,
    ) {}

    public function handle(
        \App\Services\Workforce\InstallationTaskService $taskService,
    ): void {
        $taskService->createInstallationTask(
            Uuid::fromString($this->workOrderId),
            Uuid::fromString($this->assignmentId),
            Uuid::fromString($this->customerId),
            $this->notes,
        );
    }
}
