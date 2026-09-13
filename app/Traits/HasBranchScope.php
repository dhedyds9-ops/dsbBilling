<?php

namespace App\Traits;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

trait HasBranchScope
{
    public static function bootHasBranchScope()
    {
        static::addGlobalScope('branch_isolation', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole(UserRole::Administrator->value)) {
                $table = $builder->getQuery()->from;

                static $columnsCache = [];
                if (!isset($columnsCache[$table])) {
                    $columnsCache[$table] = \Illuminate\Support\Facades\Schema::getColumnListing($table);
                }

                $hasBranchId = in_array('branch_id', $columnsCache[$table]);
                $hasResellerId = in_array('reseller_id', $columnsCache[$table]);

                // Branch scope logic for Manager (Technician/NOC)
                if (auth()->user()->branch_id && $hasBranchId && auth()->user()->hasRole(UserRole::Manager->value)) {
                    $builder->where($table . '.branch_id', auth()->user()->branch_id);
                }

                // Reseller scope logic
                if (auth()->user()->hasRole(UserRole::Reseller->value) && $hasResellerId) {
                    $effectiveResellerId = auth()->user()->getEffectiveResellerId();
                    if ($effectiveResellerId) {
                        $builder->where($table . '.reseller_id', $effectiveResellerId);
                    }
                }
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && !auth()->user()->hasRole(UserRole::Administrator->value)) {
                
                // Set branch_id for Manager
                if (auth()->user()->hasRole(UserRole::Manager->value) && empty($model->branch_id) && auth()->user()->branch_id) {
                    $model->branch_id = auth()->user()->branch_id;
                }

                // Set reseller_id for Reseller role (including sub-staff)
                if (auth()->user()->hasRole(UserRole::Reseller->value) && in_array('reseller_id', $model->getFillable(), true)) {
                    $effectiveResellerId = auth()->user()->getEffectiveResellerId();
                    if ($effectiveResellerId) {
                        $model->reseller_id = $effectiveResellerId;
                    }
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && !auth()->user()->hasRole(UserRole::Administrator->value)) {
                // Prevent Reseller from transferring ownership
                if (auth()->user()->hasRole(UserRole::Reseller->value) && in_array('reseller_id', $model->getFillable(), true)) {
                    $effectiveResellerId = auth()->user()->getEffectiveResellerId();
                    if ($effectiveResellerId) {
                        $model->reseller_id = $effectiveResellerId;
                    }
                }
            }
        });
    }
}
