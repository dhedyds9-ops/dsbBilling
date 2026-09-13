<?php

namespace App\Repositories\AAA;

use App\Models\AAA\Voucher;
use App\Repositories\BaseRepository;

class VoucherRepository extends BaseRepository
{
    public function __construct(Voucher $model)
    {
        parent::__construct($model);
    }
}
