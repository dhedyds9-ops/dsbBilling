<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\PhotoDocumentationRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Events\PhotoUploadedEvent;
use Src\Domain\Workforce\PhotoDocumentation;

readonly class PhotoDocumentationService {
    public function __construct(
        private PhotoDocumentationRepository $photoRepository,
    ) {}

    public function uploadPhoto(
        Uuid $taskId,
        string $photoPath,
        string $description,
        ?string $latitude = null,
        ?string $longitude = null,
    ): PhotoDocumentation {
        $photo = PhotoDocumentation::create(
            $taskId,
            $photoPath,
            $description,
            $latitude,
            $longitude,
        );
        $this->photoRepository->save($photo);

        $event = PhotoUploadedEvent::create(
            $photo->id,
            $photo->taskId,
            $photo->photoPath,
        );
        Event::dispatch($event);

        return $photo;
    }

    public function updatePhotoDescription(Uuid $photoId, string $description): PhotoDocumentation {
        $photo = $this->photoRepository->findById($photoId);
        if (!$photo) throw new \InvalidArgumentException("Photo not found");
        $photo->updateDescription($description);
        return $this->photoRepository->save($photo);
    }

    public function deletePhoto(Uuid $photoId): void {
        $this->photoRepository->delete($photoId);
    }

    public function findPhotosByTaskId(Uuid $taskId): array {
        return $this->photoRepository->findByTaskId($taskId);
    }
}
