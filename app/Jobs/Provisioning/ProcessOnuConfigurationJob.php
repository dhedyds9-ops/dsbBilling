<?php

namespace App\Jobs\Provisioning;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ISP\OnuConfigurationJob;
use App\Models\ISP\OnuState;
use App\Services\Provisioning\OnuParameterResolver;
use App\Services\Provisioning\ActualStateNormalizer;
use App\Services\Provisioning\StateCanonicalizer;
use App\Services\Provisioning\OnuObjectProvisioner;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Exception;
use Illuminate\Support\Facades\Log;

class ProcessOnuConfigurationJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;
    public $tries = 3;
    public $backoff = [10, 30, 60];
    
    // Idempotency: Unik selama 60 detik (timeout provisioner standard)
    public $uniqueFor = 60; 

    protected OnuConfigurationJob $jobRecord;
    protected array $fullDesiredState;

    public function uniqueId(): string
    {
        return (string) $this->jobRecord->onu_id;
    }

    public function __construct(OnuConfigurationJob $jobRecord, array $fullDesiredState)
    {
        $this->jobRecord = $jobRecord;
        $this->fullDesiredState = $fullDesiredState;
    }

    public function handle(
        OnuParameterResolver $resolver, 
        ActualStateNormalizer $normalizer, 
        StateCanonicalizer $canonicalizer,
        OnuObjectProvisioner $objectProvisioner,
        GenieACSDriver $acsDriver
    ): void {
        Log::info("ONU_PROVISIONING_STARTED: ONU {$this->jobRecord->onu_id} Job {$this->jobRecord->id}");
        
        $this->jobRecord->update([
            'status' => 'RUNNING',
            'started_at' => now(),
        ]);

        $onu = $this->jobRecord->onu;

        // Security Hardening: Enforce Job-level Authorization
        if ($this->jobRecord->requested_by) {
            $user = \App\Models\User::find($this->jobRecord->requested_by);
            if (!$user || $user->cannot('update', $onu)) {
                $this->jobRecord->update(['status' => 'FAILED', 'error_message' => 'Authorization Denied. User lacks permission to modify this ONU.']);
                Log::warning("SECURITY_VIOLATION: User {$this->jobRecord->requested_by} attempted to provision ONU {$onu->id} without sufficient permissions.");
                return;
            }
        }

        $deviceId = $onu->genieacs_device_id;
        $desiredState = $this->fullDesiredState;
        
        try {
            if (!$acsDriver->isDeviceOnline($deviceId)) {
                $this->jobRecord->update(['status' => 'WAITING_DEVICE', 'error_message' => 'Device is offline. Will retry.']);
                Log::warning("ONU_UNAVAILABLE: ONU {$onu->id} is offline");
                $this->release(300); // Retry after 5 minutes
                return;
            }

            $currentParams = $acsDriver->getDeviceParameters($deviceId);
            $trueDeviceId = $currentParams['_id'] ?? $deviceId;

            Log::info("ONU_OBJECT_DISCOVERY: Starting object resolution");
            // Object Resolution & Creation Lifecycle
            // $currentParams is passed by reference and updated inside if AddObject is called.
            $objectsCreated = $objectProvisioner->ensureObjects($onu, $currentParams, $desiredState);
            
            Log::info("ONU_OBJECT_RESOLVED: Proceeding to parameter resolution");

            $this->jobRecord->update(['status' => 'APPLYING']);

            $semanticKeys = $normalizer->flatten($desiredState);
            $parameterValues = [];
            foreach ($semanticKeys as $semanticKey => $value) {
                if (is_scalar($value)) {
                    // Pass $currentParams to prevent duplicate API calls
                    $path = $resolver->resolve($onu, $semanticKey, $currentParams);
                    if ($path) {
                        $parameterValues[$path] = $resolver->resolveValue($semanticKey, $value);
                    } else {
                        Log::warning("UNSUPPORTED_PARAMETER: Cannot resolve $semanticKey for ONU {$onu->id}");
                    }
                }
            }

            $beforeState = $normalizer->normalize($onu, $currentParams, $desiredState);
            $this->jobRecord->update(['before_state' => $canonicalizer->canonicalize($beforeState, true)]);

            if (!empty($parameterValues)) {
                Log::info("ONU_PARAMETERS_APPLIED: Setting parameters", ['params_count' => count($parameterValues)]);
                // Apply via GenieACS
                $acsDriver->setParameterValues($trueDeviceId, $parameterValues);
            }

            $this->jobRecord->update(['status' => 'VERIFYING']);
            Log::info("ONU_READBACK_STARTED: Waiting before read-back");
            sleep(5);

            $newParams = $acsDriver->getDeviceParameters($deviceId);
            $afterState = $normalizer->normalize($onu, $newParams, $desiredState);
            
            $this->jobRecord->update(['after_state' => $canonicalizer->canonicalize($afterState, true)]);

            $hashDesired = $canonicalizer->hash($desiredState, false);
            $hashActual = $canonicalizer->hash($afterState, false);

            if ($hashDesired === $hashActual) {
                $this->jobRecord->update(['status' => 'SUCCESS', 'completed_at' => now()]);
                $this->syncState($onu, $desiredState, $afterState, 'IN_SYNC', $canonicalizer);
                Log::info("ONU_CONFIGURATION_VERIFIED: SUCCESS and IN_SYNC for ONU {$onu->id}");
            } else {
                $this->jobRecord->update(['status' => 'SUCCESS', 'completed_at' => now(), 'error_message' => 'Applied but drift detected on read-back']);
                $this->syncState($onu, $desiredState, $afterState, 'CONFIGURATION_DRIFT', $canonicalizer);
                Log::warning("ONU_CONFIGURATION_DRIFT: SUCCESS but drifted for ONU {$onu->id}");
            }

        } catch (Exception $e) {
            Log::error("PARAMETER_WRITE_FAILED: " . $e->getMessage());
            
            $state = OnuState::where('onu_id', $this->jobRecord->onu_id)->first();
            if ($state) {
                $state->update(['drift_status' => 'UNKNOWN']);
            }

            // Rethrow agar worker mendeteksi kegagalan dan melakukan retry
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Hanya dieksekusi jika job sudah mencapai max $tries dan masuk ke tabel failed_jobs
        $this->jobRecord->update([
            'status' => 'FAILED',
            'error_message' => $exception->getMessage(),
            'completed_at' => now()
        ]);
        
        Log::critical("ONU_PROVISIONING_PERMANENTLY_FAILED: Job {$this->jobRecord->id} exhausted all retries. Reason: " . $exception->getMessage());
    }

    private function syncState($onu, array $desiredState, array $actualState, string $driftStatus, StateCanonicalizer $canonicalizer)
    {
        $state = OnuState::where('onu_id', $onu->id)->first();
        if ($state) {
            $state->update([
                'desired_state' => $canonicalizer->canonicalize($desiredState, true),
                'actual_state' => $canonicalizer->canonicalize($actualState, true),
                'state_hash' => $canonicalizer->hash($desiredState, false),
                'actual_hash' => $canonicalizer->hash($actualState, false),
                'drift_status' => $driftStatus,
                'last_verified_at' => now()
            ]);
        }
    }
}
