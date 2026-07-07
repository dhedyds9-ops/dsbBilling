<?php

namespace App\Jobs\Workforce;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

readonly class UpdateLocationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $technicianId,
        public float $latitude,
        public float $longitude,
        public ?float $accuracy = null,
    ) {}

    public function handle(\App\Services\Workforce\TechnicianService $service): void {
        $service->updateTechnicianLocation(
            \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->technicianId),
            new GPSCoordinate($this->latitude, $this->longitude, $this->accuracy),
        );
    }
}
