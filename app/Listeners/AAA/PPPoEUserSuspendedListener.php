<?php

namespace App\Listeners\AAA;

use App\Jobs\AAA\RemovePPPoEUserJob;
use App\Models\AAA\PPPoEUser;
use Src\Domain\AAA\Events\PPPoEUserSuspendedEvent;

class PPPoEUserSuspendedListener
{
    public function handle(PPPoEUserSuspendedEvent $event): void
    {
        $pppoeUser = PPPoEUser::where('uuid', $event->pppoeUserId)->first();
        
        if ($pppoeUser) {
            RemovePPPoEUserJob::dispatch($pppoeUser);
        }
    }
}
