<?php

namespace App\Jobs\Workforce;

use App\Services\Workforce\DigitalSignatureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class SignTaskJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $taskId,
        public readonly string $signerId,
        public readonly string $signerName,
        public readonly string $signaturePath,
    ) {}

    public function handle(DigitalSignatureService $service): void {
        $service->signTask(
            Uuid::fromString($this->taskId),
            Uuid::fromString($this->signerId),
            $this->signerName,
            $this->signaturePath,
        );
    }
}
