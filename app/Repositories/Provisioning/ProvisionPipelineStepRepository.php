<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\ProvisionPipelineStep;
use App\Repositories\BaseRepository;

class ProvisionPipelineStepRepository extends BaseRepository
{
    public function __construct(ProvisionPipelineStep $model)
    {
        parent::__construct($model);
    }
}
