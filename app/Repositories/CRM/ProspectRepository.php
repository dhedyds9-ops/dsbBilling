<?php

namespace App\Repositories\CRM;

use App\Models\CRM\Prospect;
use App\Repositories\BaseRepository;

class ProspectRepository extends BaseRepository
{
    public function __construct(Prospect $model)
    {
        parent::__construct($model);
    }
}
