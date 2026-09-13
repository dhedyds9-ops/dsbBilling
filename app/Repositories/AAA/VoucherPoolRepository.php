<?php

namespace App\Repositories\AAA;

use App\Models\AAA\VoucherPool;
use App\Repositories\BaseRepository;

class VoucherPoolRepository extends BaseRepository
{
    public function __construct(VoucherPool $model)
    {
        parent::__construct($model);
    }
}
