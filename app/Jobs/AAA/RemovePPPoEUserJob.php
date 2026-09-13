<?php

namespace App\Jobs\AAA;

use App\Models\AAA\PPPoEUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemovePPPoEUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected PPPoEUser $pppoeUser,
    ) {}

    public function handle(): void
    {
        // TODO: Integrasi dengan router/MikroTik untuk menghapus user PPPoE
    }
}
