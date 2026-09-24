<?php

namespace App\Jobs\Workforce;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

readonly class AssignTechnicianJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $workOrderId,
        public string $technicianId,
        public ?string $dispatcherId = null,
    ) {}

    public function handle(
        \App\Services\Workforce\AssignmentService $assignmentService,
        \App\Services\Workforce\TechnicianService $technicianService,
    ): void {
        $assignmentService->createAssignment(
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->workOrderId),
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->technicianId),
            $this->dispatcherId ? \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->dispatcherId) : null,
        );
    }
}
