<?php

namespace App\Repositories;

use App\Models\ACS\ConfigurationProfile;

class ConfigurationRepository extends BaseRepository
{
    public function __construct(ConfigurationProfile $model)
    {
        $this->model = $model;
    }
}
