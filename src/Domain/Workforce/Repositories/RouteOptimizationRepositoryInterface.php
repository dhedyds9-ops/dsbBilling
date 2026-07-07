<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\RouteOptimization;

interface RouteOptimizationRepositoryInterface {
    public function save(RouteOptimization $routeOptimization): RouteOptimization;
    public function findById(Uuid $id): ?RouteOptimization;
    public function findByTechnicianId(Uuid $technicianId, ?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): array;
}
