<?php

namespace App\Repositories\Billing;

use App\Models\Billing\Invoice;
use App\Repositories\BaseRepository;

class InvoiceRepository extends BaseRepository
{
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }
}
