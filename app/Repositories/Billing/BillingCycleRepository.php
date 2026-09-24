<?php

namespace App\Repositories\Billing;

use App\Models\Billing\BillingCycle;
use App\Repositories\BaseRepository;

class BillingCycleRepository extends BaseRepository
{
    public function __construct(BillingCycle $model)
    {
        parent::__construct($model);
    }
}
