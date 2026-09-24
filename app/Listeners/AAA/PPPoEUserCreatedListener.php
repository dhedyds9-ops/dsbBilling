<?php

namespace App\Listeners\AAA;

use App\Jobs\AAA\ProvisionPPPoEUserJob;
use App\Models\AAA\PPPoEUser;
use Src\Domain\AAA\Events\PPPoEUserCreatedEvent;

class PPPoEUserCreatedListener
{
    public function handle(PPPoEUserCreatedEvent $event): void
    {
        $pppoeUser = PPPoEUser::where('uuid', $event->pppoeUserId)->first();
        
        if ($pppoeUser) {
            ProvisionPPPoEUserJob::dispatch($pppoeUser);
        }
    }
}
