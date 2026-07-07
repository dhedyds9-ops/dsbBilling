<?php

namespace App\Jobs\Workforce;

use App\Services\Workforce\TrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class UpdateGPSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $technicianId,
        protected float $latitude,
        protected float $longitude,
        protected ?float $speed = null,
        protected ?float $heading = null,
        protected ?float $altitude = null,
    ) {
    }

    public function handle(TrackingService $trackingService): void
    {
        $trackingService->logGPSLocation(
            Uuid::fromString($this->technicianId),
            new GPSCoordinate($this->latitude, $this->longitude),
            $this->speed,
            $this->heading,
            $this->altitude
        );
    }
}
