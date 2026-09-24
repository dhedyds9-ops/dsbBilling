<?php

namespace App\Traits;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

/**
 * HasResellerScope — Global scope untuk memastikan Reseller HANYA melihat data MILIKNYA.
 *
 * PENGGUNAAN:
 *   Gunakan trait ini pada model yang punya kolom `reseller_id`
 *   (Customer, PPPoEUser, HotspotUser, Voucher, Invoice, Payment, ONU, dll).
 *
 * PRINSIP:
 *  - Administrator: akses SEMUA data (no scope)
 *  - Manager: akses SEMUA data (no scope — enforce via BranchScope jika multi-branch)
 *  - Reseller: HANYA data yang reseller_id === auth()->id()
 *  - Customer: HANYA data user_id === auth()->id() (jika model mendukung user_id)
 *
 * TIDAK BOLEH ada bypass scope pada frontend saja. Backend harus enforce.
 */
trait HasResellerScope
{
    public static function bootHasResellerScope()
    {
        // GLOBAL SCOPE: Selalu diterapkan pada query SELECT
        static::addGlobalScope('reseller_isolation', function (Builder $builder) {
            if (! auth()->check()) {
                // Belum login: tidak ada akses
                $builder->whereRaw('1=0');
                return;
            }

            $user = auth()->user();

            // Administrator dan Manager: NO scope (semua data)
            if ($user->hasRole(UserRole::Administrator->value)
                || $user->hasRole(UserRole::Manager->value)) {
                return;
            }

            // Reseller: strictly scope ke reseller_id = user->id
            // SECURITY: Gunakan fully qualified column untuk menghindari ambiguity join
            if ($user->hasRole(UserRole::Reseller->value)) {
                $table = $builder->getQuery()->from;
                $column = $table . '.reseller_id';
                $builder->where($column, $user->id);
                return;
            }

            // Customer: jika model punya user_id, scope ke user_id
            if ($user->hasRole(UserRole::Customer->value)) {
                $table = $builder->getQuery()->from;
                $column = $table . '.user_id';
                // Hanya terapkan jika kolom user_id ada di schema (tidak error jika tidak ada)
                $builder->where(function (Builder $q) use ($column, $user) {
                    try {
                        $q->where($column, $user->id);
                    } catch (\Throwable $e) {
                        // Jika kolom tidak ada, tetap deny (1=0) — customer tidak boleh data lain
                        $q->whereRaw('1=0');
                    }
                });
                return;
            }

            // Role tidak dikenal: DEFAULT DENY (1=0)
            $builder->whereRaw('1=0');
        });

        // CREATING HOOK: Otomatis inject reseller_id jika Reseller yang login
        static::creating(function ($model) {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();

            // Reseller login: force reseller_id ke dirinya sendiri (JANGAN percaya input)
            if ($user->hasRole(UserRole::Reseller->value)) {
                if (in_array('reseller_id', $model->getFillable(), true)) {
                    $model->reseller_id = $user->id;
                }
            }

            // Customer login: force user_id ke dirinya sendiri
            if ($user->hasRole(UserRole::Customer->value)) {
                if (in_array('user_id', $model->getFillable(), true) && empty($model->user_id)) {
                    $model->user_id = $user->id;
                }
            }
        });

        // UPDATING HOOK: Cegah Reseller mengubah record yang bukan miliknya
        static::updating(function ($model) {
            if (! auth()->check()) {
                return false;
            }

            $user = auth()->user();

            // Reseller hanya boleh update record miliknya
            if ($user->hasRole(UserRole::Reseller->value)) {
                if (isset($model->reseller_id) && (int) $model->reseller_id !== (int) $user->id) {
                    \Illuminate\Support\Facades\Log::warning('Reseller mencoba update record bukan miliknya', [
                        'user_id' => $user->id,
                        'model' => $model::class,
                        'model_id' => $model->getKey(),
                        'record_reseller_id' => $model->reseller_id,
                    ]);
                    return false; // ABORT UPDATE
                }
                // SELALU inject reseller_id, mencegah Reseller memindahkan record ke reseller lain
                if (in_array('reseller_id', $model->getFillable(), true)) {
                    $model->reseller_id = $user->id;
                }
            }

            return true;
        });

        // DELETING HOOK: Cegah Reseller menghapus record yang bukan miliknya
        static::deleting(function ($model) {
            if (! auth()->check()) {
                return false;
            }

            $user = auth()->user();

            if ($user->hasRole(UserRole::Reseller->value)) {
                if (isset($model->reseller_id) && (int) $model->reseller_id !== (int) $user->id) {
                    \Illuminate\Support\Facades\Log::warning('Reseller mencoba delete record bukan miliknya', [
                        'user_id' => $user->id,
                        'model' => $model::class,
                        'model_id' => $model->getKey(),
                        'record_reseller_id' => $model->reseller_id,
                    ]);
                    return false; // ABORT DELETE
                }
            }

            return true;
        });
    }
}
