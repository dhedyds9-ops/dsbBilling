<?php

namespace App\Jobs\ACS;

use App\Models\ISP\Onu;
use App\Services\ISP\GenieAcsProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionOntLifecycleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(
        public int $onuId,
        public array $options = []
    ) {}

    public function handle(GenieAcsProvisioningService $provisioningService): void
    {
        $onu = Onu::withoutGlobalScopes()->find($this->onuId);
        if (!$onu) {
            return;
        }

        try {
            $result = $provisioningService->provisionOnt($onu, $this->options);
            $status = $result['status'] ?? 'pending';

            $updates = [
                'provision_status' => $status,
            ];

            if ($status === 'success') {
                $updates['operational_status'] = Onu::OP_ONLINE;
                $updates['last_seen_at'] = now();
            }

            $onu->update($updates);
        } catch (\Throwable $e) {
            Log::warning("[ProvisionOntLifecycleJob] Gagal provisioning ONT #{$this->onuId}: " . $e->getMessage());
            $onu->update([
                'provision_status' => 'failed',
            ]);
            throw $e;
        }
    }
}
