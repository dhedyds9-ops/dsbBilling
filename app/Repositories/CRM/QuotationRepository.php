<?php

namespace App\Repositories\CRM;

use App\Models\CRM\Quotation;
use App\Repositories\BaseRepository;

class QuotationRepository extends BaseRepository
{
    public function __construct(Quotation $model)
    {
        parent::__construct($model);
    }
}
