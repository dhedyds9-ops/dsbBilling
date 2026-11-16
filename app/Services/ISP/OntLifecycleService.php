<?php

namespace App\Services\ISP;

use App\Jobs\ACS\ProvisionOntLifecycleJob;
use App\Models\ACS\ACSDevice;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Odp;
use App\Models\ISP\Onu;
use App\Models\ISP\OnuHistory;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Models\Workforce\WorkOrder;
use App\Services\Inventory\StockMutationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OntLifecycleService
{
    public function __construct(
        protected StockMutationService $stockMutationService
    ) {}

    /**
     * Normalisasi transaction_id agar aman untuk unique index (maks 90 karakter sehingga aman ditambah suffix '-OUT').
     */
    protected function normalizeTransactionId(?string $rawTxId): ?string
    {
        if ($rawTxId === null) {
            return null;
        }
        $clean = trim($rawTxId);
        if ($clean === '') {
            return null;
        }
        return substr($clean, 0, 90);
    }

    /**
     * Eksekusi transaksi dengan proteksi idempotensi penuh (termasuk tangkapan duplicate-key akibat race condition paralel).
     */
    protected function runIdempotentTransaction(?string $txId, callable $callback): array
    {
        if ($txId) {
            $existing = OnuHistory::withoutGlobalScopes()
                ->where('transaction_id', $txId)
                ->first();
            if ($existing && !empty($existing->payload)) {
                return array_merge($existing->payload, ['idempotent_replay' => true]);
            }
        }

        try {
            return DB::transaction(function () use ($txId, $callback) {
                if ($txId) {
                    $inTxExisting = OnuHistory::withoutGlobalScopes()
                        ->where('transaction_id', $txId)
                        ->lockForUpdate()
                        ->first();
                    if ($inTxExisting && !empty($inTxExisting->payload)) {
                        return array_merge($inTxExisting->payload, ['idempotent_replay' => true]);
                    }
                }

                return $callback();
            });
        } catch (QueryException $qe) {
            $msg = $qe->getMessage();
            $isUniqueViolation = str_contains($msg, 'UNIQUE constraint failed')
                || str_contains($msg, 'Duplicate entry')
                || in_array($qe->getCode(), ['23000', '23505', 23000, 1062, 19], true);

            if ($txId && $isUniqueViolation) {
                $committed = OnuHistory::withoutGlobalScopes()
                    ->where('transaction_id', $txId)
                    ->first();
                if ($committed && !empty($committed->payload)) {
                    return array_merge($committed->payload, ['idempotent_replay' => true]);
                }
            }

            throw $qe;
        }
    }

    /**
     * Hitung ulang jumlah port aktif pada ODP (hanya menghitung ONU dengan status = 'active').
     */
    public function recalculateOdpUsedPorts(?int $odpId): void
    {
        if (!$odpId) {
            return;
        }

        $odp = Odp::withoutGlobalScopes()->find($odpId);
        if ($odp) {
            $activeCount = Onu::withoutGlobalScopes()
                ->where('odp_id', $odp->id)
                ->where('status', Onu::STATUS_ACTIVE)
                ->count();
            $odp->update(['used_port_count' => $activeCount]);
        }
    }

    /**
     * 1. SENSUS (Field Audit ONU & ODP)
     * Tidak memaksa operational_status menjadi 'online' kecuali ada sinyal riil dari OLT/ACS.
     */
    public function recordSensus(array $data, User $actor): array
    {
        $txId = $this->normalizeTransactionId($data['transaction_id'] ?? null);

        $sn = strtoupper(trim((string) ($data['onu_sn'] ?? $data['serial_number'] ?? '')));
        if ($sn === '') {
            throw new \InvalidArgumentException('Serial Number ONU wajib diisi.');
        }

        $isTitipan = (bool) ($data['is_titipan'] ?? false);
        $customerId = !$isTitipan && !empty($data['customer_id']) ? (int) $data['customer_id'] : null;
        $oltId = !empty($data['olt_id']) ? (int) $data['olt_id'] : null;
        $ponPort = !empty($data['pon_port']) ? (int) $data['pon_port'] : 1;
        $odpId = !empty($data['odp_id']) ? (int) $data['odp_id'] : null;
        $odpPort = !empty($data['odp_port']) ? (int) $data['odp_port'] : null;
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        return $this->runIdempotentTransaction($txId, function () use (
            $txId,
            $sn,
            $isTitipan,
            $customerId,
            $oltId,
            $ponPort,
            $odpId,
            $odpPort,
            $latitude,
            $longitude,
            $data,
            $actor
        ) {
            $customer = null;
            if (!$isTitipan && $customerId) {
                $customer = Customer::withoutGlobalScopes()->find($customerId);
                if (!$customer) {
                    throw new \InvalidArgumentException('Pelanggan tidak ditemukan.');
                }
            }

            $branchId = $actor->branch_id
                ?? $customer?->branch_id
                ?? $customer?->reseller?->branch_id
                ?? ($odpId ? Odp::withoutGlobalScopes()->where('id', $odpId)->value('branch_id') : null)
                ?? 1;

            // Cek apakah SN sedang aktif digunakan oleh pelanggan lain (baik mode reguler maupun mode Titipan!)
            $existingOnu = Onu::withoutGlobalScopes()
                ->where('serial_number', $sn)
                ->lockForUpdate()
                ->first();

            if ($existingOnu) {
                $activeOtherCsQuery = CustomerService::withoutGlobalScopes()
                    ->where('onu_id', $existingOnu->id)
                    ->where('status', 'active')
                    ->with('customer');

                if (!$isTitipan && $customer) {
                    $activeOtherCsQuery->where('customer_id', '!=', $customer->id);
                }

                $activeOtherCs = $activeOtherCsQuery->first();
                if ($activeOtherCs) {
                    $otherName = $activeOtherCs->customer?->name ?? ('Pelanggan #' . $activeOtherCs->customer_id);
                    throw new \InvalidArgumentException(
                        "ONU SN {$sn} sedang aktif digunakan oleh pelanggan ({$otherName}). Tidak dapat dialihkan secara sepihak."
                    );
                }
            }

            $oldStatus = $existingOnu?->status;
            $oldOltId = $existingOnu?->olt_id;
            $oldOdpId = $existingOnu?->odp_id;

            // Evaluasi operational_status riil dari sinyal OLT atau ACSDevice (TIDAK dipaksa online)
            $hasRealSignal = $existingOnu && $existingOnu->rx_power_dbm !== null && (float) $existingOnu->rx_power_dbm !== 0.0;
            $acsOnline = false;
            if (Schema::hasTable('acs_devices')) {
                $acsOnline = ACSDevice::withoutGlobalScopes()
                    ->where('serial_number', $sn)
                    ->where('status', 'online')
                    ->exists();
            }

            $operationalStatus = ($hasRealSignal || $acsOnline)
                ? Onu::OP_ONLINE
                : ($existingOnu?->operational_status ?: Onu::OP_UNKNOWN);

            $onuName = $isTitipan
                ? 'Titipan: ' . trim((string) ($data['titipan_name'] ?? 'Perangkat'))
                : 'ONU ' . ($customer ? $customer->name : 'Pelanggan');

            $onuDesc = $isTitipan
                ? (($data['titipan_notes'] ?? null) ?: 'Perangkat titipan infrastruktur/lokal tanpa paket PPPoE/Hotspot')
                : 'Hasil Sensus Lapangan & Audit Jalur';

            if ($customer) {
                $updateCust = ['branch_id' => $branchId];
                if ($latitude && $longitude) {
                    $updateCust['latitude'] = $latitude;
                    $updateCust['longitude'] = $longitude;
                }
                $customer->update($updateCust);
            }

            $prevAttrs = is_array($existingOnu?->attributes) ? $existingOnu->attributes : [];
            $onuData = [
                'olt_id' => $oltId,
                'pon_port' => $ponPort,
                'odp_id' => $odpId,
                'branch_id' => $branchId,
                'name' => $onuName,
                'description' => $onuDesc,
                'status' => Onu::STATUS_ACTIVE,
                'condition' => Onu::CONDITION_GOOD,
                'audit_status' => Onu::AUDIT_AUDITED,
                'operational_status' => $operationalStatus,
                'audited_at' => now(),
                'audited_by' => $actor->id,
                'attributes' => array_merge($prevAttrs, [
                    'odp_port' => $odpPort,
                    'is_titipan' => $isTitipan,
                    'titipan_name' => $isTitipan ? ($data['titipan_name'] ?? null) : null,
                    'audited_by' => $actor->name,
                    'audited_at' => now()->toDateTimeString(),
                ]),
            ];

            if ($latitude && $longitude) {
                $onuData['latitude'] = $latitude;
                $onuData['longitude'] = $longitude;
            }

            if ($existingOnu) {
                $existingOnu->update($onuData);
                $onu = $existingOnu->fresh();
            } else {
                $onu = Onu::withoutGlobalScopes()->create(array_merge([
                    'serial_number' => $sn,
                    'created_by' => $actor->id,
                ], $onuData));
            }

            // Rekalkulasi port ODP lama (jika pindah ODP) dan ODP baru
            if ($oldOdpId && (int) $oldOdpId !== (int) $odpId) {
                $this->recalculateOdpUsedPorts((int) $oldOdpId);
            }
            if ($odpId) {
                $this->recalculateOdpUsedPorts((int) $odpId);
            }

            if ($customer && !$isTitipan) {
                $cs = CustomerService::withoutGlobalScopes()
                    ->where('customer_id', $customer->id)
                    ->where('onu_id', $onu->id)
                    ->first();

                if (!$cs) {
                    $existingCs = CustomerService::withoutGlobalScopes()
                        ->where('customer_id', $customer->id)
                        ->latest('id')
                        ->first();

                    if ($existingCs && empty($existingCs->onu_id)) {
                        $existingCs->update(['onu_id' => $onu->id]);
                    } else {
                        CustomerService::create([
                            'uuid' => (string) Str::uuid(),
                            'customer_id' => $customer->id,
                            'onu_id' => $onu->id,
                            'status' => 'active',
                            'service_status' => 'active',
                            'created_by' => $actor->id,
                        ]);
                    }
                }
            }

            $resultPayload = [
                'success' => true,
                'onu_id' => $onu->id,
                'serial_number' => $onu->serial_number,
                'status' => $onu->status,
                'condition' => $onu->condition,
                'audit_status' => $onu->audit_status,
                'operational_status' => $onu->operational_status,
                'customer_id' => $customer?->id,
                'is_titipan' => $isTitipan,
            ];

            OnuHistory::withoutGlobalScopes()->create([
                'transaction_id' => $txId,
                'onu_id' => $onu->id,
                'serial_number' => $onu->serial_number,
                'customer_id' => $customer?->id,
                'old_status' => $oldStatus,
                'new_status' => $onu->status,
                'condition' => $onu->condition,
                'old_olt_id' => $oldOltId,
                'new_olt_id' => $onu->olt_id,
                'old_odp_id' => $oldOdpId,
                'new_odp_id' => $onu->odp_id,
                'activity' => 'sensus',
                'technician_id' => $actor->id,
                'ticket_id' => $data['ticket_id'] ?? null,
                'work_order_id' => $data['work_order_id'] ?? null,
                'branch_id' => $branchId,
                'payload' => $resultPayload,
                'description' => $onuDesc,
            ]);

            return $resultPayload;
        });
    }

    /**
     * 2. SWAP ONT (Ganti ONT Lama -> ONT Baru)
     * Standar status: ONT Lama = 'replaced', ONT Baru = 'active'.
     */
    public function executeSwap(array $data, User $actor): array
    {
        $txId = $this->normalizeTransactionId($data['transaction_id'] ?? null);

        $oldSn = strtoupper(trim((string) ($data['old_serial_number'] ?? '')));
        $newSn = strtoupper(trim((string) ($data['new_serial_number'] ?? '')));

        if ($newSn === '') {
            throw new \InvalidArgumentException('Serial Number ONT baru wajib diisi.');
        }
        if ($oldSn !== '' && $oldSn === $newSn) {
            throw new \InvalidArgumentException('Serial Number baru tidak boleh sama dengan Serial Number lama.');
        }

        return $this->runIdempotentTransaction($txId, function () use ($txId, $oldSn, $newSn, $data, $actor) {
            $oldOnu = null;
            if (!empty($data['old_onu_id'])) {
                $oldOnu = Onu::withoutGlobalScopes()->lockForUpdate()->find($data['old_onu_id']);
            } elseif ($oldSn !== '') {
                $oldOnu = Onu::withoutGlobalScopes()->where('serial_number', $oldSn)->lockForUpdate()->first();
            }

            if ($oldOnu && $oldSn === '') {
                $oldSn = (string) $oldOnu->serial_number;
            }

            // Proteksi Concurrent Swap & APK lama tanpa transaction_id:
            // Jika ONT lama sudah berstatus 'replaced', tolak agar tidak terjadi mutasi stok ganda!
            if ($oldOnu && $oldOnu->status === Onu::STATUS_REPLACED) {
                throw new \InvalidArgumentException(
                    "Perangkat ONT lama {$oldSn} sudah berstatus replaced (diganti). Operasi dibatalkan untuk mencegah mutasi stok ganda."
                );
            }

            // Reseller / Tenant boundary check
            $userResellerId = $actor->getEffectiveResellerId();
            if ($userResellerId && $oldOnu && $oldOnu->reseller_id && (int) $oldOnu->reseller_id !== (int) $userResellerId) {
                throw new \RuntimeException('Akses ditolak: Perangkat ONT bukan milik reseller/tenant Anda.');
            }

            $customerId = !empty($data['customer_id']) ? (int) $data['customer_id'] : null;
            $customerService = null;

            if (!empty($data['customer_service_id'])) {
                $customerService = CustomerService::withoutGlobalScopes()->lockForUpdate()->find($data['customer_service_id']);
            } elseif ($oldOnu) {
                $customerService = CustomerService::withoutGlobalScopes()
                    ->where('onu_id', $oldOnu->id)
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();
            } elseif ($customerId) {
                $customerService = CustomerService::withoutGlobalScopes()
                    ->where('customer_id', $customerId)
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();
            }

            if (!$customerId && $customerService) {
                $customerId = $customerService->customer_id;
            }

            $customer = $customerId ? Customer::withoutGlobalScopes()->find($customerId) : null;
            $custName = $customer?->name ?? 'Pelanggan';

            // Cek apakah SN baru sedang aktif dipakai pelanggan lain
            $existingNewOnu = Onu::withoutGlobalScopes()
                ->where('serial_number', $newSn)
                ->lockForUpdate()
                ->first();

            if ($existingNewOnu && (!$oldOnu || $existingNewOnu->id !== $oldOnu->id)) {
                if ($existingNewOnu->status === Onu::STATUS_DAMAGED || $existingNewOnu->condition === Onu::CONDITION_DAMAGED) {
                    throw new \InvalidArgumentException("SN {$newSn} tercatat dalam kondisi rusak (damaged) dan tidak dapat dipasang.");
                }

                $otherActive = CustomerService::withoutGlobalScopes()
                    ->where('onu_id', $existingNewOnu->id)
                    ->where('status', 'active')
                    ->when($customerId, fn($q) => $q->where('customer_id', '!=', $customerId))
                    ->with('customer')
                    ->first();

                if ($otherActive) {
                    $otherCustName = $otherActive->customer?->name ?? ('ID #' . $otherActive->customer_id);
                    throw new \InvalidArgumentException(
                        "SN {$newSn} sedang digunakan oleh pelanggan lain ({$otherCustName}). Harap gunakan ONT lain."
                    );
                }
            }

            $oldConditionRaw = strtolower((string) ($data['old_device_condition'] ?? $data['condition'] ?? 'damaged'));
            $oldCondition = in_array($oldConditionRaw, ['good', 'berfungsi']) ? Onu::CONDITION_GOOD : Onu::CONDITION_DAMAGED;
            $reason = $data['replacement_reason'] ?? $data['notes'] ?? 'Penggantian ONT di lapangan';
            $notes = $data['notes'] ?? null;
            $opticalPower = isset($data['optical_power']) && $data['optical_power'] !== '' ? (float) $data['optical_power'] : null;

            $branchId = $oldOnu?->branch_id ?? $customer?->branch_id ?? $actor->branch_id ?? 1;

            // 1. Mutasi Stok terlebih dahulu via StockMutationService (agar jika Inventory Shortage, langsung gagal sebelum mengubah ONT)
            $ticketId = !empty($data['ticket_id']) ? (int) $data['ticket_id'] : null;
            $workOrderId = !empty($data['work_order_id']) ? (int) $data['work_order_id'] : null;

            $stockResult = $this->stockMutationService->recordOntSwap(
                technicianId: $actor->id,
                oldSn: $oldSn ?: 'ONT-LAMA',
                newSn: $newSn,
                oldCondition: $oldCondition,
                customerId: $customerId,
                ticketId: $ticketId,
                workOrderId: $workOrderId,
                supportMaterialId: !empty($data['selected_material_id']) ? (int) $data['selected_material_id'] : null,
                oldModel: $oldOnu?->model ?: 'ONT GPON',
                newModel: !empty($data['new_model']) ? (string) $data['new_model'] : ($oldOnu?->model ?: 'ONT GPON'),
                opticalPower: $opticalPower !== null ? (string) $opticalPower : null,
                notes: "Penyebab swap: {$reason}. " . ($notes ? "Catatan: {$notes}" : ''),
                enforceStock: (bool) ($data['enforce_stock'] ?? false)
            );

            // 2. Update ONT Lama -> status 'replaced'
            $oldOnuPrevStatus = $oldOnu?->status ?? 'active';
            $oldOdpId = $oldOnu?->odp_id;
            if ($oldOnu) {
                $oldOnu->status = Onu::STATUS_REPLACED;
                $oldOnu->condition = $oldCondition;
                $oldOnu->operational_status = Onu::OP_OFFLINE;
                $oldOnu->provision_status = 'unprovisioned';
                $oldOnu->rx_power_dbm = null;
                $oldOnu->description = "Bekas penarikan dari {$custName} pada " . now()->format('d/m/Y H:i')
                    . " oleh {$actor->name}. Diganti (Swap) ke SN Baru: {$newSn}. Alasan: {$reason}";
                $oldOnu->updated_by = $actor->id;
                $oldOnu->save();
            }

            // 3. Buat atau Update ONT Baru -> status 'active'
            $newOnuPrevStatus = $existingNewOnu?->status ?? Onu::STATUS_SPARE;
            $newOnuPrevOltId = $existingNewOnu?->olt_id;
            $newOnuPrevOdpId = $existingNewOnu?->odp_id;

            $targetOnu = $existingNewOnu ?: new Onu(['serial_number' => $newSn]);
            $targetOnu->serial_number = $newSn;
            if (!empty($data['new_mac_address'])) {
                $targetOnu->mac_address = strtoupper(trim((string) $data['new_mac_address']));
            }
            if (!empty($data['new_model'])) {
                $targetOnu->model = trim((string) $data['new_model']);
            } elseif (empty($targetOnu->model) && $oldOnu?->model) {
                $targetOnu->model = $oldOnu->model;
            }
            if (empty($targetOnu->name)) {
                $targetOnu->name = ($targetOnu->model ?: 'ONT') . ' ' . substr($newSn, -6);
            }
            if (!empty($data['new_vendor_id'])) {
                $targetOnu->vendor_id = (int) $data['new_vendor_id'];
            } elseif (empty($targetOnu->vendor_id) && $oldOnu?->vendor_id) {
                $targetOnu->vendor_id = $oldOnu->vendor_id;
            }

            $targetOnu->olt_id = $data['olt_id'] ?? $oldOnu?->olt_id ?? $targetOnu->olt_id;
            $targetOnu->pon_port = $data['pon_port'] ?? $oldOnu?->pon_port ?? $targetOnu->pon_port;
            $targetOnu->pon_port_id = $data['pon_port_id'] ?? $oldOnu?->pon_port_id ?? $targetOnu->pon_port_id;
            $targetOnu->odp_id = $data['odp_id'] ?? $oldOnu?->odp_id ?? $targetOnu->odp_id;
            $targetOnu->wifi_ssid = !empty($data['new_wifi_ssid']) ? $data['new_wifi_ssid'] : ($oldOnu?->wifi_ssid ?? $targetOnu->wifi_ssid);
            if (!empty($data['new_wifi_password'])) {
                $targetOnu->wifi_password = $data['new_wifi_password'];
            } elseif ($oldOnu?->wifi_password) {
                $targetOnu->wifi_password = $oldOnu->wifi_password;
            }

            if ($opticalPower !== null) {
                $targetOnu->rx_power_dbm = $opticalPower;
            }

            $targetOnu->branch_id = $branchId;
            $targetOnu->status = Onu::STATUS_ACTIVE;
            $targetOnu->condition = Onu::CONDITION_GOOD;
            $targetOnu->audit_status = Onu::AUDIT_AUDITED;
            $targetOnu->operational_status = $opticalPower !== null ? Onu::OP_ONLINE : Onu::OP_UNKNOWN;
            $targetOnu->provision_status = 'pending';
            $targetOnu->last_seen_at = now();
            $targetOnu->created_by = $targetOnu->created_by ?? $actor->id;
            $targetOnu->updated_by = $actor->id;
            $targetOnu->description = "Pemasangan ONT baru untuk {$custName} (Swap pengganti " . ($oldSn ?: 'ONT-LAMA') . ") pada "
                . now()->format('d/m/Y H:i') . " oleh {$actor->name}. Alasan: {$reason}";
            $targetOnu->save();

            // Rekalkulasi port ODP jika ada perubahan
            if ($oldOdpId) {
                $this->recalculateOdpUsedPorts((int) $oldOdpId);
            }
            if ($targetOnu->odp_id && (int) $targetOnu->odp_id !== (int) $oldOdpId) {
                $this->recalculateOdpUsedPorts((int) $targetOnu->odp_id);
            }

            // 4. Re-bind CustomerService ke ONT Baru
            if ($customerService) {
                $customerService->update([
                    'onu_id' => $targetOnu->id,
                    'status' => 'active',
                ]);
            }

            // 5. Update ACSDevice
            if (Schema::hasTable('acs_devices')) {
                if ($oldSn !== '') {
                    ACSDevice::withoutGlobalScopes()->where('serial_number', $oldSn)->update([
                        'customer_service_id' => null,
                        'status' => 'replaced',
                    ]);
                }
                $newAcs = ACSDevice::withoutGlobalScopes()->where('serial_number', $newSn)->first();
                if ($newAcs) {
                    $newAcs->update([
                        'customer_service_id' => $customerService?->id,
                        'status' => 'online',
                        'wifi_ssid' => $targetOnu->wifi_ssid,
                        'wifi_password' => $targetOnu->wifi_password,
                    ]);
                }
            }

            // 6. Provisioning GenieACS (Synchronous + Outbox Queue Retry Fallback)
            $acsProvisionStatus = 'pending';
            $pppoeUsername = $data['pppoe_username']
                ?? $customerService?->pppoeUser?->username
                ?? $customerService?->username
                ?? '';
            $pppoePassword = $data['pppoe_password']
                ?? $customerService?->pppoeUser?->clear_password
                ?? $customerService?->pppoeUser?->password
                ?? $customerService?->password
                ?? '';

            $provisionOptions = [
                'pppoe_username' => (string) $pppoeUsername,
                'pppoe_password' => (string) $pppoePassword,
                'wifi_ssid' => (string) ($targetOnu->wifi_ssid ?? ''),
                'wifi_password' => (string) ($targetOnu->wifi_password ?? ''),
            ];

            if (!empty($data['queue_provisioning'])) {
                ProvisionOntLifecycleJob::dispatch($targetOnu->id, $provisionOptions);
                $acsProvisionStatus = 'queued';
                $targetOnu->update(['provision_status' => 'queued']);
            } else {
                try {
                    $targetOnu->update(['provision_status' => 'processing']);
                    $genieService = app(GenieAcsProvisioningService::class);
                    $acsResult = $genieService->provisionOnt($targetOnu, $provisionOptions);
                    $acsProvisionStatus = $acsResult['status'] ?? 'pending';
                    if ($acsProvisionStatus === 'success') {
                        $targetOnu->update([
                            'provision_status' => 'success',
                            'operational_status' => Onu::OP_ONLINE,
                        ]);
                    } elseif ($acsProvisionStatus === 'waiting_device') {
                        $targetOnu->update(['provision_status' => 'waiting_device']);
                        ProvisionOntLifecycleJob::dispatch($targetOnu->id, $provisionOptions)->delay(now()->addSeconds(30));
                    } else {
                        $targetOnu->update(['provision_status' => 'failed']);
                        ProvisionOntLifecycleJob::dispatch($targetOnu->id, $provisionOptions)->delay(now()->addSeconds(30));
                    }
                } catch (\Throwable $acsEx) {
                    Log::warning('[OntLifecycleService] GenieACS Provisioning during Swap ONT warning: ' . $acsEx->getMessage());
                    $acsProvisionStatus = 'failed';
                    $targetOnu->update(['provision_status' => 'failed']);
                    ProvisionOntLifecycleJob::dispatch($targetOnu->id, $provisionOptions)->delay(now()->addSeconds(30));
                }
            }

            // 7. Validasi Bukti Pekerjaan & Update Tiket / WorkOrder
            $ticketResolved = false;
            $ticketStatusAfter = null;

            $ticket = $ticketId ? Ticket::withoutGlobalScopes()->find($ticketId) : null;
            if (!$ticket && $workOrderId) {
                $wo = WorkOrder::withoutGlobalScopes()->find($workOrderId);
                if ($wo && $wo->ticket_id) {
                    $ticket = Ticket::withoutGlobalScopes()->find($wo->ticket_id);
                }
            }

            if ($ticket) {
                $photoBeforePath = $data['photo_before_path'] ?? null;
                $photoAfterPath = $data['photo_after_path'] ?? null;
                $digitalSignature = $data['digital_signature'] ?? null;

                $requireProof = (bool) ($data['require_proof'] ?? false);
                $requireAcsSuccess = (bool) ($data['require_acs_success'] ?? false);

                $hasProof = !empty($photoBeforePath) || !empty($photoAfterPath) || !empty($digitalSignature);
                $acsOk = !$requireAcsSuccess || in_array($acsProvisionStatus, ['success', 'waiting_device', 'queued']);
                $canResolve = (!$requireProof || $hasProof) && $acsOk;

                $resolutionText = "Pekerjaan Ganti/Swap ONT berhasil dilaksanakan.\n"
                    . "• Pelanggan: {$custName}\n"
                    . "• ONT Lama: " . ($oldSn ?: '-') . " (" . ($oldOnu?->model ?: 'ONT') . ")\n"
                    . "• ONT Baru: {$newSn} (" . ($targetOnu->model ?: 'ONT') . ")\n"
                    . ($opticalPower !== null ? "• Redaman Optik (OPM): {$opticalPower} dBm\n" : '')
                    . "• SSID Wi-Fi: " . ($targetOnu->wifi_ssid ?: '-') . "\n"
                    . "• Status ACS: {$acsProvisionStatus}\n"
                    . "• Alasan: {$reason}\n"
                    . ($notes ? "• Catatan: {$notes}\n" : '');

                $ticketUpdates = [
                    'resolution_notes' => $resolutionText,
                    'status' => $canResolve ? 'resolved' : 'in_progress',
                ];

                if ($canResolve) {
                    $ticketUpdates['resolved_at'] = now();
                    $ticketUpdates['resolved_by'] = $actor->id;
                    $ticketResolved = true;
                }

                if (Schema::hasColumn('tickets', 'optical_power') && $opticalPower !== null) {
                    $ticketUpdates['optical_power'] = $opticalPower . ' dBm';
                }
                if (Schema::hasColumn('tickets', 'digital_signature') && !empty($digitalSignature)) {
                    $ticketUpdates['digital_signature'] = $digitalSignature;
                }
                if ($photoBeforePath || $photoAfterPath) {
                    $currentPhotos = is_array($ticket->photos) ? $ticket->photos : [];
                    if ($photoBeforePath) $currentPhotos[] = '/storage/' . ltrim($photoBeforePath, '/');
                    if ($photoAfterPath) $currentPhotos[] = '/storage/' . ltrim($photoAfterPath, '/');
                    $ticketUpdates['photos'] = array_values(array_unique($currentPhotos));
                }

                $ticket->update($ticketUpdates);
                $ticketStatusAfter = $ticket->status;
            }

            if ($workOrderId) {
                $wo = WorkOrder::withoutGlobalScopes()->find($workOrderId);
                if ($wo) {
                    $wo->update([
                        'status' => ($ticket && !$ticketResolved) ? 'in_progress' : 'completed',
                        'completed_at' => ($ticket && !$ticketResolved) ? null : now(),
                    ]);
                }
            }

            $resultPayload = [
                'success' => true,
                'old_onu_id' => $oldOnu?->id,
                'old_serial_number' => $oldSn,
                'old_status' => $oldOnu?->status ?? Onu::STATUS_REPLACED,
                'old_condition' => $oldCondition,
                'new_onu_id' => $targetOnu->id,
                'new_serial_number' => $targetOnu->serial_number,
                'new_status' => $targetOnu->status,
                'new_condition' => $targetOnu->condition,
                'customer_service_id' => $customerService?->id,
                'acs_provision_status' => $acsProvisionStatus,
                'ticket_resolved' => $ticketResolved,
                'ticket_status' => $ticketStatusAfter,
                'stock' => $stockResult,
            ];

            // 8. Catat Riwayat ke onu_histories
            if ($oldOnu) {
                OnuHistory::withoutGlobalScopes()->create([
                    'transaction_id' => $txId ? ($txId . '-OUT') : null,
                    'onu_id' => $oldOnu->id,
                    'serial_number' => $oldOnu->serial_number,
                    'customer_id' => $customerId,
                    'old_status' => $oldOnuPrevStatus,
                    'new_status' => Onu::STATUS_REPLACED,
                    'condition' => $oldCondition,
                    'old_olt_id' => $oldOnu->olt_id,
                    'new_olt_id' => $oldOnu->olt_id,
                    'old_odp_id' => $oldOdpId,
                    'new_odp_id' => $oldOnu->odp_id,
                    'activity' => 'swap_out',
                    'technician_id' => $actor->id,
                    'ticket_id' => $ticket?->id,
                    'work_order_id' => $workOrderId,
                    'branch_id' => $branchId,
                    'description' => "ONT lama ditarik (digantikan oleh {$newSn}). Alasan: {$reason}",
                ]);
            }

            OnuHistory::withoutGlobalScopes()->create([
                'transaction_id' => $txId,
                'onu_id' => $targetOnu->id,
                'serial_number' => $targetOnu->serial_number,
                'customer_id' => $customerId,
                'old_status' => $newOnuPrevStatus,
                'new_status' => Onu::STATUS_ACTIVE,
                'condition' => Onu::CONDITION_GOOD,
                'old_olt_id' => $newOnuPrevOltId,
                'new_olt_id' => $targetOnu->olt_id,
                'old_odp_id' => $newOnuPrevOdpId,
                'new_odp_id' => $targetOnu->odp_id,
                'activity' => 'swap_in',
                'technician_id' => $actor->id,
                'ticket_id' => $ticket?->id,
                'work_order_id' => $workOrderId,
                'branch_id' => $branchId,
                'payload' => $resultPayload,
                'description' => "ONT baru dipasang menggantikan " . ($oldSn ?: 'ONT-LAMA') . ". Alasan: {$reason}",
            ]);

            return $resultPayload;
        });
    }

    /**
     * 3. DISMANTLE ONT (Cabut / Penarikan ONT)
     * Standar status: 'dismantled' di semua platform (Web & Android), dengan kolom 'condition' ('good' / 'damaged').
     */
    public function executeDismantle(array $data, User $actor): array
    {
        $txId = $this->normalizeTransactionId($data['transaction_id'] ?? null);

        return $this->runIdempotentTransaction($txId, function () use ($txId, $data, $actor) {
            $onu = null;
            if (!empty($data['onu_id'])) {
                $onu = Onu::withoutGlobalScopes()->lockForUpdate()->find($data['onu_id']);
            } elseif (!empty($data['serial_number'])) {
                $snLookup = strtoupper(trim((string) $data['serial_number']));
                $onu = Onu::withoutGlobalScopes()->where('serial_number', $snLookup)->lockForUpdate()->first();
            }

            if (!$onu) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Perangkat ONT tidak ditemukan.');
            }

            $sn = (string) $onu->serial_number;

            // Proteksi Concurrent Dismantle & APK lama tanpa transaction_id:
            // Jika ONT sudah berstatus 'dismantled', tolak agar tidak terjadi penambahan stok ganda!
            if (in_array($onu->status, [Onu::STATUS_DISMANTLED, 'dismantled_good', 'dismantled_damaged'], true)) {
                throw new \InvalidArgumentException(
                    "Perangkat ONT {$sn} sudah berstatus dismantled (dicabut). Operasi dibatalkan untuk mencegah mutasi stok ganda."
                );
            }

            $userResellerId = $actor->getEffectiveResellerId();
            if ($userResellerId && $onu->reseller_id && (int) $onu->reseller_id !== (int) $userResellerId) {
                throw new \RuntimeException('Akses ditolak: Perangkat ONT bukan milik reseller/tenant Anda.');
            }

            $oldStatus = $onu->status;
            $oldOltId = $onu->olt_id;
            $oldOdpId = $onu->odp_id;

            $condRaw = strtolower((string) ($data['device_condition'] ?? $data['condition'] ?? 'good'));
            $normalizedCond = in_array($condRaw, ['good', 'berfungsi']) ? Onu::CONDITION_GOOD : Onu::CONDITION_DAMAGED;
            $reason = $data['dismantle_reason'] ?? 'Berhenti Berlangganan (Permintaan Pelanggan)';
            $notes = $data['notes'] ?? null;

            $customerService = CustomerService::withoutGlobalScopes()
                ->where('onu_id', $onu->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $customerId = !empty($data['customer_id'])
                ? (int) $data['customer_id']
                : ($customerService?->customer_id ?? $onu->customer_id);

            $customer = $customerId ? Customer::withoutGlobalScopes()->find($customerId) : null;
            $custName = $customer?->name ?? 'Pelanggan';
            $modelName = $onu->model ?: 'ONT GPON';
            $branchId = $onu->branch_id ?? $actor->branch_id ?? 1;

            // 1. Update ONU dengan status standar 'dismantled' dan condition terpisah ('good' / 'damaged')
            $onu->status = Onu::STATUS_DISMANTLED;
            $onu->condition = $normalizedCond;
            $onu->operational_status = Onu::OP_OFFLINE;
            $onu->odp_id = null;
            $onu->description = "Dibongkar/ditarik dari {$custName} pada " . now()->format('d/m/Y H:i')
                . " oleh {$actor->name}. Alasan: {$reason}. Kondisi: {$normalizedCond}";
            $onu->updated_by = $actor->id;
            $onu->save();

            // Rekalkulasi port ODP lama karena ONT telah dicabut dari ODP tersebut
            if ($oldOdpId) {
                $this->recalculateOdpUsedPorts((int) $oldOdpId);
            }

            // 2. Lepaskan ikatan di CustomerService
            if ($customerService) {
                $customerService->update([
                    'onu_id' => null,
                    'status' => 'inactive',
                ]);
            }

            // 3. Lepaskan ikatan di ACSDevice
            if (Schema::hasTable('acs_devices')) {
                ACSDevice::withoutGlobalScopes()->where('serial_number', $sn)->update([
                    'customer_service_id' => null,
                    'status' => 'dismantled',
                ]);
            }

            // 4. Mutasi Stok via StockMutationService
            $ticketId = !empty($data['ticket_id']) ? (int) $data['ticket_id'] : null;
            $workOrderId = !empty($data['work_order_id']) ? (int) $data['work_order_id'] : null;

            $stockResult = $this->stockMutationService->recordOntDismantle(
                technicianId: $actor->id,
                sn: $sn,
                condition: $normalizedCond,
                customerId: $customerId,
                ticketId: $ticketId,
                workOrderId: $workOrderId,
                modelName: $modelName,
                notes: "Pembongkaran dari {$custName}. Alasan: {$reason}. " . ($notes ? "Catatan: {$notes}" : '')
            );

            // 5. Validasi Bukti & Update Tiket / WorkOrder
            $ticketResolved = false;
            $ticketStatusAfter = null;

            $ticket = $ticketId ? Ticket::withoutGlobalScopes()->find($ticketId) : null;
            if (!$ticket && $workOrderId) {
                $wo = WorkOrder::withoutGlobalScopes()->find($workOrderId);
                if ($wo && $wo->ticket_id) {
                    $ticket = Ticket::withoutGlobalScopes()->find($wo->ticket_id);
                }
            }

            if ($ticket) {
                $photoPath = $data['photo_path'] ?? null;
                $digitalSignature = $data['digital_signature'] ?? null;
                $requireProof = (bool) ($data['require_proof'] ?? false);
                $hasProof = !empty($photoPath) || !empty($digitalSignature);
                $canResolve = !$requireProof || $hasProof;

                $resText = "Pembongkaran perangkat selesai dilaksanakan.\n"
                    . "• Pelanggan: {$custName}\n"
                    . "• Unit Ditarik: {$modelName} (SN: {$sn})\n"
                    . "• Kondisi: " . ($normalizedCond === 'good' ? 'Berfungsi Baik (Masuk ke Stok Teknisi)' : 'Rusak / Afkir') . "\n"
                    . "• Alasan: {$reason}\n"
                    . ($notes ? "• Catatan: {$notes}\n" : '');

                $tUpdates = [
                    'resolution_notes' => $resText,
                    'status' => $canResolve ? 'resolved' : 'in_progress',
                ];

                if ($canResolve) {
                    $tUpdates['resolved_at'] = now();
                    $tUpdates['resolved_by'] = $actor->id;
                    $ticketResolved = true;
                }

                if (Schema::hasColumn('tickets', 'digital_signature') && !empty($digitalSignature)) {
                    $tUpdates['digital_signature'] = $digitalSignature;
                }
                if ($photoPath) {
                    $cur = is_array($ticket->photos) ? $ticket->photos : [];
                    $cur[] = '/storage/' . ltrim($photoPath, '/');
                    $tUpdates['photos'] = array_values(array_unique($cur));
                }

                $ticket->update($tUpdates);
                $ticketStatusAfter = $ticket->status;
            }

            if ($workOrderId) {
                $wo = WorkOrder::withoutGlobalScopes()->find($workOrderId);
                if ($wo) {
                    $wo->update([
                        'status' => ($ticket && !$ticketResolved) ? 'in_progress' : 'completed',
                        'completed_at' => ($ticket && !$ticketResolved) ? null : now(),
                    ]);
                }
            }

            $resultPayload = [
                'success' => true,
                'onu_id' => $onu->id,
                'serial_number' => $onu->serial_number,
                'status' => $onu->status,
                'condition' => $onu->condition,
                'device_condition' => $onu->condition,
                'operational_status' => $onu->operational_status,
                'customer_id' => $customerId,
                'checklist' => $data['checklist'] ?? [],
                'ticket_resolved' => $ticketResolved,
                'ticket_status' => $ticketStatusAfter,
                'stock' => $stockResult,
            ];

            // 6. Catat Riwayat ke onu_histories
            OnuHistory::withoutGlobalScopes()->create([
                'transaction_id' => $txId,
                'onu_id' => $onu->id,
                'serial_number' => $onu->serial_number,
                'customer_id' => $customerId,
                'old_status' => $oldStatus,
                'new_status' => Onu::STATUS_DISMANTLED,
                'condition' => $normalizedCond,
                'old_olt_id' => $oldOltId,
                'new_olt_id' => $onu->olt_id,
                'old_odp_id' => $oldOdpId,
                'new_odp_id' => null,
                'activity' => 'dismantle',
                'technician_id' => $actor->id,
                'ticket_id' => $ticket?->id,
                'work_order_id' => $workOrderId,
                'branch_id' => $branchId,
                'payload' => $resultPayload,
                'description' => "Dismantle ONT dari {$custName}. Kondisi: {$normalizedCond}. Alasan: {$reason}",
            ]);

            return $resultPayload;
        });
    }
}
