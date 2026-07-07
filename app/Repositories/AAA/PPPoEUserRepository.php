<?php

namespace App\Repositories\AAA;

use App\Models\AAA\PPPoEUser;
use App\Repositories\BaseRepository;

class PPPoEUserRepository extends BaseRepository
{
    public function __construct(PPPoEUser $model)
    {
        parent::__construct($model);
    }
}
