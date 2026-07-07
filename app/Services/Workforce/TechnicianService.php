<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\TechnicianRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\TechnicianStatus;
use Src\Domain\Workforce\Technician;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

readonly class TechnicianService {
    public function __construct(
        private TechnicianRepository $technicianRepository,
    ) {}

    public function createTechnician(
        Uuid $userId,
        string $name,
        ?string $phone = null,
        ?string $email = null,
        ?string $employeeId = null,
    ): Technician {
        $technician = Technician::create($userId, $name, $phone, $email, $employeeId);
        return $this->technicianRepository->save($technician);
    }

    public function updateTechnicianStatus(Uuid $technicianId, TechnicianStatus $status): Technician {
        $technician = $this->technicianRepository->findById($technicianId);
        if (!$technician) throw new \InvalidArgumentException("Technician not found");
        
        $technician->updateStatus($status);
        $this->technicianRepository->save($technician);
        return $technician;
    }

    public function updateTechnicianLocation(Uuid $technicianId, GPSCoordinate $coordinate): Technician {
        $technician = $this->technicianRepository->findById($technicianId);
        if (!$technician) throw new \InvalidArgumentException("Technician not found");
        
        $technician->updateLocation($coordinate);
        $this->technicianRepository->save($technician);
        return $technician;
    }
}
