<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\RouteHistory;

interface RouteHistoryRepositoryInterface {
    public function save(RouteHistory $routeHistory): RouteHistory;
    public function findById(Uuid $id): ?RouteHistory;
    public function findByTechnicianId(Uuid $technicianId, ?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): array;
    public function findByAssignmentId(Uuid $assignmentId): ?RouteHistory;
}
