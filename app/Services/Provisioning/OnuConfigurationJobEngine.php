<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\ISP\OnuState;
use App\Models\ISP\OnuConfigurationJob;
use App\Jobs\Provisioning\ProcessOnuConfigurationJob;
use Illuminate\Support\Facades\Log;

class OnuConfigurationJobEngine
{
    private StateCanonicalizer $canonicalizer;

    public function __construct(StateCanonicalizer $canonicalizer)
    {
        $this->canonicalizer = $canonicalizer;
    }

    public function dispatchProvisioningJob(Onu $onu, array $desiredState, string $type, ?int $customerServiceId = null, ?int $requestedBy = null): ?OnuConfigurationJob
    {
        $stateHash = $this->canonicalizer->hash($desiredState);

        $onuState = OnuState::firstOrCreate(
            ['onu_id' => $onu->id],
            [
                'desired_state' => $desiredState,
                'state_hash' => $stateHash,
                'drift_status' => 'UNKNOWN'
            ]
        );

        // Check for idempotency
        if ($onuState->state_hash === $stateHash && $onuState->drift_status === 'IN_SYNC') {
            Log::info("Idempotent provisioning ignored for ONU {$onu->id}. No drift detected.");
            return null; // NO-OP
        }

        // Update desired state
        if ($onuState->state_hash !== $stateHash) {
            $onuState->update([
                'desired_state' => $desiredState,
                'state_hash' => $stateHash,
                'drift_status' => 'UNKNOWN'
            ]);
        }

        $activeJob = OnuConfigurationJob::where('onu_id', $onu->id)
            ->whereIn('status', ['PENDING', 'RUNNING', 'WAITING_DEVICE', 'APPLYING', 'VERIFYING'])
            ->first();

        if ($activeJob) {
            Log::warning("ONU {$onu->id} already has an active configuration job ({$activeJob->id}).");
        }

        // Create the job record. Payload redacted.
        $jobRecord = OnuConfigurationJob::create([
            'onu_id' => $onu->id,
            'customer_service_id' => $customerServiceId,
            'status' => 'PENDING',
            'type' => $type,
            'payload' => $this->canonicalizer->canonicalize($desiredState, true),
            'requested_by' => $requestedBy,
        ]);

        ProcessOnuConfigurationJob::dispatch($jobRecord, $desiredState);

        return $jobRecord;
    }
}
