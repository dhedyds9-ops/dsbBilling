<?php

namespace App\Jobs\Monitoring;

use App\Models\ISP\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PollRouterDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        $activeRouters = Router::active()->get();
        
        foreach ($activeRouters as $router) {
            PollSingleRouterJob::dispatch($router);
        }
    }
}
