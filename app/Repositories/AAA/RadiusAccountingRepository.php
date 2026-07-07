<?php

namespace App\Repositories\AAA;

use App\Models\AAA\RadiusAccounting;
use App\Repositories\BaseRepository;

class RadiusAccountingRepository extends BaseRepository
{
    public function __construct(RadiusAccounting $model)
    {
        parent::__construct($model);
    }
}
