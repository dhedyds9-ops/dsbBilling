<?php

namespace App\Services\ACS;

use App\Models\ACS\ConfigurationProfile;
use App\Repositories\ConfigurationRepository;

class ConfigurationService
{
    protected $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getAllProfiles()
    {
        return $this->configurationRepository->all();
    }

    public function getProfileById(int $id)
    {
        return $this->configurationRepository->find($id);
    }

    public function createProfile(array $data)
    {
        $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $data['created_by'] = auth()->id() ?? null;
        $data['updated_by'] = auth()->id() ?? null;
        return $this->configurationRepository->create($data);
    }

    public function updateProfile(int $id, array $data)
    {
        $data['updated_by'] = auth()->id() ?? null;
        return $this->configurationRepository->update($id, $data);
    }

    public function deleteProfile(int $id)
    {
        return $this->configurationRepository->delete($id);
    }
}
