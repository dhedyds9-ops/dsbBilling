<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\GPSHistory;

interface GPSHistoryRepositoryInterface {
    public function save(GPSHistory $gpsHistory): GPSHistory;
    public function findById(Uuid $id): ?GPSHistory;
    public function findByTechnicianId(Uuid $technicianId, ?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): array;
    public function findLatestByTechnicianId(Uuid $technicianId): ?GPSHistory;
}
