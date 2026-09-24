<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\QueueAllocation;
use App\Repositories\BaseRepository;

class QueueAllocationRepository extends BaseRepository
{
    public function __construct(QueueAllocation $model)
    {
        parent::__construct($model);
    }
}
