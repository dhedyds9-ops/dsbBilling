<?php

namespace App\Services\ACS;

use App\Models\ACS\ACSDevice;
use App\Repositories\ACSDeviceRepository;

class ACSService
{
    protected $acsDeviceRepository;

    public function __construct(ACSDeviceRepository $acsDeviceRepository)
    {
        $this->acsDeviceRepository = $acsDeviceRepository;
    }

    public function getAllDevices()
    {
        return $this->acsDeviceRepository->all(['*'], ['vendor', 'customerService', 'asset']);
    }

    public function getDeviceById(int $id)
    {
        return $this->acsDeviceRepository->find($id, ['*'], ['vendor', 'customerService', 'asset', 'onu', 'olt', 'pop', 'odp']);
    }

    public function createDevice(array $data)
    {
        $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $data['created_by'] = auth()->id() ?? null;
        $data['updated_by'] = auth()->id() ?? null;
        return $this->acsDeviceRepository->create($data);
    }

    public function updateDevice(int $id, array $data)
    {
        $data['updated_by'] = auth()->id() ?? null;
        return $this->acsDeviceRepository->update($id, $data);
    }

    public function deleteDevice(int $id)
    {
        return $this->acsDeviceRepository->delete($id);
    }
}
