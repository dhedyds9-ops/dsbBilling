<?php

namespace App\Repositories\CRM;

use App\Models\CRM\CustomerActivation;
use App\Repositories\BaseRepository;

class CustomerActivationRepository extends BaseRepository
{
    public function __construct(CustomerActivation $model)
    {
        parent::__construct($model);
    }
}
