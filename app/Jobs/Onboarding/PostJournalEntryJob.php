<?php

namespace App\Jobs\Onboarding;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PostJournalEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $invoiceId,
    ) {}

    public function handle(): void
    {
        Log::info('Posting journal entry for invoice: ' . $this->invoiceId);
        // TODO: Implement journal posting
    }
}
