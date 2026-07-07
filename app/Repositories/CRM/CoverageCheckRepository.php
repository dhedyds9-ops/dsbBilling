<?php

namespace App\Repositories\CRM;

use App\Models\CRM\CoverageCheck;
use App\Repositories\BaseRepository;

class CoverageCheckRepository extends BaseRepository
{
    public function __construct(CoverageCheck $model)
    {
        parent::__construct($model);
    }
}
