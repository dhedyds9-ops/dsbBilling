<?php

namespace App\Repositories\ISP;

use App\Models\ISP\VoucherPool;
use App\Repositories\BaseRepository;

class VoucherPoolRepository extends BaseRepository
{
    public function __construct(VoucherPool $model)
    {
        parent::__construct($model);
    }
}
