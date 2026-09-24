<?php

namespace App\Jobs\Workforce;

use App\Services\Workforce\PhotoDocumentationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class UploadPhotoJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $taskId,
        public readonly string $photoPath,
        public readonly string $description,
        public readonly ?string $latitude = null,
        public readonly ?string $longitude = null,
    ) {}

    public function handle(PhotoDocumentationService $service): void {
        $service->uploadPhoto(
            Uuid::fromString($this->taskId),
            $this->photoPath,
            $this->description,
            $this->latitude,
            $this->longitude,
        );
    }
}
