<?php

namespace App\Repositories\CRM;

use App\Models\CRM\InstallationChecklist;
use App\Repositories\BaseRepository;

class InstallationChecklistRepository extends BaseRepository
{
    public function __construct(InstallationChecklist $model)
    {
        parent::__construct($model);
    }
}
