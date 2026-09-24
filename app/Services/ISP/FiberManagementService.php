<?php

namespace App\Services\ISP;

use App\Models\ISP\FiberCable;
use App\Models\ISP\FiberCore;
use App\Models\ISP\FiberSegment;

class FiberManagementService
{
    public function createFiberCableWithCores(array $data, array $coresData, $user): FiberCable
    {
        return \DB::transaction(function () use ($data, $coresData, $user) {
            $fiberCableService = app(FiberCableService::class);
            $fiberCable = $fiberCableService->create($data, $user);

            $fiberCoreService = app(FiberCoreService::class);
            foreach ($coresData as $coreData) {
                $coreData['fiber_cable_id'] = $fiberCable->id;
                $fiberCoreService->create($coreData, $user);
            }

            return $fiberCable->load('fiberCores');
        });
    }

    public function createFiberSegment(array $data, $user): FiberSegment
    {
        $fiberSegmentService = app(FiberSegmentService::class);
        return $fiberSegmentService->create($data, $user);
    }
}
