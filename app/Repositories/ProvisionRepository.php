<?php

namespace App\Repositories;

use App\Models\ACS\ProvisionTemplate;
use App\Models\ACS\ProvisionProfile;
use App\Models\ACS\ProvisionQueue;

class ProvisionRepository extends BaseRepository
{
    public function __construct(ProvisionTemplate $model)
    {
        $this->model = $model;
    }

    public function getProfiles()
    {
        return ProvisionProfile::all();
    }

    public function getQueues()
    {
        return ProvisionQueue::with(['device', 'profile'])->get();
    }
}
