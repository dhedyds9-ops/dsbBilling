<?php

namespace App\Repositories\ISP;

use App\Models\ISP\HotspotUser;
use App\Repositories\BaseRepository;

class HotspotUserRepository extends BaseRepository
{
    public function __construct(HotspotUser $model)
    {
        parent::__construct($model);
    }
}
