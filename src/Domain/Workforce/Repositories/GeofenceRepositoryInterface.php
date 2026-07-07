<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Geofence;

interface GeofenceRepositoryInterface {
    public function save(Geofence $geofence): Geofence;
    public function findById(Uuid $id): ?Geofence;
    public function findAll(): array;
    public function findAllActive(): array;
    public function delete(Uuid $id): void;
}
