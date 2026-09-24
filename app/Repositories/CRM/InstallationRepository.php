<?php

namespace App\Repositories\CRM;

use App\Models\CRM\Installation;
use App\Repositories\BaseRepository;

class InstallationRepository extends BaseRepository
{
    public function __construct(Installation $model)
    {
        parent::__construct($model);
    }
}
