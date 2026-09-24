<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\VlanAllocation;
use App\Repositories\BaseRepository;

class VlanAllocationRepository extends BaseRepository
{
    public function __construct(VlanAllocation $model)
    {
        parent::__construct($model);
    }
}
