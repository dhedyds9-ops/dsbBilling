<?php

namespace App\Repositories\Provisioning;

use App\Models\Provisioning\PortReservation;
use App\Repositories\BaseRepository;

class PortReservationRepository extends BaseRepository
{
    public function __construct(PortReservation $model)
    {
        parent::__construct($model);
    }
}
