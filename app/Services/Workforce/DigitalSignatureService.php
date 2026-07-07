<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\DigitalSignatureRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\DigitalSignature;
use Src\Domain\Workforce\Events\DigitalSignedEvent;

readonly class DigitalSignatureService {
    public function __construct(
        private DigitalSignatureRepository $signatureRepository,
    ) {}

    public function signTask(
        Uuid $taskId,
        Uuid $signerId,
        string $signerName,
        string $signaturePath,
    ): DigitalSignature {
        $signature = DigitalSignature::create(
            $taskId,
            $signerId,
            $signerName,
            $signaturePath,
        );
        $this->signatureRepository->save($signature);

        $event = DigitalSignedEvent::create(
            $signature->id,
            $signature->taskId,
            $signature->signerId,
        );
        Event::dispatch($event);

        return $signature;
    }

    public function findSignaturesByTaskId(Uuid $taskId): array {
        return $this->signatureRepository->findByTaskId($taskId);
    }
}
