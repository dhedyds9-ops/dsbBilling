<?php

namespace App\Repositories\Billing;

use App\Models\Billing\InvoiceItem;
use App\Repositories\BaseRepository;

class InvoiceItemRepository extends BaseRepository
{
    public function __construct(InvoiceItem $model)
    {
        parent::__construct($model);
    }
}
