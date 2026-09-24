<?php

namespace App\Services\CRM;

use App\Models\AuditLog;
use App\Models\CRM\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerManagementService
{
    public function createFromMap(array $data, User $user): Customer
    {
        return DB::transaction(function () use ($data, $user) {
            $customer = Customer::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => $data['status'] ?? 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->logAudit($customer, 'created', null, $customer->toArray(), $user);

            return $customer;
        });
    }

    public function updateFromMap(Customer $customer, array $data, User $user): Customer
    {
        return DB::transaction(function () use ($customer, $data, $user) {
            $old = $customer->toArray();

            $customer->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => $data['status'] ?? $customer->status,
                'updated_by' => $user->id,
            ]);

            if (($data['sn'] ?? null) || ($data['mac'] ?? null) || ($data['ssid'] ?? null)) {
                $cs = $customer->customerServices()->first();
                if ($cs && $cs->onu_id) {
                    $onu = \App\Models\ISP\Onu::find($cs->onu_id);
                    if ($onu) {
                        $onu->update([
                            'serial_number' => $data['sn'] ?? $onu->serial_number,
                            'mac_address' => $data['mac'] ?? $onu->mac_address,
                            'wifi_ssid' => $data['ssid'] ?? $onu->wifi_ssid,
                        ]);
                    }
                }
            }

            $this->logAudit($customer, 'updated', $old, $customer->toArray(), $user);

            return $customer;
        });
    }

    public function delete(Customer $customer, User $user): void
    {
        DB::transaction(function () use ($customer, $user) {
            $old = $customer->toArray();
            $customer->update(['updated_by' => $user->id]);
            $customer->delete();
            $this->logAudit($customer, 'deleted', $old, null, $user);
        });
    }

    protected function logAudit(Customer $customer, string $event, ?array $oldValues, ?array $newValues, User $user): void
    {
        try {
            AuditLog::create([
                'auditable_type' => Customer::class,
                'auditable_id' => $customer->id,
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Audit log customer map gagal', [
                'customer_id' => $customer->id,
                'event' => $event,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
