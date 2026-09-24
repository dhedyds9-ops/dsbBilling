<?php

namespace App\Repositories;

use App\Models\ACS\DeviceTask;

class TaskRepository extends BaseRepository
{
    public function __construct(DeviceTask $model)
    {
        $this->model = $model;
    }
}
