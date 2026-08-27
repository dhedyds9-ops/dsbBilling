<?php

namespace App\Repositories\ISP;

use App\Models\ISP\Voucher;
use App\Repositories\BaseRepository;

class VoucherRepository extends BaseRepository
{
    public function __construct(Voucher $model)
    {
        parent::__construct($model);
    }
}
