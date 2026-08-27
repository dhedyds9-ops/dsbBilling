<?php

namespace App\Livewire\ProfilePaket;

use App\Integration\MikroTik\Services\RouterOSService;
use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\IpPool;
use App\Models\ISP\Router;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class GrupProfile extends BaseNetworkComponent
{
    public bool $showSyncModal = false;
    public bool $showCreateModal = false;
    public ?int $selectedRouterId = null;
    public ?int $editingId = null;

    public array $selectedIds = [];
    public bool $selectAll = false;

    public array $form = [
        'name' => '',
        'code' => '',
        'type' => 'ppp',
        'module' => 'mikrotik-ippool',
        'parent_pool_id' => null,
        'router_id' => null,
        'owner_user_id' => null,
        'gateway' => '',
        'start_ip' => '',
        'end_ip' => '',
        'netmask' => '255.255.255.0',
        'network' => '',
        'description' => '',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'profile-paket';
        $this->activePage = 'grup-profile';
        $this->filters = ['type' => '', 'status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Profile Paket'],
            ['label' => 'Grup Profile'],
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

    public function openCreateModal()
    {
        $this->editingId = null;
        $this->resetErrorBag();
        $this->form = [
            'name' => '',
            'code' => strtoupper('GRP_' . substr(uniqid(), -6)),
            'type' => 'ppp',
            'module' => 'mikrotik-ippool',
            'parent_pool_id' => null,
            'router_id' => null,
            'owner_user_id' => Auth::id(),
            'gateway' => '',
            'start_ip' => '',
            'end_ip' => '',
            'netmask' => '255.255.255.0',
            'network' => '',
            'description' => '',
        ];
        $this->showCreateModal = true;
    }

    public function updatedFormType($val)
    {
        if ($val === 'hotspot') {
            $this->form['gateway'] = '';
            $this->form['start_ip'] = '';
            $this->form['end_ip'] = '';
            $this->form['module'] = 'GROUP ONLY';
        } else {
            $this->form['module'] = 'mikrotik-ippool';
        }
    }

    public function openEditModal(int $id)
    {
        try {
            $this->resetErrorBag();
            $pool = IpPool::with(['pop', 'createdBy'])->findOrFail($id);
            $isHotspot = !$pool->start_ip;
            $this->editingId = $id;
            $this->form = [
                'name' => (string)$pool->name,
                'code' => (string)$pool->code,
                'type' => $isHotspot ? 'hotspot' : 'ppp',
                'module' => $isHotspot ? 'GROUP ONLY' : 'mikrotik-ippool',
                'parent_pool_id' => null,
                'router_id' => $pool->pop_id,
                'owner_user_id' => $pool->created_by,
                'gateway' => (string)($pool->gateway ?? ''),
                'start_ip' => (string)($pool->start_ip ?? ''),
                'end_ip' => (string)($pool->end_ip ?? ''),
                'netmask' => (string)($pool->netmask ?? '255.255.255.0'),
                'network' => (string)($pool->network ?? ''),
                'description' => (string)($pool->description ?? ''),
            ];
            $this->showCreateModal = true;
        } catch (Throwable $e) {
            Log::error('Open Edit Grup Profile Gagal', ['id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal membuka edit: ' . $e->getMessage());
        }
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->editingId = null;
    }

    public function syncToMikrotik(?int $profileId = null)
    {
        try {
            $ids = $profileId ? [$profileId] : ($this->selectedIds ?: []);
            $query = IpPool::with('pop')
                ->when($this->selectedRouterId, fn($q) => $q->where('pop_id', $this->selectedRouterId))
                ->when($ids, fn($q) => $q->whereIn('id', $ids));

            $pools = $query->get();
            if ($pools->isEmpty()) {
                session()->flash('warning', 'Tidak ada Grup Profile untuk disinkronkan.');
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
                $router = $routerPools->first()->pop;
                if (!$router || !$router instanceof Router) {
                    $failedCount += $routerPools->count();
                    continue;
                }
                try {
                    $driver = $routerService->getDriver($router);
                    if (!$driver->connect()) {
                        Log::warning('Gagal connect Router saat sync Grup Profile', ['router_id' => $router->id]);
                        $failedCount += $routerPools->count();
                        continue;
                    }
                    $processedRouterIds[] = $router->id;
                } catch (Throwable $e) {
                    Log::warning('Connect Router Gagal sync Grup Profile', ['router_id' => $router->id, 'err' => $e->getMessage()]);
                    $failedCount += $routerPools->count();
                    continue;
                }

                $defaultDns = '8.8.8.8,8.8.4.4';

                foreach ($routerPools as $pool) {
                    try {
                        $isPpp = !empty($pool->start_ip) && !empty($pool->end_ip);
                        $name = (string)$pool->name;
                        $comment = 'dsB:' . ($pool->code ?: 'GRP') . '|id:' . $pool->id;

                        if ($isPpp) {
                            $ranges = $pool->start_ip . '-' . $pool->end_ip;
                            $nextPool = $pool->parent_pool_id ? (IpPool::find($pool->parent_pool_id)?->name ?: null) : null;

                            $okPool = $driver->updateIpPool($name, $ranges, $comment, $nextPool);
                            if (!$okPool) {
                                Log::warning('Gagal update IP Pool di Router', ['router_id' => $router->id, 'pool' => $name]);
                                $failedCount++;
                                continue;
                            }

                            $pppOptions = [
                                'local-address' => $pool->gateway ?: null,
                                'remote-address' => $name,
                                'use-encryption' => 'yes',
                                'dns-server' => $defaultDns,
                                'comment' => $comment,
                            ];
                            $okProfile = $driver->updatePppProfile($name, $pppOptions);
                            if (!$okProfile) {
                                Log::warning('Gagal update PPP Profile di Router', ['router_id' => $router->id, 'profile' => $name]);
                                $failedCount++;
                                continue;
                            }
                        } else {
                            $hotspotOptions = [
                                'shared-users' => 1,
                                'address-pool' => 'none',
                                'comment' => $comment,
                            ];
                            $okHs = $driver->updateHotspotUserProfile($name, $hotspotOptions);
                            if (!$okHs) {
                                Log::warning('Gagal update Hotspot Profile di Router', ['router_id' => $router->id, 'profile' => $name]);
                                $failedCount++;
                                continue;
                            }
                        }

                        $pool->timestamps = false;
                        $pool->updated_by = Auth::id();
                        $pool->saveQuietly();
                        $syncedCount++;
                    } catch (Throwable $e) {
                        Log::warning('Sync 1 Grup Profile Gagal', ['pool_id' => $pool->id, 'err' => $e->getMessage()]);
                        $failedCount++;
                    }
                }
                try { $driver->disconnect(); } catch (Throwable $e) {}
            }

            Log::info('Sync Grup Profile (IP Pool) ke MikroTik SELESAI', [
                'synced' => $syncedCount,
                'failed' => $failedCount,
                'routers' => $processedRouterIds,
            ]);

            $parts = [];
            if ($syncedCount > 0) $parts[] = "{$syncedCount} berhasil";
            if ($failedCount > 0) $parts[] = "{$failedCount} gagal";
            $msg = 'Sinkronisasi Grup Profile selesai: ' . implode(', ', $parts) . '.';
            if ($failedCount === 0 && $syncedCount > 0) {
                $msg = $profileId
                    ? '1 Grup Profile berhasil disinkronkan ke Router!'
                    : "{$syncedCount} Grup Profile berhasil disinkronkan ke " . count($processedRouterIds) . " Router!";
                session()->flash('success', $msg);
            } elseif ($syncedCount > 0) {
                session()->flash('warning', $msg);
            } else {
                session()->flash('error', $msg);
            }
            $this->closeSyncModal();
        } catch (Throwable $e) {
            Log::error('Sync Grup Profile Gagal FATAL', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal export: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $isPpp = $this->form['type'] === 'ppp';
        $rules = [
            'form.name' => 'required|string|max:150',
            'form.type' => 'required|in:ppp,hotspot',
            'form.code' => 'required|string|max:50',
            'form.router_id' => 'nullable|exists:routers,id',
            'form.owner_user_id' => 'nullable|exists:users,id',
        ];
        if ($isPpp) {
            $rules['form.gateway'] = 'required|ip';
            $rules['form.start_ip'] = 'required|ip';
            $rules['form.end_ip'] = 'required|ip';
            $rules['form.netmask'] = 'required|string|max:20';
        }
        $this->validate($rules);

        try {
            $user = Auth::user();

            $module = $isPpp ? 'mikrotik-ippool' : 'GROUP ONLY';
            $meta = [
                'module' => $module,
                'type' => $this->form['type'],
            ];
            if ($this->form['parent_pool_id']) $meta['parent_pool_id'] = (int)$this->form['parent_pool_id'];
            if ($this->form['owner_user_id']) $meta['owner_uid'] = (int)$this->form['owner_user_id'];

            $desc = trim($this->form['description'] . ' | meta:' . json_encode($meta, JSON_UNESCAPED_SLASHES));
            if ($desc === ' | meta:' . json_encode($meta, JSON_UNESCAPED_SLASHES)) {
                $desc = str_replace(' | meta:', 'meta:', $desc);
            }

            $codeUnique = $this->form['code'];
            $baseCode = $codeUnique;
            $suffix = 1;
            $currentId = $this->editingId;
            while (IpPool::where('code', $codeUnique)->when($currentId, fn($q) => $q->where('id', '!=', $currentId))->exists()) {
                $codeUnique = $baseCode . '_' . $suffix++;
            }

            $totalIps = 0;
            if ($isPpp && $this->form['start_ip'] && $this->form['end_ip']) {
                $totalIps = max(0, (int)(ip2long($this->form['end_ip']) - ip2long($this->form['start_ip']) + 1));
            }

            $payload = [
                'name' => $this->form['name'],
                'code' => $codeUnique,
                'pop_id' => $this->form['router_id'] ?: null,
                'gateway' => $isPpp ? $this->form['gateway'] : null,
                'start_ip' => $isPpp ? $this->form['start_ip'] : null,
                'end_ip' => $isPpp ? $this->form['end_ip'] : null,
                'netmask' => $isPpp ? $this->form['netmask'] : null,
                'network' => $isPpp && $this->form['network'] ? $this->form['network'] : null,
                'description' => $desc,
                'total_ips' => $totalIps,
                'used_ips' => $this->editingId ? null : 0,
            ];

            if ($this->editingId) {
                $pool = IpPool::findOrFail($this->editingId);
                $payload['updated_by'] = $user->id;
                unset($payload['used_ips']);
                $pool->update($payload);
                $msg = 'Grup Profile berhasil diperbarui!';
            } else {
                $payload['status'] = 'active';
                $payload['used_ips'] = 0;
                $payload['created_by'] = $user->id;
                IpPool::create($payload);
                $msg = 'Grup Profile berhasil dibuat!';
            }

            session()->flash('success', $msg);
            $this->closeCreateModal();
        } catch (Throwable $e) {
            Log::error('Save Grup Profile Gagal', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
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
            Log::error('Toggle Grup Profile Gagal', ['id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal ubah status: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $pool = IpPool::findOrFail($id);
            $nama = $pool->name;
            $pool->delete();
            session()->flash('success', "Grup Profile '{$nama}' berhasil dihapus.");
        } catch (Throwable $e) {
            Log::error('Delete Grup Profile Gagal', ['id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function bulkDelete()
    {
        try {
            if (empty($this->selectedIds)) {
                session()->flash('error', 'Pilih minimal satu grup profile.');
                return;
            }
            $count = IpPool::whereIn('id', $this->selectedIds)->delete();
            $this->selectedIds = [];
            $this->selectAll = false;
            session()->flash('success', "{$count} Grup Profile terpilih berhasil dihapus.");
        } catch (Throwable $e) {
            Log::error('Bulk Delete Grup Profile Gagal', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal bulk delete: ' . $e->getMessage());
        }
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
                        Log::warning('Gagal connect Router saat import Grup Profile', ['router_id' => $router->id]);
                        $failedRouters++;
                        continue;
                    }
                } catch (Throwable $e) {
                    Log::warning('Connect Router Gagal import Grup Profile', ['router_id' => $router->id, 'err' => $e->getMessage()]);
                    $failedRouters++;
                    continue;
                }

                $routerId = $router->id;
                $host = $router->ip_address ?: '';
                $dnsDefault = '8.8.8.8,8.8.4.4';

                try { $pools = $driver->getPools(); } catch (Throwable $e) { $pools = []; Log::warning('getPools gagal', ['rtr' => $routerId, 'err' => $e->getMessage()]); }
                try { $pppProfiles = $driver->getPppProfiles(); } catch (Throwable $e) { $pppProfiles = []; Log::warning('getPppProfiles gagal', ['rtr' => $routerId, 'err' => $e->getMessage()]); }
                try { $hsProfiles = $driver->getHotspotUserProfiles(); } catch (Throwable $e) { $hsProfiles = []; Log::warning('getHotspotUserProfiles gagal', ['rtr' => $routerId, 'err' => $e->getMessage()]); }

                $poolMap = [];
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
                        'pop_id' => $routerId,
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
                    $poolMap[$name] = true;
                }

                foreach ($pppProfiles as $row) {
                    $name = trim((string)($row['name'] ?? ''));
                    if ($name === '' || stripos($name, 'default') === 0) continue;
                    if (isset($poolMap[$name])) continue;

                    $localAddr = (string)($row['local-address'] ?? '');
                    $remoteAddr = (string)($row['remote-address'] ?? '');
                    $dns = (string)($row['dns-server'] ?? $dnsDefault);
                    $comment = (string)($row['comment'] ?? '');

                    $startIp = $endIp = $gateway = $network = $netmask = null;
                    $totalIps = 0;
                    if ($localAddr && filter_var($localAddr, FILTER_VALIDATE_IP)) {
                        $gateway = $localAddr;
                        $parts = explode('.', $localAddr);
                        if (count($parts) === 4) {
                            $network = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0';
                            $netmask = '255.255.255.0';
                        }
                    }

                    $code = 'RTR' . $routerId . 'PPP' . strtoupper(preg_replace('/[^A-Z0-9]/', '', $name));
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
                        'source' => 'import:ppp/profile',
                        'local_address' => $localAddr,
                        'remote_address' => $remoteAddr,
                        'dns_server' => $dns,
                    ];
                    $desc = trim($comment);
                    $desc = ($desc ? $desc . ' | ' : '') . 'meta:' . json_encode($meta, JSON_UNESCAPED_SLASHES);

                    $payload = [
                        'name' => $name,
                        'pop_id' => $routerId,
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

                foreach ($hsProfiles as $row) {
                    $name = trim((string)($row['name'] ?? ''));
                    if ($name === '' || stripos($name, 'default') === 0) continue;

                    $shared = (string)($row['shared-users'] ?? '1');
                    $addrPool = (string)($row['address-pool'] ?? 'none');
                    $comment = (string)($row['comment'] ?? '');

                    $code = 'RTR' . $routerId . 'HSP' . strtoupper(preg_replace('/[^A-Z0-9]/', '', $name));
                    $code = substr($code, 0, 40);
                    $uniq = 1;
                    $baseCode = $code;
                    while (isset($seenNames[$code]) || IpPool::where('code', $code)->exists()) {
                        $code = substr($baseCode, 0, 36) . $uniq++;
                    }
                    $seenNames[$code] = true;

                    $meta = [
                        'module' => 'GROUP ONLY',
                        'type' => 'hotspot',
                        'router_host' => $host,
                        'router_id' => $routerId,
                        'source' => 'import:hotspot/user/profile',
                        'shared_users' => $shared,
                        'address_pool' => $addrPool,
                    ];
                    $desc = trim($comment);
                    $desc = ($desc ? $desc . ' | ' : '') . 'meta:' . json_encode($meta, JSON_UNESCAPED_SLASHES);

                    $payload = [
                        'name' => $name,
                        'pop_id' => $routerId,
                        'description' => $desc,
                        'total_ips' => 0,
                        'used_ips' => 0,
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
            Log::info('Import Grup Profile dari MikroTik selesai', compact('imported', 'updated', 'failedRouters'));
            if ($failedRouters > 0 && ($imported + $updated) === 0) {
                session()->flash('error', $msg);
            } elseif ($failedRouters > 0) {
                session()->flash('warning', $msg);
            } else {
                session()->flash('success', $msg);
            }
        } catch (Throwable $e) {
            Log::error('Import Grup Profile Gagal FATAL', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $routers = Router::active()->get(['id', 'name', 'ip_address']);
        $owners = User::limit(50)->get(['id', 'name', 'username']);
        $parentPools = IpPool::active()->limit(100)->get(['id', 'name', 'code']);

        $query = IpPool::with(['pop', 'createdBy'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('gateway', 'like', "%{$this->search}%")
                        ->orWhere('start_ip', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filters['type'] ?? null, function ($q, $v) {
                if ($v === 'ppp') return $q->whereNotNull('start_ip');
                if ($v === 'hotspot') return $q->whereNull('start_ip');
                return $q;
            })
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v));

        $cloneForStats = clone $query;
        $allForStats = $cloneForStats->get(['start_ip', 'end_ip', 'total_ips', 'pop_id']);
        $stats = [
            'total' => $allForStats->count(),
            'ppp' => $allForStats->whereNotNull('start_ip')->count(),
            'hotspot' => $allForStats->whereNull('start_ip')->count(),
            'routers_count' => $allForStats->pluck('pop_id')->filter()->unique()->count(),
            'available_ips' => (int)$allForStats->sum('total_ips'),
        ];

        $groups = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage === 'All' ? 99999 : $this->perPage);

        return view('livewire.profile-paket.grup-profile', compact('groups', 'routers', 'owners', 'parentPools', 'stats'));
    }
}
