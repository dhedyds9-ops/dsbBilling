<?php

namespace App\Repositories\AAA;

use App\Models\AAA\HotspotUser;
use App\Repositories\BaseRepository;

class HotspotUserRepository extends BaseRepository
{
    public function __construct(HotspotUser $model)
    {
        parent::__construct($model);
    }
}
