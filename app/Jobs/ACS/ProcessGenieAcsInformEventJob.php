<?php

namespace App\Jobs\ACS;

use App\Events\ISP\Tr069StatusChanged;
use App\Models\ACS\ACSDevice;
use App\Models\ACS\GenieAcsWebhookEvent;
use App\Models\Customer\CustomerService;
use App\Models\ISP\DeviceCorrelation;
use App\Models\ISP\Onu;
use App\Services\ISP\CorrelationService;
use App\Services\ISP\UnifiedDeviceStateEngine;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGenieAcsInformEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(public int $webhookEventId)
    {
    }

    public function handle(CorrelationService $correlationService, UnifiedDeviceStateEngine $stateEngine): void
    {
        $event = GenieAcsWebhookEvent::find($this->webhookEventId);
        if (!$event) {
            return;
        }

        if ($event->processing_status === GenieAcsWebhookEvent::STATUS_PROCESSED) {
            return;
        }

        $event->update([
            'processing_status' => GenieAcsWebhookEvent::STATUS_PROCESSING,
            'retry_count' => $event->retry_count + 1,
        ]);

        try {
            $deviceId = trim((string) $event->device_id);
            $serialNumber = trim((string) ($event->serial_number ?: $deviceId));
            $eventCodes = (array) ($event->event_codes ?: []);
            $lastInformAt = $event->last_inform_at ?: ($event->received_at ?: now());

            $isBootEvent = false;
            $isBootstrapEvent = false;
            foreach ($eventCodes as $code) {
                $codeUpper = strtoupper((string)$code);
                if (str_contains($codeUpper, '0 BOOTSTRAP')) {
                    $isBootstrapEvent = true;
                    $isBootEvent = true;
                } elseif (str_contains($codeUpper, '1 BOOT') || str_contains($codeUpper, 'M REBOOT') || str_contains($codeUpper, 'REBOOT')) {
                    $isBootEvent = true;
                }
            }

            // 1. Resolve ONU
            $onu = $this->resolveOnu($deviceId, $serialNumber, $event->payload);

            // 2. Resolve or Update ACSDevice
            $acsDevice = $this->resolveOrCreateAcsDevice($deviceId, $serialNumber, $event, $lastInformAt, $onu);
            $oldAcsStatus = $acsDevice->getOriginal('status') ?? 'unknown';

            // 3. If ONU still not found
            if (!$onu) {
                // Try evaluation via CorrelationService to see if correlation creates candidate/auto_bind
                try {
                    $correlationService->evaluateAcsDevice($acsDevice);
                    $acsDevice->refresh();
                    if ($acsDevice->onu_id) {
                        $onu = Onu::withoutGlobalScopes(['branch_isolation'])->find($acsDevice->onu_id);
                    }
                } catch (\Throwable $e) {
                    Log::warning('ProcessGenieAcsInformEventJob: CorrelationService evaluation failed', [
                        'device_id' => $deviceId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (!$onu) {
                // Device not registered in database - per specification: DO NOT auto-create customer or ONU
                $event->update([
                    'processing_status' => GenieAcsWebhookEvent::STATUS_IGNORED,
                    'processed_at' => now(),
                    'error_message' => "Perangkat {$deviceId} (SN: {$serialNumber}) belum terdaftar pada tabel onus.",
                ]);

                // Still fire Tr069StatusChanged so NOC/dashboard can see TR-069 state for discovered devices
                event(new Tr069StatusChanged($deviceId, $oldAcsStatus, 'online', $lastInformAt));
                return;
            }

            // 4. Resolve Company and Branch strictly from registered ONT
            $branchId = $onu->branch_id ?? $onu->olt?->branch_id;

            // 5. Timestamp validation: prevent stale Inform from overwriting newer state
            $isStaleInform = false;
            if ($onu->last_seen_at && $lastInformAt->lt($onu->last_seen_at->subSeconds(10))) {
                $isStaleInform = true;
                Log::info("ProcessGenieAcsInformEventJob: Received older Inform event ({$lastInformAt->toIso8601String()}) than existing ONU last_seen ({$onu->last_seen_at->toIso8601String()}) for ONU {$onu->id}. State downgrade prevented.");
            }

            // 6. Update ONU record
            if (!$isStaleInform) {
                $onu->last_seen_at = $lastInformAt;
                $onu->status = 'active';

                if (empty($onu->model) && !empty($event->product_class)) {
                    $onu->model = $event->product_class;
                }
                if (!empty($event->software_version)) {
                    $onu->firmware_version = $event->software_version;
                }
                if (empty($onu->mac_address) && !empty($acsDevice->mac_address)) {
                    $onu->mac_address = $acsDevice->mac_address;
                }
            }

            // Metadata updates in attributes column
            $attributes = is_array($onu->attributes) ? $onu->attributes : [];
            $attributes['genieacs_device_id'] = $deviceId;
            $attributes['last_event_codes'] = $eventCodes;
            $attributes['last_inform_at'] = $lastInformAt->toIso8601String();
            $attributes['last_sync_at'] = now()->toIso8601String();
            if ($isBootEvent) {
                $attributes['last_boot_at'] = $lastInformAt->toIso8601String();
            }
            $onu->attributes = $attributes;
            $onu->save();

            // 7. Ensure ACSDevice points to this ONU
            if ($acsDevice->onu_id !== $onu->id) {
                $acsDevice->onu_id = $onu->id;
                $acsDevice->save();
            }

            // 8. Run Correlation Service
            try {
                $correlationService->evaluateOnu($onu);
            } catch (\Throwable $e) {
                Log::error('ProcessGenieAcsInformEventJob: Correlation evaluation failed', [
                    'onu_id' => $onu->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // 9. Run UnifiedDeviceStateEngine on linked CustomerService
            $cs = $onu->customerService
                ?? CustomerService::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->where('onu_id', $onu->id)->first()
                ?? ($acsDevice->customer_service_id ? CustomerService::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->find($acsDevice->customer_service_id) : null);

            if ($cs) {
                try {
                    $stateEngine->evaluateAndSave($cs);
                } catch (\Throwable $e) {
                    Log::error('ProcessGenieAcsInformEventJob: UnifiedDeviceStateEngine failed', [
                        'customer_service_id' => $cs->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 10. Zero-Touch Auto-Provisioning on Factory Reset (0 BOOTSTRAP)
            // Jika modem di-reset pabrik, kembalikan otomatis konfigurasi PPPoE dan Wi-Fi pelanggan
            if ($isBootstrapEvent) {
                $isRegisteredDevice = ($onu->provision_status === 'success' || !empty($onu->provisioned_at) || $cs !== null || !empty($onu->wifi_ssid));
                if ($isRegisteredDevice) {
                    try {
                        Log::info("ProcessGenieAcsInformEventJob: Detected 0 BOOTSTRAP on registered ONU #{$onu->id} (SN: {$onu->serial_number}). Triggering Zero-Touch Auto-Reprovisioning.");
                        $provisioningService = app(\App\Services\ISP\GenieAcsProvisioningService::class);
                        $res = $provisioningService->provisionOnt($onu, [
                            'customer_service' => $cs,
                            'force_retry' => true,
                        ]);
                        Log::info("ProcessGenieAcsInformEventJob: Zero-Touch Auto-Provisioning executed for ONU #{$onu->id}", [
                            'status' => $res['status'] ?? 'unknown',
                            'message' => $res['message'] ?? ($res['error'] ?? ''),
                        ]);
                    } catch (\Throwable $e) {
                        Log::error("ProcessGenieAcsInformEventJob: Zero-touch auto-reprovisioning error on ONU #{$onu->id}", [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // 11. Dispatch Tr069StatusChanged event
            event(new Tr069StatusChanged($deviceId, $oldAcsStatus, 'online', $lastInformAt));

            // 12. Mark event processed
            $event->update([
                'processing_status' => GenieAcsWebhookEvent::STATUS_PROCESSED,
                'processed_at' => now(),
                'error_message' => null,
            ]);

        } catch (\Throwable $e) {
            Log::error('ProcessGenieAcsInformEventJob failed', [
                'event_id' => $this->webhookEventId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $event->update([
                'processing_status' => $this->attempts() >= $this->tries ? GenieAcsWebhookEvent::STATUS_FAILED : GenieAcsWebhookEvent::STATUS_QUEUED,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function resolveOnu(string $deviceId, string $serialNumber, ?array $payload = null): ?Onu
    {
        $candidates = array_unique(array_filter([$serialNumber, $deviceId]));

        // Direct match by Serial Number
        foreach ($candidates as $sn) {
            $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $sn)->first();
            if ($onu) return $onu;
        }

        // Support Vendor Hex <-> ASCII
        foreach ($candidates as $sn) {
            $snUpper = strtoupper($sn);

            // Huawei: 48575443 <-> HWTC
            if (str_starts_with($snUpper, '48575443')) {
                $alt = 'HWTC' . substr($snUpper, 8);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            } elseif (str_starts_with($snUpper, 'HWTC')) {
                $alt = '48575443' . substr($snUpper, 4);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            // ZTE: 5A544547 <-> ZTEG
            if (str_starts_with($snUpper, '5A544547')) {
                $alt = 'ZTEG' . substr($snUpper, 8);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            } elseif (str_starts_with($snUpper, 'ZTEG')) {
                $alt = '5A544547' . substr($snUpper, 4);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            // FiberHome: 46485454 <-> FHTT
            if (str_starts_with($snUpper, '46485454')) {
                $alt = 'FHTT' . substr($snUpper, 8);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            } elseif (str_starts_with($snUpper, 'FHTT')) {
                $alt = '46485454' . substr($snUpper, 4);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            // VSOL: 56534F4C <-> VSOL
            if (str_starts_with($snUpper, '56534F4C')) {
                $alt = 'VSOL' . substr($snUpper, 8);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            } elseif (str_starts_with($snUpper, 'VSOL')) {
                $alt = '56534F4C' . substr($snUpper, 4);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }

            if (str_starts_with($snUpper, '12345') && strlen($snUpper) >= 12) {
                $alt = 'VSOL' . substr($snUpper, -8);
                $onu = Onu::withoutGlobalScopes(['branch_isolation'])->where('serial_number', $alt)->first();
                if ($onu) return $onu;
            }
        }

        // Match via existing ACSDevice link
        $acsDevice = ACSDevice::withoutGlobalScopes()->where('uuid', $deviceId)
            ->orWhere('serial_number', $serialNumber)
            ->first();

        if ($acsDevice && $acsDevice->onu_id) {
            $onu = Onu::withoutGlobalScopes(['branch_isolation'])->find($acsDevice->onu_id);
            if ($onu) return $onu;
        }

        if ($acsDevice && $acsDevice->resolved_onu) {
            return $acsDevice->resolved_onu;
        }

        // Match via DeviceCorrelation table
        $correlation = DeviceCorrelation::where('genieacs_device_id', $deviceId)
            ->orWhere('genieacs_device_id', $serialNumber)
            ->first();

        if ($correlation && $correlation->onu_id) {
            $onu = Onu::withoutGlobalScopes(['branch_isolation'])->find($correlation->onu_id);
            if ($onu) return $onu;
        }

        // Match via MAC Address fallback
        $macAddress = $payload['mac_address'] ?? null;
        if (!empty($macAddress)) {
            $cleaned = strtoupper(preg_replace('/[^A-F0-9]/i', '', trim($macAddress)));
            $formatted = strlen($cleaned) === 12 ? implode(':', str_split($cleaned, 2)) : $macAddress;
            $onu = Onu::withoutGlobalScopes(['branch_isolation'])
                ->where('mac_address', $macAddress)
                ->orWhere('mac_address', $formatted)
                ->first();
            if ($onu) return $onu;
        }

        return null;
    }

    protected function resolveOrCreateAcsDevice(string $deviceId, string $serialNumber, GenieAcsWebhookEvent $event, Carbon $lastInformAt, ?Onu $onu = null): ACSDevice
    {
        $acsDevice = ACSDevice::withoutGlobalScopes()->where('uuid', $deviceId)->first();
        if (!$acsDevice) {
            $acsDevice = ACSDevice::withoutGlobalScopes()->where('serial_number', $serialNumber)->first();
        }

        $fields = [
            'uuid' => $deviceId,
            'serial_number' => $serialNumber,
            'last_inform' => $lastInformAt,
            'last_contact' => now(),
            'status' => 'online',
        ];

        if (!empty($event->manufacturer)) {
            $fields['manufacturer'] = $event->manufacturer;
        }
        if (!empty($event->product_class)) {
            $fields['product_class'] = $event->product_class;
        }
        if (!empty($event->software_version)) {
            $fields['software_version'] = $event->software_version;
            $fields['firmware_version'] = $event->software_version;
        }
        if (!empty($event->ip_address)) {
            $fields['ip_address'] = $event->ip_address;
        }
        if ($onu) {
            $fields['onu_id'] = $onu->id;
            if ($onu->olt_id) $fields['olt_id'] = $onu->olt_id;
            if ($onu->odp_id) $fields['odp_id'] = $onu->odp_id;
        }

        if ($acsDevice) {
            $acsDevice->update($fields);
        } else {
            $acsDevice = ACSDevice::create($fields);
        }

        return $acsDevice;
    }
}
