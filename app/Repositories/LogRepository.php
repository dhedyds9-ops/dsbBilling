<?php

namespace App\Repositories;

use App\Models\ACS\ACSLog;

class LogRepository extends BaseRepository
{
    public function __construct(ACSLog $model)
    {
        $this->model = $model;
    }
}
