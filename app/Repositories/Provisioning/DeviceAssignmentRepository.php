<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\DeviceAssignment;
use App\Repositories\BaseRepository;

class DeviceAssignmentRepository extends BaseRepository
{
    public function __construct(DeviceAssignment $model)
    {
        parent::__construct($model);
    }
}
