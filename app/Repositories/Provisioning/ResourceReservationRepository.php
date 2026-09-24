<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\ResourceReservation;
use App\Repositories\BaseRepository;

class ResourceReservationRepository extends BaseRepository
{
    public function __construct(ResourceReservation $model)
    {
        parent::__construct($model);
    }
}
