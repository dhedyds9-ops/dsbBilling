<?php

namespace App\Services\ISP;

use App\Models\ISP\Onu;
use App\Models\ISP\OnuPort;

class OnuManagementService
{
    public function createOnuWithPorts(array $data, array $portsData, $user): Onu
    {
        return \DB::transaction(function () use ($data, $portsData, $user) {
            $onuService = app(OnuService::class);
            $onu = $onuService->create($data, $user);

            $onuPortService = app(OnuPortService::class);
            foreach ($portsData as $portData) {
                $portData['onu_id'] = $onu->id;
                $onuPortService->create($portData, $user);
            }

            return $onu->load('onuPorts');
        });
    }
}
