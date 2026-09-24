<?php

namespace App\Jobs\Workforce;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

readonly class NotifyTechnicianJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $technicianId,
        public string $message,
        public string $type = 'info',
    ) {}

    public function handle(): void {
        // TODO: Integrate with Notification Service here
    }
}
