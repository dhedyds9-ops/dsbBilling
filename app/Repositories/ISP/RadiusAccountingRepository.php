<?php

namespace App\Repositories\ISP;

use App\Models\ISP\RadiusAccounting;
use App\Repositories\BaseRepository;

class RadiusAccountingRepository extends BaseRepository
{
    public function __construct(RadiusAccounting $model)
    {
        parent::__construct($model);
    }
}
