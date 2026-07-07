<?php

namespace App\Repositories\CRM;

use App\Models\CRM\Lead;
use App\Repositories\BaseRepository;

class LeadRepository extends BaseRepository
{
    public function __construct(Lead $model)
    {
        parent::__construct($model);
    }
}
