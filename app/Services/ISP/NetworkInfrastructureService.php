<?php

namespace App\Services\ISP;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class NetworkInfrastructureService
{
    abstract protected function getModelClass(): string;

    public function create(array $data, User $user): Model
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            $modelClass = $this->getModelClass();
            $model = $modelClass::create($data);

            $this->logAudit($model, 'created', null, $model->toArray(), $user);

            return $model;
        });
    }

    public function update(Model $model, array $data, User $user): Model
    {
        return DB::transaction(function () use ($model, $data, $user) {
            $oldValues = $model->toArray();
            $data['updated_by'] = $user->id;

            $model->update($data);

            $this->logAudit($model, 'updated', $oldValues, $model->toArray(), $user);

            return $model;
        });
    }

    public function delete(Model $model, User $user): void
    {
        DB::transaction(function () use ($model, $user) {
            $oldValues = $model->toArray();
            $model->update(['updated_by' => $user->id]);
            $model->delete();
            $this->logAudit($model, 'deleted', $oldValues, null, $user);
        });
    }

    public function restore(Model $model, User $user): void
    {
        DB::transaction(function () use ($model, $user) {
            $model->update(['updated_by' => $user->id]);
            $model->restore();
            $this->logAudit($model, 'restored', null, $model->toArray(), $user);
        });
    }

    public function bulkDelete(array $ids, User $user): void
    {
        DB::transaction(function () use ($ids, $user) {
            $modelClass = $this->getModelClass();
            $models = $modelClass::whereIn('id', $ids)->get();
            foreach ($models as $model) {
                $this->delete($model, $user);
            }
            $this->logAudit(null, 'bulk_delete', ['ids' => $ids], null, $user);
        });
    }

    public function bulkActivate(array $ids, User $user): void
    {
        DB::transaction(function () use ($ids, $user) {
            $modelClass = $this->getModelClass();
            $modelClass::whereIn('id', $ids)->update(['status' => 'active', 'updated_by' => $user->id]);
            $this->logAudit(null, 'bulk_activate', ['ids' => $ids], ['status' => 'active'], $user);
        });
    }

    public function bulkDeactivate(array $ids, User $user): void
    {
        DB::transaction(function () use ($ids, $user) {
            $modelClass = $this->getModelClass();
            $modelClass::whereIn('id', $ids)->update(['status' => 'inactive', 'updated_by' => $user->id]);
            $this->logAudit(null, 'bulk_deactivate', ['ids' => $ids], ['status' => 'inactive'], $user);
        });
    }

    public function bulkRestore(array $ids, User $user): void
    {
        DB::transaction(function () use ($ids, $user) {
            $modelClass = $this->getModelClass();
            $models = $modelClass::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($models as $model) {
                $this->restore($model, $user);
            }
            $this->logAudit(null, 'bulk_restore', ['ids' => $ids], null, $user);
        });
    }

    protected function logAudit($model, string $event, ?array $oldValues, ?array $newValues, User $user): void
    {
        try {
            if ($model) {
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
            } else {
                AuditLog::create([
                    'event' => $event,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'user_id' => $user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Audit log failed: ' . $e->getMessage(), [
                'model' => $model ? get_class($model) : null,
                'id' => $model->id ?? null,
            ]);
        }
    }
}
