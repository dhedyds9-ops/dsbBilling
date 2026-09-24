<?php

namespace App\Livewire\Isp\IpPool;

use App\Integration\MikroTik\Services\RouterOSService;
use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\IpPool;
use App\Models\ISP\Pop;
use App\Models\ISP\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Index extends BaseNetworkComponent
{
    public bool $showSyncModal = false;
    public ?int $selectedRouterId = null;

    public array $selectedIds = [];
    public bool $selectAll = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'ip-pools';
        $this->filters = ['status' => '', 'type' => '', 'pop_id' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Paket & Layanan'],
            ['label' => 'IP Pool'],
        ];
    }

    public function updatedSelectAll($val)
    {
        $pools = IpPool::active()->limit(500)->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->selectedIds = $val ? $pools : [];
    }

    public function openSyncModal()
    {
        $this->selectedRouterId = null;
        $this->showSyncModal = true;
    }

    public function closeSyncModal()
    {
        $this->showSyncModal = false;
    }

    public function syncToMikrotik(?int $poolId = null)
    {
        try {
            $ids = $poolId ? [$poolId] : ($this->selectedIds ?: []);
            $query = IpPool::with('pop')
                ->when($this->selectedRouterId, fn($q) => $q->where('pop_id', $this->selectedRouterId))
                ->when($ids, fn($q) => $q->whereIn('id', $ids));

            $pools = $query->get();
            if ($pools->isEmpty()) {
                session()->flash('warning', 'Tidak ada IP Pool untuk disinkronkan.');
                $this->closeSyncModal();
                return;
            }

            $grouped = $pools->groupBy(fn($p) => $p->pop_id ?: 'unassigned');
            $routerService = app(RouterOSService::class);
            $syncedCount = 0;
            $failedCount = 0;
            $processedRouterIds = [];

            foreach ($grouped as $popId => $routerPools) {
                if ($popId === 'unassigned') {
                    $failedCount += $routerPools->count();
                    continue;
                }
                $pop = $routerPools->first()->pop;
                $router = $pop ? Router::find($pop->router_id) : null;
                if (!$router || !$router instanceof Router) {
                    $failedCount += $routerPools->count();
                    continue;
                }
                try {
                    $driver = $routerService->getDriver($router);
                    if (!$driver->connect()) {
                        Log::warning('Gagal connect Router saat sync IP Pool', ['router_id' => $router->id]);
                        $failedCount += $routerPools->count();
                        continue;
                    }
                    $processedRouterIds[] = $router->id;
                } catch (Throwable $e) {
                    Log::warning('Connect Router Gagal sync IP Pool', ['router_id' => $router->id, 'err' => $e->getMessage()]);
                    $failedCount += $routerPools->count();
                    continue;
                }

                $defaultDns = '8.8.8.8,8.8.4.4';

                foreach ($routerPools as $pool) {
                    try {
                        $isPpp = !empty($pool->start_ip) && !empty($pool->end_ip);
                        $name = (string)$pool->name;
                        $comment = 'dsB:' . ($pool->code ?: 'POOL') . '|id:' . $pool->id;

                        if ($isPpp) {
                            $ranges = $pool->start_ip . '-' . $pool->end_ip;
                            $okPool = $driver->updateIpPool($name, $ranges, $comment);
                            if (!$okPool) {
                                Log::warning('Gagal update IP Pool di Router', ['router_id' => $router->id, 'pool' => $name]);
                                $failedCount++;
                                continue;
                            }
                        }

                        $pool->timestamps = false;
                        $pool->updated_by = Auth::id();
                        $pool->saveQuietly();
                        $syncedCount++;
                    } catch (Throwable $e) {
                        Log::warning('Sync 1 IP Pool Gagal', ['pool_id' => $pool->id, 'err' => $e->getMessage()]);
                        $failedCount++;
                    }
                }
                try { $driver->disconnect(); } catch (Throwable $e) {}
            }

            Log::info('Sync IP Pool ke MikroTik SELESAI', [
                'synced' => $syncedCount,
                'failed' => $failedCount,
                'routers' => $processedRouterIds,
            ]);

            $parts = [];
            if ($syncedCount > 0) $parts[] = "{$syncedCount} berhasil";
            if ($failedCount > 0) $parts[] = "{$failedCount} gagal";
            $msg = 'Sinkronisasi IP Pool selesai: ' . implode(', ', $parts) . '.';
            if ($failedCount === 0 && $syncedCount > 0) {
                $msg = $poolId
                    ? '1 IP Pool berhasil disinkronkan ke Router!'
                    : "{$syncedCount} IP Pool berhasil disinkronkan ke " . count($processedRouterIds) . " Router!";
                session()->flash('success', $msg);
            } elseif ($syncedCount > 0) {
                session()->flash('warning', $msg);
            } else {
                session()->flash('error', $msg);
            }
            $this->closeSyncModal();
            $this->selectedIds = [];
            $this->selectAll = false;
        } catch (Throwable $e) {
            Log::error('Sync IP Pool Gagal FATAL', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal sync: ' . $e->getMessage());
        }
    }

    public function syncAll()
    {
        $this->selectedIds = [];
        $this->syncToMikrotik();
    }

    public function exportFromMikrotik()
    {
        try {
            $routers = Router::active()->get();
            if ($routers->isEmpty()) {
                session()->flash('warning', 'Tidak ada Router MikroTik aktif untuk diimport.');
                return;
            }
            $routerService = app(RouterOSService::class);
            $userId = Auth::id();
            $now = now();
            $imported = 0;
            $updated = 0;
            $failedRouters = 0;
            $seenNames = [];

            foreach ($routers as $router) {
                try {
                    $driver = $routerService->getDriver($router);
                    if (!$driver->connect()) {
                        Log::warning('Gagal connect Router saat import IP Pool', ['router_id' => $router->id]);
                        $failedRouters++;
                        continue;
                    }
                } catch (Throwable $e) {
                    Log::warning('Connect Router Gagal import IP Pool', ['router_id' => $router->id, 'err' => $e->getMessage()]);
                    $failedRouters++;
                    continue;
                }

                $routerId = $router->id;
                $pop = Pop::where('router_id', $routerId)->first();
                $popId = $pop?->id;
                $host = $router->ip_address ?: '';

                try { $pools = $driver->getPools(); } catch (Throwable $e) { $pools = []; Log::warning('getPools gagal', ['rtr' => $routerId, 'err' => $e->getMessage()]); }

                foreach ($pools as $row) {
                    $name = trim((string)($row['name'] ?? ''));
                    if ($name === '' || $name === 'default') continue;
                    $ranges = (string)($row['ranges'] ?? '');
                    $comment = (string)($row['comment'] ?? '');
                    $nextPool = (string)($row['next-pool'] ?? '');

                    $startIp = $endIp = $gateway = $network = $netmask = null;
                    $totalIps = 0;
                    $segments = array_filter(array_map('trim', explode(',', $ranges)));
                    $firstSegment = $segments[0] ?? '';
                    if (str_contains($firstSegment, '-')) {
                        [$s, $e] = array_map('trim', explode('-', $firstSegment, 2));
                        $startIp = $s;
                        $endIp = $e;
                        try {
                            $sLong = ip2long($s);
                            $eLong = ip2long($e);
                            if ($sLong !== false && $eLong !== false && $eLong >= $sLong) {
                                $totalIps = $eLong - $sLong + 1;
                            }
                            $longParts = explode('.', $s);
                            if (count($longParts) === 4) {
                                $gateway = $longParts[0] . '.' . $longParts[1] . '.' . $longParts[2] . '.1';
                                $network = $longParts[0] . '.' . $longParts[1] . '.' . $longParts[2] . '.0';
                                $netmask = '255.255.255.0';
                            }
                        } catch (Throwable $e) {}
                    }
                    foreach (array_slice($segments, 1) as $seg) {
                        if (str_contains($seg, '-')) {
                            [$s2, $e2] = array_map('trim', explode('-', $seg, 2));
                            $sl2 = ip2long($s2); $el2 = ip2long($e2);
                            if ($sl2 !== false && $el2 !== false && $el2 >= $sl2) $totalIps += ($el2 - $sl2 + 1);
                        }
                    }

                    $code = 'RTR' . $routerId . 'POOL' . strtoupper(preg_replace('/[^A-Z0-9]/', '', $name));
                    $code = substr($code, 0, 40);
                    $uniq = 1;
                    $baseCode = $code;
                    while (isset($seenNames[$code]) || IpPool::where('code', $code)->exists()) {
                        $code = substr($baseCode, 0, 36) . $uniq++;
                    }
                    $seenNames[$code] = true;

                    $meta = [
                        'module' => 'mikrotik-ippool',
                        'type' => 'ppp',
                        'router_host' => $host,
                        'router_id' => $routerId,
                        'source' => 'import:ip/pool',
                        'original_ranges' => $ranges,
                    ];
                    if ($nextPool) $meta['next_pool'] = $nextPool;
                    $desc = trim($comment);
                    $desc = ($desc ? $desc . ' | ' : '') . 'meta:' . json_encode($meta, JSON_UNESCAPED_SLASHES);

                    $payload = [
                        'name' => $name,
                        'pop_id' => $popId,
                        'gateway' => $gateway,
                        'start_ip' => $startIp,
                        'end_ip' => $endIp,
                        'netmask' => $netmask,
                        'network' => $network,
                        'total_ips' => $totalIps,
                        'used_ips' => 0,
                        'description' => $desc,
                        'status' => 'active',
                        'updated_by' => $userId,
                        'updated_at' => $now,
                    ];

                    $existing = IpPool::where('code', $code)->first();
                    if ($existing) {
                        $existing->update($payload);
                        $updated++;
                    } else {
                        $payload['code'] = $code;
                        $payload['created_by'] = $userId;
                        $payload['created_at'] = $now;
                        IpPool::create($payload);
                        $imported++;
                    }
                }

                try { $driver->disconnect(); } catch (Throwable $e) {}
            }

            $okCount = $routers->count() - $failedRouters;
            $msg = "Import dari {$okCount}/{$routers->count()} Router berhasil: {$imported} baru, {$updated} diperbarui";
            if ($failedRouters > 0) $msg .= ", {$failedRouters} router gagal konek.";
            else $msg .= '.';
            Log::info('Import IP Pool dari MikroTik selesai', compact('imported', 'updated', 'failedRouters'));
            if ($failedRouters > 0 && ($imported + $updated) === 0) {
                session()->flash('error', $msg);
            } elseif ($failedRouters > 0) {
                session()->flash('warning', $msg);
            } else {
                session()->flash('success', $msg);
            }
        } catch (Throwable $e) {
            Log::error('Import IP Pool Gagal FATAL', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $pool = IpPool::findOrFail($id);
            $pool->status = $pool->status === 'active' ? 'inactive' : 'active';
            $pool->updated_by = Auth::id();
            $pool->save();
            session()->flash('success', sprintf('Status %s diubah menjadi %s.', $pool->name, strtoupper($pool->status)));
        } catch (Throwable $e) {
            Log::error('Toggle IP Pool Gagal', ['id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal ubah status: ' . $e->getMessage());
        }
    }

    public function bulkEnable()
    {
        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Pilih minimal satu IP Pool.');
                return;
            }
            $count = IpPool::whereIn('id', $this->selectedIds)->update([
                'status' => 'active',
                'updated_by' => Auth::id(),
            ]);
            $this->selectedIds = [];
            $this->selectAll = false;
            session()->flash('success', "{$count} IP Pool berhasil diaktifkan.");
        } catch (Throwable $e) {
            Log::error('Bulk Enable IP Pool Gagal', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal bulk enable: ' . $e->getMessage());
        }
    }

    public function bulkDisable()
    {
        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Pilih minimal satu IP Pool.');
                return;
            }
            $count = IpPool::whereIn('id', $this->selectedIds)->update([
                'status' => 'inactive',
                'updated_by' => Auth::id(),
            ]);
            $this->selectedIds = [];
            $this->selectAll = false;
            session()->flash('success', "{$count} IP Pool berhasil dinonaktifkan.");
        } catch (Throwable $e) {
            Log::error('Bulk Disable IP Pool Gagal', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal bulk disable: ' . $e->getMessage());
        }
    }

    public function bulkSync()
    {
        $this->syncToMikrotik();
    }

    public function bulkDelete()
    {
        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Pilih minimal satu IP Pool.');
                return;
            }
            $count = IpPool::whereIn('id', $this->selectedIds)->delete();
            $this->selectedIds = [];
            $this->selectAll = false;
            session()->flash('success', "{$count} IP Pool terpilih berhasil dihapus.");
        } catch (Throwable $e) {
            Log::error('Bulk Delete IP Pool Gagal', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal bulk delete: ' . $e->getMessage());
        }
    }

    public function bulkExport()
    {
        try {
            $ids = empty($this->selectedIds) ? null : $this->selectedIds;
            $pools = IpPool::with('pop')
                ->when($ids, fn($q) => $q->whereIn('id', $ids))
                ->get();

            if ($pools->isEmpty()) {
                session()->flash('warning', 'Tidak ada data untuk diexport.');
                return;
            }

            $filename = 'ip-pools-' . date('Y-m-d-His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($pools) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Code', 'Name', 'POP', 'Network', 'Gateway', 'Start IP', 'End IP', 'Total IPs', 'Used IPs', 'Status', 'Created At']);
                foreach ($pools as $p) {
                    fputcsv($file, [
                        $p->id,
                        $p->code,
                        $p->name,
                        optional($p->pop)->name ?? '-',
                        $p->network ?? '-',
                        $p->gateway ?? '-',
                        $p->start_ip ?? '-',
                        $p->end_ip ?? '-',
                        $p->total_ips ?? 0,
                        $p->used_ips ?? 0,
                        $p->status,
                        $p->created_at,
                    ]);
                }
                fclose($file);
            };

            $this->selectedIds = [];
            $this->selectAll = false;
            session()->flash('success', "Export {$pools->count()} IP Pool berhasil diunduh.");
            return response()->stream($callback, 200, $headers);
        } catch (Throwable $e) {
            Log::error('Bulk Export IP Pool Gagal', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal export: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $pool = IpPool::findOrFail($id);
            $nama = $pool->name;
            $pool->delete();
            session()->flash('success', "IP Pool '{$nama}' berhasil dihapus.");
        } catch (Throwable $e) {
            Log::error('Delete IP Pool Gagal', ['id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $pops = Pop::active()->with('router')->get(['id', 'name', 'router_id']);
        $routers = Router::active()->get(['id', 'name', 'ip_address']);

        $query = IpPool::with(['pop', 'createdBy'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('gateway', 'like', "%{$this->search}%")
                        ->orWhere('start_ip', 'like', "%{$this->search}%")
                        ->orWhere('network', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($this->filters['type'] ?? null, function ($q, $v) {
                if ($v === 'ppp') return $q->whereNotNull('start_ip');
                if ($v === 'hotspot') return $q->whereNull('start_ip');
                return $q;
            })
            ->when($this->filters['pop_id'] ?? null, fn($q, $v) => $q->where('pop_id', $v));

        $cloneForStats = clone $query;
        $allForStats = $cloneForStats->get(['start_ip', 'end_ip', 'total_ips', 'used_ips', 'status', 'pop_id']);
        $summary = [
            'total' => $allForStats->count(),
            'active' => $allForStats->where('status', 'active')->count(),
            'used' => (int)$allForStats->sum('used_ips'),
            'free' => (int)max(0, $allForStats->sum('total_ips') - $allForStats->sum('used_ips')),
        ];

        $ipPools = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage === 'All' ? 99999 : $this->perPage);

        return view('livewire.isp.ip-pool.index', compact('ipPools', 'pops', 'routers', 'summary'));
    }
}
