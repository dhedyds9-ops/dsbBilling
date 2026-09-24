<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\IpAllocation;
use App\Repositories\BaseRepository;

class IpAllocationRepository extends BaseRepository
{
    public function __construct(IpAllocation $model)
    {
        parent::__construct($model);
    }
}
