<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\ResourceAssignment;
use App\Repositories\BaseRepository;

class ResourceAssignmentRepository extends BaseRepository
{
    public function __construct(ResourceAssignment $model)
    {
        parent::__construct($model);
    }
}
