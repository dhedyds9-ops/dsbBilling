<?php

namespace App\Repositories;

use App\Models\ACS\ACSDevice;

class ACSDeviceRepository extends BaseRepository
{
    public function __construct(ACSDevice $model)
    {
        $this->model = $model;
    }
}
