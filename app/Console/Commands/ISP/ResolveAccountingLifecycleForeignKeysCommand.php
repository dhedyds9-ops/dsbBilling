<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\RadiusAccounting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResolveAccountingLifecycleForeignKeysCommand extends Command
{
    protected $signature = 'radius:resolve-lifecycle-fk {--limit=10000 : max rows per run}';
    protected $description = 'Backfill PPPoE/Hotspot/CustomerService FK pada radius_accounting yang masih NULL (ingest terjadi sebelum sync data user)';

    public function handle(): int
    {
        $limit = (int)$this->option('limit');

        $rows = RadiusAccounting::query()
            ->select(['id', 'username', 'framed_protocol', 'pppoe_user_id', 'hotspot_user_id', 'customer_service_id'])
            ->whereNotNull('username')
            ->where(function ($q) {
                $q->whereNull('customer_service_id')
                    ->orWhereNull('pppoe_user_id')
                    ->orWhereNull('hotspot_user_id');
            })
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        $this->info("Radius accounting tanpa FK lifecycle: {$rows->count()} (limit: {$limit})");

        if ($rows->count() === 0) {
            return self::SUCCESS;
        }

        $done = 0;
        $missed = 0;
        foreach ($rows as $row) {
            try {
                $updated = $this->resolveRow($row);
                if ($updated) {
                    $done++;
                } else {
                    $missed++;
                }
            } catch (\Throwable $e) {
                $missed++;
                $this->error("Resolve gagal id={$row->id}: {$e->getMessage()}");
            }
        }

        $this->info("Berhasil: {$done}; Tetap miss: {$missed}");

        return self::SUCCESS;
    }

    private function resolveRow(RadiusAccounting $row): bool
    {
        $proto = strtolower((string)$row->framed_protocol);
        $username = trim((string)$row->username);
        if ($username === '') {
            return false;
        }

        $set = [];

        if ((int)$row->pppoe_user_id <= 0 && ($proto === 'ppp' || $proto === '1' || $proto === '')) {
            $pppoe = PPPoEUser::query()->where('username', $username)->first(['id', 'customer_service_id']);
            if ($pppoe) {
                $set['pppoe_user_id'] = $pppoe->id;
                if ((int)$row->customer_service_id <= 0) {
                    $set['customer_service_id'] = $pppoe->customer_service_id;
                }
            }
        }

        if ((int)$row->hotspot_user_id <= 0) {
            $hs = HotspotUser::query()->where('username', $username)->first(['id', 'customer_service_id']);
            if ($hs) {
                $set['hotspot_user_id'] = $hs->id;
                if ((int)($row->customer_service_id ?? 0) <= 0 && empty($set['customer_service_id'])) {
                    $set['customer_service_id'] = $hs->customer_service_id;
                }
            }
        }

        if (count($set) === 0) {
            return false;
        }

        DB::table('radius_accounting')
            ->where('id', $row->id)
            ->update(array_merge($set, ['updated_at' => now()]));

        return true;
    }
}
