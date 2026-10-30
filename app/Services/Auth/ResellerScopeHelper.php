<?php

namespace App\Services\Auth;

use App\Models\Master\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ResellerScopeHelper
{
    /**
     * Resolve effective reseller context (supports both main reseller and sub-staff).
     *
     * @return array{reseller_id: int|null, branch_id: int|null, wilayah: string, keywords: array<int, string>, branch_area: string|null}
     */
    public static function resolveContext(?User $user = null): array
    {
        $user = $user ?? auth()->user();
        if (!$user || !$user->hasRole('reseller')) {
            return [
                'reseller_id' => null,
                'branch_id'   => null,
                'wilayah'     => '',
                'keywords'    => [],
                'branch_area' => null,
            ];
        }

        $resellerId = $user->getEffectiveResellerId() ?: $user->id;
        $branchId = $user->getEffectiveBranchId();
        $wilayah = $user->getEffectiveWilayah();
        $keywords = $user->getEffectiveTerritoryKeywords();

        $branchArea = null;
        if ($branchId) {
            $branch = Branch::find($branchId);
            if ($branch && !empty($branch->name)) {
                $cleaned = trim((string) preg_replace('/^(cabang|kantor|wilayah|area|unit|pop)\s+/i', '', trim($branch->name)));
                $genericNames = ['pusat', 'utama', 'global', 'head office', 'ho', 'all', 'semua'];
                if (mb_strlen($cleaned) >= 3 && !in_array(mb_strtolower($cleaned), $genericNames, true)) {
                    $branchArea = $cleaned;
                }
            }
        }

        return [
            'reseller_id' => $resellerId ? (int) $resellerId : null,
            'branch_id'   => $branchId ? (int) $branchId : null,
            'wilayah'     => $wilayah,
            'keywords'    => $keywords,
            'branch_area' => $branchArea,
        ];
    }

    /**
     * Check whether the reseller has any configured territory (branch_id or wilayah keywords).
     */
    public static function hasTerritory(array $ctx): bool
    {
        return !empty($ctx['branch_id']) || !empty($ctx['keywords']);
    }

    /**
     * Build a human-readable summary of the reseller's active territory scope for UI badges/empty states.
     *
     * @return array{has_territory: bool, branch_name: string|null, wilayah: string, label: string}
     */
    public static function describeTerritory(?User $user = null): array
    {
        $ctx = static::resolveContext($user);
        $branchName = !empty($ctx['branch_id']) ? Branch::where('id', $ctx['branch_id'])->value('name') : null;
        $wilayah = $ctx['wilayah'];
        $parts = [];
        if ($branchName) {
            $parts[] = $branchName;
        }
        if ($wilayah !== '') {
            $parts[] = 'Wilayah: ' . $wilayah;
        }

        return [
            'has_territory' => static::hasTerritory($ctx),
            'branch_name'   => $branchName,
            'wilayah'       => $wilayah,
            'label'         => !empty($parts) ? implode(' • ', $parts) : 'Belum diatur oleh Admin',
        ];
    }

    /**
     * Direct territory match on OLT table + POP/Branch relations (non-recursive).
     */
    public static function applyOltTerritory(Builder $q, array $ctx, string $table = 'olts'): void
    {
        $branchId = $ctx['branch_id'];
        $keywords = $ctx['keywords'];
        $branchArea = $ctx['branch_area'];

        if (empty($branchId) && empty($keywords)) {
            $q->orWhereRaw('1 = 0');
            return;
        }

        if (!empty($branchId)) {
            $q->orWhere("{$table}.branch_id", $branchId);

            // Fallback untuk OLT lama yang branch_id-nya masih NULL namun nama/alamat/POP cocok dengan nama cabang
            if (!empty($branchArea)) {
                $q->orWhere(function (Builder $sub) use ($table, $branchArea) {
                    $like = '%' . $branchArea . '%';
                    $sub->whereNull("{$table}.branch_id")
                        ->where(function (Builder $m) use ($table, $like) {
                            $m->where("{$table}.name", 'like', $like)
                              ->orWhere("{$table}.address", 'like', $like)
                              ->orWhere("{$table}.description", 'like', $like)
                              ->orWhereHas('pop', function (Builder $pq) use ($like) {
                                  $pq->where('name', 'like', $like)
                                     ->orWhere('district', 'like', $like)
                                     ->orWhere('village', 'like', $like)
                                     ->orWhere('city', 'like', $like)
                                     ->orWhere('address', 'like', $like);
                              });
                        });
                });
            }
        }

        if (!empty($keywords)) {
            $q->orWhere(function (Builder $wq) use ($table, $keywords, $branchId) {
                if (!empty($branchId)) {
                    // Jangan menabrak OLT yang secara eksplisit sudah dikunci ke cabang lain
                    $wq->where(function (Builder $b) use ($table, $branchId) {
                        $b->whereNull("{$table}.branch_id")->orWhere("{$table}.branch_id", $branchId);
                    });
                }

                $wq->where(function (Builder $kwGroup) use ($table, $keywords) {
                    foreach ($keywords as $kw) {
                        $like = '%' . $kw . '%';
                        $kwGroup->orWhere("{$table}.name", 'like', $like)
                                ->orWhere("{$table}.address", 'like', $like)
                                ->orWhere("{$table}.description", 'like', $like)
                                ->orWhere("{$table}.code", 'like', $like)
                                ->orWhereHas('pop', function (Builder $pq) use ($like) {
                                    $pq->where('name', 'like', $like)
                                       ->orWhere('district', 'like', $like)
                                       ->orWhere('village', 'like', $like)
                                       ->orWhere('city', 'like', $like)
                                       ->orWhere('address', 'like', $like);
                                })
                                ->orWhereHas('branch', function (Builder $bq) use ($like) {
                                    $bq->where('name', 'like', $like)
                                       ->orWhere('code', 'like', $like)
                                       ->orWhere('city', 'like', $like);
                                });
                    }
                });
            });
        }
    }

    /**
     * Direct territory match on ODC table (non-recursive).
     */
    public static function applyOdcTerritory(Builder $q, array $ctx, string $table = 'odcs'): void
    {
        $branchId = $ctx['branch_id'];
        $keywords = $ctx['keywords'];
        $branchArea = $ctx['branch_area'];

        if (empty($branchId) && empty($keywords)) {
            $q->orWhereRaw('1 = 0');
            return;
        }

        if (!empty($branchId)) {
            $q->orWhere("{$table}.branch_id", $branchId);

            if (!empty($branchArea)) {
                $q->orWhere(function (Builder $sub) use ($table, $branchArea) {
                    $like = '%' . $branchArea . '%';
                    $sub->whereNull("{$table}.branch_id")
                        ->where(function (Builder $m) use ($table, $like) {
                            $m->where("{$table}.name", 'like', $like)
                              ->orWhere("{$table}.address", 'like', $like)
                              ->orWhere("{$table}.description", 'like', $like);
                        });
                });
            }
        }

        if (!empty($keywords)) {
            $q->orWhere(function (Builder $wq) use ($table, $keywords, $branchId) {
                if (!empty($branchId)) {
                    $wq->where(function (Builder $b) use ($table, $branchId) {
                        $b->whereNull("{$table}.branch_id")->orWhere("{$table}.branch_id", $branchId);
                    });
                }

                $wq->where(function (Builder $kwGroup) use ($table, $keywords) {
                    foreach ($keywords as $kw) {
                        $like = '%' . $kw . '%';
                        $kwGroup->orWhere("{$table}.name", 'like', $like)
                                ->orWhere("{$table}.address", 'like', $like)
                                ->orWhere("{$table}.description", 'like', $like)
                                ->orWhere("{$table}.code", 'like', $like)
                                ->orWhereHas('pop', function (Builder $pq) use ($like) {
                                    $pq->where('name', 'like', $like)
                                       ->orWhere('district', 'like', $like)
                                       ->orWhere('village', 'like', $like)
                                       ->orWhere('city', 'like', $like);
                                })
                                ->orWhereHas('branch', function (Builder $bq) use ($like) {
                                    $bq->where('name', 'like', $like)
                                       ->orWhere('code', 'like', $like)
                                       ->orWhere('city', 'like', $like);
                                });
                    }
                });
            });
        }
    }

    /**
     * Direct territory match on ODP table (non-recursive).
     */
    public static function applyOdpTerritory(Builder $q, array $ctx, string $table = 'odps'): void
    {
        $branchId = $ctx['branch_id'];
        $keywords = $ctx['keywords'];
        $branchArea = $ctx['branch_area'];

        if (empty($branchId) && empty($keywords)) {
            $q->orWhereRaw('1 = 0');
            return;
        }

        if (!empty($branchId)) {
            $q->orWhere("{$table}.branch_id", $branchId);

            if (!empty($branchArea)) {
                $q->orWhere(function (Builder $sub) use ($table, $branchArea) {
                    $like = '%' . $branchArea . '%';
                    $sub->whereNull("{$table}.branch_id")
                        ->where(function (Builder $m) use ($table, $like) {
                            $m->where("{$table}.name", 'like', $like)
                              ->orWhere("{$table}.district", 'like', $like)
                              ->orWhere("{$table}.village", 'like', $like)
                              ->orWhere("{$table}.regency", 'like', $like)
                              ->orWhere("{$table}.address", 'like', $like);
                        });
                });
            }
        }

        if (!empty($keywords)) {
            $q->orWhere(function (Builder $wq) use ($table, $keywords, $branchId) {
                if (!empty($branchId)) {
                    $wq->where(function (Builder $b) use ($table, $branchId) {
                        $b->whereNull("{$table}.branch_id")->orWhere("{$table}.branch_id", $branchId);
                    });
                }

                $wq->where(function (Builder $kwGroup) use ($table, $keywords) {
                    foreach ($keywords as $kw) {
                        $like = '%' . $kw . '%';
                        $kwGroup->orWhere("{$table}.district", 'like', $like)
                                ->orWhere("{$table}.village", 'like', $like)
                                ->orWhere("{$table}.regency", 'like', $like)
                                ->orWhere("{$table}.address", 'like', $like)
                                ->orWhere("{$table}.name", 'like', $like)
                                ->orWhere("{$table}.code", 'like', $like)
                                ->orWhere("{$table}.description", 'like', $like)
                                ->orWhereHas('branch', function (Builder $bq) use ($like) {
                                    $bq->where('name', 'like', $like)
                                       ->orWhere('code', 'like', $like)
                                       ->orWhere('city', 'like', $like);
                                });
                    }
                });
            });
        }
    }

    /**
     * Direct ownership match on ONU via CustomerService / Customer / created_by (non-recursive).
     */
    public static function applyOnuOwnership(Builder $q, int $resellerId, string $table = 'onus'): void
    {
        $q->orWhere("{$table}.created_by", $resellerId)
          ->orWhereHas('customerService', function (Builder $csQ) use ($resellerId) {
              $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                  ->where(function (Builder $sub) use ($resellerId) {
                      $sub->where('customer_services.reseller_id', $resellerId)
                          ->orWhere('customer_services.created_by', $resellerId)
                          ->orWhereHas('customer', function (Builder $cQ) use ($resellerId) {
                              $cQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                                 ->where(function (Builder $cSub) use ($resellerId) {
                                     $cSub->where('customers.reseller_id', $resellerId)
                                          ->orWhere('customers.created_by', $resellerId);
                                 });
                          });
                  });
          });
    }

    /**
     * Ensure a CPE (ONU / ACSDevice) matched by territory is NOT already bound to another reseller's customer.
     */
    public static function excludeOtherResellersCustomers(Builder $q, int $resellerId): void
    {
        $q->whereDoesntHave('customerService', function (Builder $csQ) use ($resellerId) {
            $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                ->where(function (Builder $sub) use ($resellerId) {
                    $sub->where(function (Builder $r1) use ($resellerId) {
                        $r1->whereNotNull('customer_services.reseller_id')
                           ->where('customer_services.reseller_id', '!=', $resellerId);
                    })->orWhereHas('customer', function (Builder $cQ) use ($resellerId) {
                        $cQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                           ->whereNotNull('customers.reseller_id')
                           ->where('customers.reseller_id', '!=', $resellerId);
                    });
                });
        });
    }
}
