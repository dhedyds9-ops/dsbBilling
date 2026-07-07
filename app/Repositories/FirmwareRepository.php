<?php

namespace App\Repositories;

use App\Models\ACS\Firmware;

class FirmwareRepository extends BaseRepository
{
    public function __construct(Firmware $model)
    {
        $this->model = $model;
    }
}
