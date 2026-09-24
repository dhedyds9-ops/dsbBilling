<?php

namespace Src\Domain\Workforce;

use DateTimeImmutable;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\GeofenceType;
use Src\Domain\Workforce\ValueObjects\GPSCoordinate;

class Geofence extends AggregateRoot {
    public function __construct(
        public readonly Uuid $id,
        public string $name,
        public readonly GeofenceType $type,
        public GPSCoordinate $center,
        public ?float $radius = null, // for circle type (meters)
        /** @var GPSCoordinate[] */
        public array $vertices = [], // for polygon/rectangle
        public string $description = '',
        public bool $isActive = true,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function createCircle(
        string $name,
        GPSCoordinate $center,
        float $radius,
        string $description = '',
    ): self {
        return new self(
            Uuid::random(),
            $name,
            GeofenceType::CIRCLE,
            $center,
            $radius,
            [],
            $description
        );
    }

    public static function createPolygon(
        string $name,
        array $vertices,
        string $description = '',
    ): self {
        $center = self::calculateCenter($vertices);
        return new self(
            Uuid::random(),
            $name,
            GeofenceType::POLYGON,
            $center,
            null,
            $vertices,
            $description
        );
    }

    private static function calculateCenter(array $vertices): GPSCoordinate {
        $latSum = 0;
        $lngSum = 0;
        foreach ($vertices as $vertex) {
            $latSum += $vertex->latitude;
            $lngSum += $vertex->longitude;
        }
        $count = count($vertices);
        return new GPSCoordinate($latSum / $count, $lngSum / $count);
    }

    public function updateName(string $name): void {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDescription(string $description): void {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }
}
