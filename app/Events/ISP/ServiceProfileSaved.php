<?php

namespace App\Events\ISP;

use App\Models\ISP\ServiceProfile;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceProfileSaved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceProfile $serviceProfile,
        public readonly bool $isNew = false,
    ) {}
}
