<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\ProvisionPipeline;
use App\Repositories\BaseRepository;

class ProvisionPipelineRepository extends BaseRepository
{
    public function __construct(ProvisionPipeline $model)
    {
        parent::__construct($model);
    }
}
