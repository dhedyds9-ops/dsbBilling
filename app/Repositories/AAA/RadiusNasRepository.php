<?php

namespace App\Repositories\AAA;

use App\Models\AAA\RadiusNas;
use App\Repositories\BaseRepository;

class RadiusNasRepository extends BaseRepository
{
    public function __construct(RadiusNas $model)
    {
        parent::__construct($model);
    }
}
