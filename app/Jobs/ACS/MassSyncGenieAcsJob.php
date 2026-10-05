<?php

namespace App\Jobs\ACS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MassSyncGenieAcsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    public function handle()
    {
        Log::info('MassSyncGenieAcsJob started');
        app(\App\Livewire\ACS\Device\Index::class)->syncDevicesBackground();
        Log::info('MassSyncGenieAcsJob finished');
    }
}
