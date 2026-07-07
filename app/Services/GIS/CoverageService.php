<?php

namespace App\Services\GIS;

use Illuminate\Support\Facades\DB;
use Src\Domain\GIS\CoverageArea;
use Src\Domain\GIS\Events\CoverageAreaCreated;
use Src\Domain\GIS\Events\CoverageAreaUpdated;
use Src\Domain\GIS\Events\GeoAreaCalculated;
use Src\Domain\GIS\GeoArea;
use Src\Domain\GIS\GeoPolygon;
use Src\Domain\GIS\Repositories\CoverageAreaRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoAreaRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoPolygonRepositoryInterface;
use Src\Domain\GIS\ValueObjects\Coordinate;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CoverageService
{
    public function __construct(
        protected CoverageAreaRepositoryInterface $coverageAreaRepository,
        protected GeoAreaRepositoryInterface $geoAreaRepository,
        protected GeoPolygonRepositoryInterface $geoPolygonRepository,
        protected GeoCalculationService $geoCalculationService,
    ) {}

    public function createCoverageArea(
        Uuid $parentId,
        GeoPolygon $polygon,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        $status = 'active'
    ): CoverageArea {
        return DB::transaction(function () use ($parentId, $polygon, $name, $description, $metadata, $status) {
            $this->geoPolygonRepository->save($polygon);

            $areaMetersSquared = $this->geoCalculationService->calculatePolygonArea($polygon);

            $geoArea = GeoArea::create($polygon, $areaMetersSquared, null, $name, $description, $metadata);
            $this->geoAreaRepository->save($geoArea);

            $coverageArea = CoverageArea::create($geoArea, $parentId, $status, $name, $description, $metadata);
            $this->coverageAreaRepository->save($coverageArea);

            event(CoverageAreaCreated::create($coverageArea->id, $parentId));
            event(GeoAreaCalculated::create($geoArea->id, $areaMetersSquared));

            return $coverageArea;
        });
    }

    public function checkPointInCoverage(Coordinate $point, CoverageArea $coverageArea): bool
    {
        return $coverageArea->area->contains($point);
    }

    public function updateCoverageStatus(CoverageArea $coverageArea, $status): CoverageArea
    {
        $coverageArea->updateStatus($status);
        $this->coverageAreaRepository->save($coverageArea);

        event(CoverageAreaUpdated::create($coverageArea->id));

        return $coverageArea;
    }
}
