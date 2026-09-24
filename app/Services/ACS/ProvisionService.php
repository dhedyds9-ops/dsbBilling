<?php

namespace App\Services\ACS;

use App\Repositories\ProvisionRepository;

class ProvisionService
{
    protected $provisionRepository;

    public function __construct(ProvisionRepository $provisionRepository)
    {
        $this->provisionRepository = $provisionRepository;
    }

    public function getAllTemplates()
    {
        return $this->provisionRepository->all();
    }

    public function getAllProfiles()
    {
        return $this->provisionRepository->getProfiles();
    }

    public function getAllQueues()
    {
        return $this->provisionRepository->getQueues();
    }

    public function createTemplate(array $data)
    {
        $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $data['created_by'] = auth()->id() ?? null;
        $data['updated_by'] = auth()->id() ?? null;
        return $this->provisionRepository->create($data);
    }

    public function updateTemplate(int $id, array $data)
    {
        $data['updated_by'] = auth()->id() ?? null;
        return $this->provisionRepository->update($id, $data);
    }

    public function deleteTemplate(int $id)
    {
        return $this->provisionRepository->delete($id);
    }
}
