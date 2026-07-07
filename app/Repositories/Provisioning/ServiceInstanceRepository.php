<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\ServiceInstance;
use App\Repositories\BaseRepository;

class ServiceInstanceRepository extends BaseRepository
{
    public function __construct(ServiceInstance $model)
    {
        parent::__construct($model);
    }
}
