<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasUserAreaScope
{
    /**
     * Membatasi query hanya pada area/cabang yang dapat diakses oleh user.
     */
    public function scopeForUserArea(Builder $query, $user): Builder
    {
        if (!$user || (method_exists($user, "isSuperAdmin") && $user->isSuperAdmin())) {
            return $query;
        }

        $table = $query->getModel()->getTable();
        $hasBranch = \Illuminate\Support\Facades\Schema::hasColumn($table, "branch_id");
        
        if ($hasBranch && isset($user->branch_id)) {
            $query->where($table . ".branch_id", $user->branch_id);
        }

        return $query;
    }
}

