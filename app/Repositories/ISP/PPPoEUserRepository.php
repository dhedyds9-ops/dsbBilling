<?php

namespace App\Repositories\ISP;

use App\Models\ISP\PPPoEUser;
use App\Repositories\BaseRepository;

class PPPoEUserRepository extends BaseRepository
{
    public function __construct(PPPoEUser $model)
    {
        parent::__construct($model);
    }
}
