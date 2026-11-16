<?php

namespace App\Jobs\ACS;

use App\Models\ISP\Onu;
use App\Services\ISP\GenieAcsProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionOntLifecycleJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [15, 60, 180];
    public int $uniqueFor = 120;

    public function __construct(
        public int $onuId,
        public array $options = []
    ) {
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return 'onu-lifecycle-prov-' . $this->onuId;
    }

    public function handle(GenieAcsProvisioningService $provisioningService): void
    {
        $onu = Onu::withoutGlobalScopes()->find($this->onuId);
        if (!$onu) {
            return;
        }

        // Cegah provisioning ganda jika sudah berhasil
        if ($onu->provision_status === 'success') {
            return;
        }

        try {
            $result = $provisioningService->provisionOnt(
                $onu,
                array_merge($this->options, ['force_retry' => true])
            );
            $status = $result['status'] ?? 'pending';

            $updates = [
                'provision_status' => $status,
            ];

            if ($status === 'success') {
                $updates['operational_status'] = Onu::OP_ONLINE;
                $updates['last_seen_at'] = now();
                $onu->update($updates);
                return;
            }

            if ($status === 'failed') {
                $attrs = is_array($onu->attributes) ? $onu->attributes : [];
                $attrs['provisioning_error'] = $result['error'] ?? 'GenieACS provisioning failed';
                $updates['attributes'] = $attrs;
                $onu->update($updates);

                throw new \RuntimeException($attrs['provisioning_error']);
            }

            $onu->update($updates);
        } catch (\Throwable $e) {
            Log::warning("[ProvisionOntLifecycleJob] Gagal provisioning ONT #{$this->onuId}: " . $e->getMessage());
            $freshOnu = Onu::withoutGlobalScopes()->find($this->onuId);
            if ($freshOnu && $freshOnu->provision_status !== 'success') {
                $attrs = is_array($freshOnu->attributes) ? $freshOnu->attributes : [];
                $attrs['provisioning_error'] = $e->getMessage();
                $freshOnu->update([
                    'provision_status' => 'failed',
                    'attributes' => $attrs,
                ]);
            }
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $onu = Onu::withoutGlobalScopes()->find($this->onuId);
        if ($onu && $onu->provision_status !== 'success') {
            $attrs = is_array($onu->attributes) ? $onu->attributes : [];
            $attrs['provisioning_error'] = $exception->getMessage();
            $onu->update([
                'provision_status' => 'failed',
                'attributes' => $attrs,
            ]);
        }
    }
}
