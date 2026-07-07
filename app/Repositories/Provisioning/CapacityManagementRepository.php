<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\CapacityManagement;
use App\Repositories\BaseRepository;

class CapacityManagementRepository extends BaseRepository
{
    public function __construct(CapacityManagement $model)
    {
        parent::__construct($model);
    }
}
