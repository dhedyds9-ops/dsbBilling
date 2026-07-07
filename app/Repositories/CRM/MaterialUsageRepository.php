<?php

namespace App\Repositories\CRM;

use App\Models\CRM\MaterialUsage;
use App\Repositories\BaseRepository;

class MaterialUsageRepository extends BaseRepository
{
    public function __construct(MaterialUsage $model)
    {
        parent::__construct($model);
    }
}
