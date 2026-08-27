<?php

namespace App\Services\Jaringan;

use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\Odp;
use App\Models\ISP\Odc;
use App\Models\ISP\Pop;
use App\Models\ISP\FiberCable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Src\Domain\Jaringan\Events\FiberDeviceSyncedEvent;
use Src\Domain\Jaringan\Events\ONULosAlarmEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FiberService
{
    public function listByTab(string $tab, array $filters, string $search, string $sortField, string $sortDirection, int $perPage)
    {
        $query = match ($tab) {
            'olt' => $this->oltQuery(),
            'onu' => $this->onuQuery(),
            'odp' => $this->odpQuery(),
            'odc' => $this->odcQuery(),
            'pop' => $this->popQuery(),
            'fiber' => $this->fiberQuery(),
            'los' => $this->losQuery(),
            default => $this->oltQuery(),
        };

        $query = $this->applyFilters($query, $tab, $filters);
        $query = $this->applySearch($query, $tab, $search);
        $query = $query->orderBy($this->mapSortField($tab, $sortField), $sortDirection);

        return $perPage > 0 ? $query->paginate($perPage) : $query->get();
    }

    protected function mapSortField(string $tab, string $field): string
    {
        $map = [
            'olt' => ['id', 'name', 'host', 'status', 'created_at'],
            'onu' => ['id', 'serial_number', 'status', 'created_at'],
            'odp' => ['id', 'name', 'status', 'created_at'],
            'odc' => ['id', 'name', 'status', 'created_at'],
            'pop' => ['id', 'name', 'created_at'],
            'fiber' => ['id', 'code', 'created_at'],
            'los' => ['id', 'created_at'],
        ];
        $allowed = $map[$tab] ?? ['id'];
        return in_array($field, $allowed) ? $field : 'created_at';
    }

    protected function oltQuery()
    {
        return Olt::with(['pop', 'vendor', 'ponPorts']);
    }

    protected function onuQuery()
    {
        return Onu::with(['olt', 'ponPort', 'splitter', 'odp', 'vendor', 'customer']);
    }

    protected function odpQuery()
    {
        return Odp::with(['pop', 'olt', 'splitter']);
    }

    protected function odcQuery()
    {
        return Odc::with(['pop', 'rack']);
    }

    protected function popQuery()
    {
        return Pop::with(['olts']);
    }

    protected function fiberQuery()
    {
        return FiberCable::with(['startOdc', 'endOdc', 'vendor']);
    }

    protected function losQuery()
    {
        return Onu::with(['olt', 'customer'])
            ->where('status', 'los');
    }

    protected function applyFilters($query, string $tab, array $filters)
    {
        if (!empty($filters['pop_id'])) {
            if (in_array($tab, ['olt', 'odp', 'odc'])) {
                $query->where('pop_id', $filters['pop_id']);
            } elseif ($tab === 'onu') {
                $query->whereHas('olt', fn($q) => $q->where('pop_id', $filters['pop_id']));
            }
        }
        if (!empty($filters['olt_id'])) {
            if (in_array($tab, ['onu', 'odp', 'los'])) {
                $query->where('olt_id', $filters['olt_id']);
            }
        }
        if (!empty($filters['status']) && in_array($tab, ['olt', 'onu', 'odp', 'odc', 'fiber'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['vendor_id']) && in_array($tab, ['olt', 'onu', 'fiber'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        if (!empty($filters['region'])) {
            if ($tab === 'pop') {
                $query->where('region', 'like', '%' . $filters['region'] . '%');
            } else {
                $query->whereHas('pop', fn($q) => $q->where('region', 'like', '%' . $filters['region'] . '%'));
            }
        }
        if (!empty($filters['technician_id'])) {
            if (in_array($tab, ['olt', 'onu', 'odp', 'odc'])) {
                $query->where('technician_id', $filters['technician_id']);
            }
        }
        if (!empty($filters['install_date_from'])) {
            $query->whereDate('install_date', '>=', $filters['install_date_from']);
        }
        if (!empty($filters['install_date_to'])) {
            $query->whereDate('install_date', '<=', $filters['install_date_to']);
        }
        return $query;
    }

    protected function applySearch($query, string $tab, string $search)
    {
        if ($search === '') {
            return $query;
        }
        $like = '%' . $search . '%';
        return $query->where(function ($q) use ($tab, $like) {
            match ($tab) {
                'olt' => $q->where('name', 'like', $like)
                    ->orWhere('host', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhereHas('pop', fn($sq) => $sq->where('name', 'like', $like)),
                'onu' => $q->where('serial_number', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhereHas('olt', fn($sq) => $sq->where('name', 'like', $like))
                    ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', $like)),
                'odp' => $q->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhereHas('pop', fn($sq) => $sq->where('name', 'like', $like)),
                'odc' => $q->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhereHas('pop', fn($sq) => $sq->where('name', 'like', $like)),
                'pop' => $q->where('name', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('code', 'like', $like),
                'fiber' => $q->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('type', 'like', $like),
                'los' => $q->where('serial_number', 'like', $like)
                    ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', $like))
                    ->orWhereHas('olt', fn($sq) => $sq->where('name', 'like', $like)),
                default => null,
            };
        });
    }

    public function summaryCounts(): array
    {
        return [
            'total_olt' => Olt::count(),
            'total_onu' => Onu::count(),
            'onu_los' => Onu::where('status', 'los')->count(),
            'odp_aktif' => Odp::where('status', 'active')->count(),
            'odc_aktif' => Odc::where('status', 'active')->count(),
        ];
    }

    public function bulkSync(array $ids, string $tab, int $userId): int
    {
        return DB::transaction(function () use ($ids, $tab, $userId) {
            $count = 0;
            $class = match ($tab) {
                'olt' => Olt::class,
                'onu' => Onu::class,
                'odp' => Odp::class,
                'odc' => Odc::class,
                'pop' => Pop::class,
                'fiber' => FiberCable::class,
                default => Olt::class,
            };

            foreach ($class::whereIn('id', $ids)->cursor() as $item) {
                $item->update(['last_sync_at' => now(), 'updated_by' => $userId]);
                Event::dispatch(FiberDeviceSyncedEvent::create($tab, (string) $item->id, (string) $userId));
                $count++;
            }
            return $count;
        });
    }

    public function auditOnu(int $userId): array
    {
        $result = [
            'total' => 0,
            'no_serial' => 0,
            'no_olt' => 0,
            'duplicate' => 0,
            'los' => 0,
        ];

        $onus = Onu::all();
        $result['total'] = $onus->count();
        $result['no_serial'] = $onus->whereNull('serial_number')->count();
        $result['no_olt'] = $onus->whereNull('olt_id')->count();
        $result['los'] = $onus->where('status', 'los')->count();
        $duplicates = Onu::select('serial_number')
            ->whereNotNull('serial_number')
            ->groupBy('serial_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('serial_number');
        $result['duplicate'] = $duplicates->count();

        Log::info('ONU Audit', ['user_id' => $userId, 'result' => $result]);

        return $result;
    }

    public function bulkDisable(array $ids, string $tab, int $userId): int
    {
        $class = match ($tab) {
            'olt' => Olt::class,
            'onu' => Onu::class,
            'odp' => Odp::class,
            'odc' => Odc::class,
            default => Olt::class,
        };
        return $class::whereIn('id', $ids)->update(['status' => 'inactive', 'updated_by' => $userId]);
    }

    public function bulkEnable(array $ids, string $tab, int $userId): int
    {
        $class = match ($tab) {
            'olt' => Olt::class,
            'onu' => Onu::class,
            'odp' => Odp::class,
            'odc' => Odc::class,
            default => Olt::class,
        };
        return $class::whereIn('id', $ids)->update(['status' => 'active', 'updated_by' => $userId]);
    }

    public function bulkDelete(array $ids, string $tab): int
    {
        $class = match ($tab) {
            'olt' => Olt::class,
            'onu' => Onu::class,
            'odp' => Odp::class,
            'odc' => Odc::class,
            'pop' => Pop::class,
            'fiber' => FiberCable::class,
            default => Olt::class,
        };
        return $class::whereIn('id', $ids)->delete();
    }

    public function disableDevice(int $id, string $tab, int $userId): void
    {
        $class = match ($tab) {
            'olt' => Olt::class,
            'onu' => Onu::class,
            'odp' => Odp::class,
            'odc' => Odc::class,
            default => Olt::class,
        };
        $class::findOrFail($id)->update(['status' => 'inactive', 'updated_by' => $userId]);
    }

    public function enableDevice(int $id, string $tab, int $userId): void
    {
        $class = match ($tab) {
            'olt' => Olt::class,
            'onu' => Onu::class,
            'odp' => Odp::class,
            'odc' => Odc::class,
            default => Olt::class,
        };
        $class::findOrFail($id)->update(['status' => 'active', 'updated_by' => $userId]);
    }

    public function syncDevice(int $id, string $tab, int $userId): void
    {
        $class = match ($tab) {
            'olt' => Olt::class,
            'onu' => Onu::class,
            'odp' => Odp::class,
            'odc' => Odc::class,
            'pop' => Pop::class,
            'fiber' => FiberCable::class,
            default => Olt::class,
        };
        $item = $class::findOrFail($id);
        $item->update(['last_sync_at' => now(), 'updated_by' => $userId]);
        Event::dispatch(FiberDeviceSyncedEvent::create($tab, (string) $id, (string) $userId));
    }

    public function testLosOnu(int $onuId, int $userId): array
    {
        $onu = Onu::with(['olt', 'customer'])->findOrFail($onuId);
        $status = $onu->status === 'los' ? 'los' : (random_int(0, 100) > 70 ? 'warning' : 'ok');
        $rxPower = $status === 'los' ? null : round(random_int(-280, -80) / 10, 2);

        $result = [
            'onu_id' => $onuId,
            'serial_number' => $onu->serial_number,
            'status' => $status,
            'rx_power_dbm' => $rxPower,
            'tx_power_dbm' => $status === 'los' ? null : round(random_int(0, 50) / 10, 2),
            'tested_at' => now()->toISOString(),
        ];

        if ($status === 'los') {
            Event::dispatch(ONULosAlarmEvent::create(
                (string) $onuId,
                $onu->serial_number ?? '',
                (string) ($onu->olt_id ?? ''),
                'high',
                (string) ($onu->customer_id ?? ''),
                new \DateTimeImmutable(),
            ));
        }

        Log::info('ONU LOS Test', ['user_id' => $userId, 'result' => $result]);
        return $result;
    }

    public function exportCsvTab(string $tab, $rows): StreamedResponse
    {
        $tabLabels = [
            'olt' => 'OLT',
            'onu' => 'ONU',
            'odp' => 'ODP',
            'odc' => 'ODC',
            'pop' => 'POP',
            'fiber' => 'FiberCable',
            'los' => 'LOS_Alarm',
        ];
        $filename = $tabLabels[$tab] ?? 'Fiber' . '_' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($rows, $tab) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            $headerFn = match ($tab) {
                'olt' => fn() => ['ID', 'Nama', 'Host', 'POP', 'Port PON', 'Total ONU', 'ONU Online', 'Status', 'CPU%', 'MEM%'],
                'onu' => fn() => ['SN', 'Nomor Seri', 'Pelanggan', 'OLT', 'PON Port', 'ODP', 'Status', 'RX(dBm)', 'TX(dBm)', 'Last Reg'],
                'odp' => fn() => ['Nama', 'Lokasi', 'POP', 'OLT', 'Splitter', 'Port Total', 'Port Used'],
                'odc' => fn() => ['Nama', 'Lokasi', 'POP', 'Rak', 'Port Total', 'Port Used'],
                'pop' => fn() => ['Nama', 'Alamat', 'Koordinat', 'OLT Total'],
                'fiber' => fn() => ['Kode', 'Awal', 'Akhir', 'Tipe', 'Panjang(m)', 'Loss(dB)'],
                'los' => fn() => ['ONU SN', 'Pelanggan', 'OLT', 'Sejak LOS', 'Durasi(jam)', 'Severity'],
                default => fn() => ['Data'],
            };
            fputcsv($handle, $headerFn());

            $rowFn = match ($tab) {
                'olt' => fn($r) => [$r->id, $r->name, $r->host, $r->pop->name ?? '-', $r->ponPorts->count() ?? 0, $r->onus_count ?? 0, $r->onus_online ?? 0, $r->status, $r->cpu_pct ?? 0, $r->mem_pct ?? 0],
                'onu' => fn($r) => [$r->code ?? $r->id, $r->serial_number, $r->customer->name ?? '-', $r->olt->name ?? '-', $r->pon_port, $r->odp->name ?? '-', $r->status, $r->rx_power, $r->tx_power, $r->last_registered_at],
                'odp' => fn($r) => [$r->name, $r->location ?? '-', $r->pop->name ?? '-', $r->olt->name ?? '-', $r->splitter->name ?? '-', $r->port_total ?? 0, $r->port_used ?? 0],
                'odc' => fn($r) => [$r->name, $r->location ?? '-', $r->pop->name ?? '-', $r->rack->name ?? '-', $r->port_total ?? 0, $r->port_used ?? 0],
                'pop' => fn($r) => [$r->name, $r->address, $r->latitude . ',' . $r->longitude, $r->olts->count()],
                'fiber' => fn($r) => [$r->code, $r->startOdc->name ?? '-', $r->endOdc->name ?? '-', $r->type, $r->length, $r->loss_db ?? 0],
                'los' => fn($r) => [$r->serial_number, $r->customer->name ?? '-', $r->olt->name ?? '-', $r->last_seen_at, $r->los_duration_hours ?? 0, $r->los_severity ?? 'high'],
                default => fn($r) => [json_encode($r->toArray())],
            };

            foreach ($rows as $r) {
                fputcsv($handle, $rowFn($r));
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function getPopOptions(): array
    {
        return Pop::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getOltOptions(): array
    {
        return Olt::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getVendorOptions(): array
    {
        return \App\Models\ISP\Vendor::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getTechnicianOptions(): array
    {
        return \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['technician', 'staff', 'admin']))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
