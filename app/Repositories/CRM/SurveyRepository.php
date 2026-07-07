<?php

namespace App\Repositories\CRM;

use App\Models\CRM\Survey;
use App\Repositories\BaseRepository;

class SurveyRepository extends BaseRepository
{
    public function __construct(Survey $model)
    {
        parent::__construct($model);
    }
}
