<?php

namespace App\Repositories\Billing;

use App\Models\Billing\Subscription;
use App\Repositories\BaseRepository;

class SubscriptionRepository extends BaseRepository
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }
}
