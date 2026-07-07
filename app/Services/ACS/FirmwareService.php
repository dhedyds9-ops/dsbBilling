<?php

namespace App\Services\ACS;

use App\Models\ACS\Firmware;
use App\Repositories\FirmwareRepository;

class FirmwareService
{
    protected $firmwareRepository;

    public function __construct(FirmwareRepository $firmwareRepository)
    {
        $this->firmwareRepository = $firmwareRepository;
    }

    public function getAllFirmwares()
    {
        return $this->firmwareRepository->all(['*'], ['vendor']);
    }

    public function getFirmwareById(int $id)
    {
        return $this->firmwareRepository->find($id, ['*'], ['vendor']);
    }

    public function createFirmware(array $data)
    {
        $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $data['created_by'] = auth()->id() ?? null;
        $data['updated_by'] = auth()->id() ?? null;
        return $this->firmwareRepository->create($data);
    }

    public function updateFirmware(int $id, array $data)
    {
        $data['updated_by'] = auth()->id() ?? null;
        return $this->firmwareRepository->update($id, $data);
    }

    public function deleteFirmware(int $id)
    {
        return $this->firmwareRepository->delete($id);
    }
}
