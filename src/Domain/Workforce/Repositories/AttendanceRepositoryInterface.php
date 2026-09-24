<?php

namespace Src\Domain\Workforce\Repositories;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Attendance;

interface AttendanceRepositoryInterface {
    public function save(Attendance $attendance): Attendance;
    public function findById(Uuid $id): ?Attendance;
    public function findByTechnicianIdAndDate(Uuid $technicianId, \DateTimeImmutable $date): ?Attendance;
    public function findByTechnicianId(Uuid $technicianId, ?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): array;
}
