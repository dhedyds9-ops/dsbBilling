<?php

namespace App\Jobs\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Billing\Events\JournalPostedEvent;

class PostJournalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $referenceType,
        public readonly string $referenceId,
        public readonly array $journalData,
    ) {}

    public function handle(): void
    {
        // TODO: Integrasi dengan Finance domain
        event(new JournalPostedEvent(
            (string) \Illuminate\Support\Str::uuid(),
            $this->referenceType,
            $this->referenceId
        ));
    }
}
