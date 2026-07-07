<?php

namespace App\Services\ISP;

use App\Models\ISP\Olt;
use App\Models\ISP\PonPort;

class OltManagementService
{
    public function createOltWithPonPorts(array $data, array $portsData, $user): Olt
    {
        return \DB::transaction(function () use ($data, $portsData, $user) {
            $oltService = app(OltService::class);
            $olt = $oltService->create($data, $user);

            $ponPortService = app(PonPortService::class);
            foreach ($portsData as $portData) {
                $portData['olt_id'] = $olt->id;
                $ponPortService->create($portData, $user);
            }

            return $olt->load('ponPorts');
        });
    }

    public function getAvailablePonPorts(Olt $olt): \Illuminate\Database\Eloquent\Collection
    {
        return $olt->ponPorts()
            ->whereDoesntHave('onus')
            ->where('status', 'active')
            ->get();
    }
}
