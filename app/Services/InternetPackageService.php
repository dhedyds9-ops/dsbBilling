<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ISP\InternetPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternetPackageService
{
    public function createPackage(array $data, User $user): InternetPackage
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            $package = InternetPackage::create($data);

            $this->logAudit($package, 'created', null, $package->toArray(), $user);

            return $package;
        });
    }

    public function updatePackage(InternetPackage $package, array $data, User $user): InternetPackage
    {
        return DB::transaction(function () use ($package, $data, $user) {
            $oldValues = $package->toArray();
            $data['updated_by'] = $user->id;

            $package->update($data);

            $this->logAudit($package, 'updated', $oldValues, $package->toArray(), $user);

            return $package;
        });
    }

    public function deletePackage(InternetPackage $package, User $user): void
    {
        DB::transaction(function () use ($package, $user) {
            $oldValues = $package->toArray();
            $package->delete();
            $this->logAudit($package, 'deleted', $oldValues, null, $user);
        });
    }

    protected function logAudit($model, string $event, ?array $oldValues, ?array $newValues, User $user): void
    {
        try {
            AuditLog::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Audit log failed: ' . $e->getMessage(), [
                'model' => get_class($model),
                'id' => $model->id,
            ]);
        }
    }
}
