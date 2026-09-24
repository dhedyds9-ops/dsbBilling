<?php

namespace App\Repositories;

use App\Models\ACS\ACSAlarm;

class AlarmRepository extends BaseRepository
{
    public function __construct(ACSAlarm $model)
    {
        $this->model = $model;
    }
}
