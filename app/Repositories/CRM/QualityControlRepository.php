<?php

namespace App\Repositories\CRM;

use App\Models\CRM\QualityControl;
use App\Repositories\BaseRepository;

class QualityControlRepository extends BaseRepository
{
    public function __construct(QualityControl $model)
    {
        parent::__construct($model);
    }
}
