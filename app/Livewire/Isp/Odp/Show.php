<?php

namespace App\Livewire\Isp\Odp;

use App\Livewire\AdminComponent;
use App\Models\AuditLog;
use App\Models\CRM\Customer;
use App\Models\ISP\AccessPoint;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Odp as OdpModel;
use App\Models\ISP\Onu;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\Splitter;
use App\Models\ISP\SplitterPort;
use App\Models\Support\Ticket;
use App\Services\ISP\GenieAcsDiagnosticService;
use App\Services\ISP\NodePhotoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Show extends AdminComponent
{
    public $odpId;
    public $odp;
    public string $activeTab = 'overview'; // 'overview', 'topology', 'ports', 'onus', 'customers', 'history'

    // Customer Filter
    public $customerSearch = '';
    public $customerStatusFilter = '';

    // Port Modal State
    public $showPortModal = false;
    public $selectedPortNumber = null;
    public $selectedPort = null;
    public $portFormStatus = 'available'; // available, used, reserved, fault
    public $portFormNotes = '';
    public $portFormDropcore = '';
    public $portFormCustomerId = '';
    public $portFormOnuId = '';
    public $targetMovePort = null;

    // GenieACS Live Modal State
    public $showGenieAcsModal = false;
    public $selectedOnuId = null;
    public $selectedOnu = null;
    public $selectedOnuLive = null;
    public $isLoadingGenieAcs = false;
    public $genieAcsMessage = null;

    // Splitter Management State (PLC & FBT in ODP)
    public bool $showSplitterModal = false;
    public string $splitterName = '';
    public string $splitterCode = '';
    public string $splitterType = 'plc'; // 'plc', 'fbt'
    public string $splitterRatio = '1:8';
    public string $splitterPackage = 'abs_box'; // 'abs_box', 'mini_tube', 'cassette'
    public int $splitterInputPorts = 1;
    public int $splitterPortCount = 8;
    public ?float $splitterLossDb = 10.50;
    public string $splitterDescription = '';

    // Splitter Port Modal State (Output to ONU/AP/Customer)
    public bool $showSplitterPortModal = false;
    public ?int $selectedSplitterPortId = null;
    public ?SplitterPort $selectedSplitterPort = null;
    public string $spPortFormTargetType = 'onu'; // 'onu', 'ap'
    public ?int $spPortFormTargetId = null;
    public ?int $spPortFormCustomerId = null;
    public string $spPortFormStatus = 'available';
    public ?float $spPortFormAttenuation = null;
    public string $spPortFormCoreColor = '';
    public string $spPortFormNotes = '';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'odps';
        $this->odpId = $id;

        $this->loadOdp();

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.odps.index')],
            ['label' => 'ODP', 'url' => route('isp.odps.index')],
            ['label' => $this->odp->name],
        ];
    }

    public function loadOdp()
    {
        $this->odp = OdpModel::with([
            'odc.olt',
            'odc.splitters',
            'olt',
            'splitters.ports.target',
            'splitters.ports.customer',
            'branch',
            'reseller',
            'parent',
            'children',
        ])->findOrFail($this->odpId);
    }

    public function setTab($tab)
    {
        if (in_array($tab, ['overview', 'topology', 'ports', 'onus', 'customers', 'history'])) {
            $this->activeTab = $tab;
        }
    }

    public function getOdpAttributes(): array
    {
        $raw = $this->odp->getAttribute('attributes');
        if (is_string($raw)) {
            return json_decode($raw, true) ?: [];
        }
        return is_array($raw) ? $raw : [];
    }

    public function setOdpAttributes(array $attrs): void
    {
        $this->odp->setAttribute('attributes', $attrs);
    }

    /**
     * Compile port allocation matrix (1..port_count) based on database relations & custom overrides.
     */
    public function getPortsProperty()
    {
        $totalPorts = (int) ($this->odp->port_count ?: 8);
        $attrs = $this->getOdpAttributes();
        $customPorts = $attrs['ports'] ?? [];

        // ONUs assigned to this ODP
        $onus = Onu::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->with(['customerService.customer', 'customerService.pppoeUser', 'customerService.hotspotUser', 'acsDevice'])
            ->get();

        // PPPoE subscribers with odp_id
        $pppoeUsers = PPPoEUser::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->with(['customerService.customer', 'customerService.onu.acsDevice'])
            ->get();

        // Hotspot subscribers with odp_id
        $hotspotUsers = HotspotUser::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->with(['customerService.customer', 'customerService.onu.acsDevice'])
            ->get();

        $ports = [];

        for ($p = 1; $p <= $totalPorts; $p++) {
            $portConfig = $customPorts[$p] ?? ($customPorts[(string) $p] ?? []);
            $matchingOnu = null;
            $matchingCustomer = null;
            $matchingPppoe = null;
            $matchingHotspot = null;

            // 1. Check PPPoE
            foreach ($pppoeUsers as $pu) {
                if ((int) $pu->port_number === $p) {
                    $matchingPppoe = $pu;
                    $matchingCustomer = $pu->customerService?->customer;
                    $matchingOnu = $pu->customerService?->onu;
                    break;
                }
            }

            // 2. Check Hotspot if not found
            if (!$matchingCustomer) {
                foreach ($hotspotUsers as $hu) {
                    if ((int) $hu->port_number === $p) {
                        $matchingHotspot = $hu;
                        $matchingCustomer = $hu->customerService?->customer;
                        $matchingOnu = $hu->customerService?->onu;
                        break;
                    }
                }
            }

            // 3. Check ONUs assigned directly to this port
            if (!$matchingOnu) {
                foreach ($onus as $onu) {
                    $assignedPort = (int) ($onu->pon_port ?: ($onu->attributes['port_number'] ?? ($onu->attributes['odp_port'] ?? 0)));
                    if ($assignedPort === $p) {
                        $matchingOnu = $onu;
                        if (!$matchingCustomer && $onu->customerService?->customer) {
                            $matchingCustomer = $onu->customerService->customer;
                        }
                        if (!$matchingPppoe && $onu->customerService?->pppoeUser) {
                            $matchingPppoe = $onu->customerService->pppoeUser;
                        }
                        break;
                    }
                }
            }

            // Determine status
            $isUsed = ($matchingCustomer !== null || $matchingOnu !== null || $matchingPppoe !== null || $matchingHotspot !== null || ($portConfig['status'] ?? '') === 'used');
            $status = $isUsed ? 'used' : ($portConfig['status'] ?? 'available');

            // Resolve IP and Signal
            $ipAddress = $matchingOnu?->ip_address
                ?? $matchingPppoe?->static_ip
                ?? $matchingHotspot?->static_ip
                ?? '-';

            $rxPower = $matchingOnu?->rx_power_dbm ?? $matchingOnu?->acsDevice?->signal ?? null;
            $txPower = $matchingOnu?->tx_power_dbm ?? null;

            $serviceType = '-';
            $username = '-';
            if ($matchingPppoe) {
                $serviceType = 'PPPoE';
                $username = $matchingPppoe->username;
            } elseif ($matchingHotspot) {
                $serviceType = 'Hotspot';
                $username = $matchingHotspot->username;
            } elseif ($matchingOnu?->customerService) {
                $serviceType = 'Dedicated';
                $username = $matchingOnu->customerService->username ?? '-';
            }

            $isOnline = false;
            if ($matchingOnu) {
                $isOnline = (bool) $matchingOnu->is_online;
            } elseif ($matchingPppoe) {
                $isOnline = ($matchingPppoe->status === 'active');
            }

            $ports[$p] = [
                'port_number' => $p,
                'status' => $status,
                'customer' => $matchingCustomer ? [
                    'id' => $matchingCustomer->id,
                    'name' => $matchingCustomer->name,
                    'code' => $matchingCustomer->code,
                    'phone' => $matchingCustomer->phone ?? '-',
                    'address' => $matchingCustomer->address ?? '-',
                ] : null,
                'onu' => $matchingOnu ? [
                    'id' => $matchingOnu->id,
                    'name' => $matchingOnu->name,
                    'serial_number' => $matchingOnu->serial_number,
                    'model' => $matchingOnu->model ?: ($matchingOnu->acsDevice?->model ?? 'ONT Standard'),
                    'mac_address' => $matchingOnu->mac_address ?: ($matchingOnu->acsDevice?->mac_address ?? '-'),
                    'is_online' => $isOnline,
                    'rx_power' => $rxPower,
                    'tx_power' => $txPower,
                ] : null,
                'service_type' => $serviceType,
                'username' => $username,
                'ip_address' => $ipAddress,
                'is_online' => $isOnline,
                'dropcore_length' => $portConfig['dropcore_length'] ?? null,
                'notes' => $portConfig['notes'] ?? '',
                'updated_at' => $portConfig['updated_at'] ?? null,
            ];
        }

        return $ports;
    }

    /**
     * Compute summary statistics for NOC view.
     */
    public function getStatsProperty()
    {
        $ports = $this->ports;
        $total = count($ports);
        $used = 0;
        $available = 0;
        $reserved = 0;
        $fault = 0;
        $online = 0;

        foreach ($ports as $p) {
            if ($p['status'] === 'used') {
                $used++;
                if (!empty($p['is_online'])) {
                    $online++;
                }
            } elseif ($p['status'] === 'reserved') {
                $reserved++;
            } elseif ($p['status'] === 'fault') {
                $fault++;
            } else {
                $available++;
            }
        }

        $occupancyPct = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        return [
            'total_ports' => $total,
            'used_ports' => $used,
            'available_ports' => $available,
            'reserved_ports' => $reserved,
            'fault_ports' => $fault,
            'online_ports' => $online,
            'occupancy_pct' => $occupancyPct,
            'total_onus' => Onu::where('odp_id', $this->odpId)->count(),
            'total_customers' => $used,
        ];
    }

    /**
     * Get all connected customers for Customer Management Tab.
     */
    public function getConnectedCustomersProperty()
    {
        $ports = $this->ports;
        $list = [];

        foreach ($ports as $p) {
            if (!empty($p['customer'])) {
                $c = $p['customer'];
                $list[] = [
                    'port_number' => $p['port_number'],
                    'customer_id' => $c['id'],
                    'customer_name' => $c['name'],
                    'customer_code' => $c['code'],
                    'phone' => $c['phone'],
                    'service_type' => $p['service_type'],
                    'username' => $p['username'],
                    'ip_address' => $p['ip_address'],
                    'onu_model' => $p['onu']['model'] ?? '-',
                    'serial_number' => $p['onu']['serial_number'] ?? '-',
                    'is_online' => $p['is_online'],
                    'rx_power' => $p['onu']['rx_power'] ?? null,
                    'status' => $p['is_online'] ? 'online' : 'offline',
                ];
            }
        }

        if (!empty($this->customerSearch)) {
            $q = strtolower(trim($this->customerSearch));
            $list = array_filter($list, function ($item) use ($q) {
                return str_contains(strtolower($item['customer_name']), $q)
                    || str_contains(strtolower($item['customer_code']), $q)
                    || str_contains(strtolower($item['username']), $q)
                    || str_contains(strtolower($item['serial_number']), $q)
                    || str_contains(strtolower($item['ip_address']), $q);
            });
        }

        if (!empty($this->customerStatusFilter)) {
            $list = array_filter($list, function ($item) {
                return $item['status'] === $this->customerStatusFilter;
            });
        }

        return array_values($list);
    }

    /**
     * Get all ONUs for GenieACS tab.
     */
    public function getOnusProperty()
    {
        return Onu::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->with(['acsDevice', 'customerService.customer', 'customerService.pppoeUser', 'customerService.hotspotUser'])
            ->get();
    }

    /**
     * Support tickets related to this ODP.
     */
    public function getTicketsProperty()
    {
        $customerIds = [];
        foreach ($this->ports as $p) {
            if (!empty($p['customer']['id'])) {
                $customerIds[] = $p['customer']['id'];
            }
        }

        return Ticket::where(function ($q) use ($customerIds) {
            if (!empty($customerIds)) {
                $q->whereIn('customer_id', $customerIds);
            }
            $q->orWhere('title', 'like', "%{$this->odp->code}%")
              ->orWhere('title', 'like', "%{$this->odp->name}%")
              ->orWhere('description', 'like', "%{$this->odp->code}%")
              ->orWhere('description', 'like', "%{$this->odp->name}%");
        })->latest()->limit(15)->get();
    }

    /**
     * Port mutation / activity history.
     */
    public function getPortLogsProperty()
    {
        $attrs = $this->getOdpAttributes();
        return $attrs['port_logs'] ?? [];
    }

    /**
     * Open modal to inspect or edit a specific port.
     */
    public function openPortModal($portNumber)
    {
        $ports = $this->ports;
        if (!isset($ports[$portNumber])) {
            return;
        }

        $this->selectedPortNumber = (int) $portNumber;
        $this->selectedPort = $ports[$portNumber];
        $this->portFormStatus = $this->selectedPort['status'];
        $this->portFormNotes = $this->selectedPort['notes'] ?? '';
        $this->portFormDropcore = $this->selectedPort['dropcore_length'] ?? '';
        $this->targetMovePort = null;
        $this->showPortModal = true;
    }

    public function closePortModal()
    {
        $this->showPortModal = false;
        $this->selectedPortNumber = null;
        $this->selectedPort = null;
    }

    /**
     * Save port details (status, notes, dropcore length).
     */
    public function savePortDetails()
    {
        if (!$this->selectedPortNumber) return;

        $p = $this->selectedPortNumber;
        $attrs = $this->getOdpAttributes();
        $customPorts = $attrs['ports'] ?? [];

        $oldStatus = $this->selectedPort['status'] ?? 'available';
        $newStatus = in_array($this->portFormStatus, ['available', 'reserved', 'fault']) ? $this->portFormStatus : $oldStatus;

        // If currently used by customer, keep status as used unless disconnected
        if ($this->selectedPort['customer'] !== null || $this->selectedPort['onu'] !== null) {
            $newStatus = 'used';
        }

        $customPorts[$p] = [
            'status' => $newStatus,
            'notes' => trim((string) $this->portFormNotes),
            'dropcore_length' => $this->portFormDropcore !== '' ? (int) $this->portFormDropcore : null,
            'updated_at' => now()->toDateTimeString(),
            'updated_by' => Auth::user()?->name ?? 'System',
        ];

        // Audit & Log
        $logs = $attrs['port_logs'] ?? [];
        $logs[] = [
            'timestamp' => now()->toDateTimeString(),
            'user' => Auth::user()?->name ?? 'Admin',
            'port' => $p,
            'action' => 'update',
            'detail' => "Update status Port {$p} menjadi " . strtoupper($newStatus) . " (Catatan: " . ($this->portFormNotes ?: '-') . ")",
        ];
        $attrs['ports'] = $customPorts;
        $attrs['port_logs'] = array_slice($logs, -50); // Keep last 50 logs

        $this->odp->setAttribute('attributes', $attrs);
        $this->odp->used_port_count = $this->stats['used_ports'];
        $this->odp->reserved_port_count = $this->stats['reserved_ports'];
        $this->odp->save();

        AuditLog::create([
            'auditable_type' => OdpModel::class,
            'auditable_id' => $this->odpId,
            'event' => 'port_update',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus, 'port' => $p],
            'user_id' => Auth::id(),
            'notes' => "Perubahan status Port {$p} ODP {$this->odp->name}",
        ]);

        $this->closePortModal();
        session()->flash('success', "Konfigurasi Port {$p} berhasil disimpan.");
    }

    /**
     * Move active subscriber / ONU from current port to target port.
     */
    public function movePort()
    {
        if (!$this->selectedPortNumber || !$this->targetMovePort) {
            session()->flash('error', 'Silakan pilih port tujuan pemindahan.');
            return;
        }

        $fromPort = (int) $this->selectedPortNumber;
        $toPort = (int) $this->targetMovePort;

        if ($fromPort === $toPort) {
            session()->flash('error', 'Port tujuan tidak boleh sama dengan port asal.');
            return;
        }

        $ports = $this->ports;
        if (!isset($ports[$toPort])) {
            session()->flash('error', "Port tujuan {$toPort} tidak valid.");
            return;
        }

        // Anti-collision validation: Target port must not be in use
        if ($ports[$toPort]['status'] === 'used') {
            $otherCust = $ports[$toPort]['customer']['name'] ?? 'pelanggan lain';
            session()->flash('error', "Port tujuan {$toPort} sudah digunakan oleh {$otherCust}. Pemindahan dibatalkan demi keamanan topologi.");
            return;
        }

        $customerName = $this->selectedPort['customer']['name'] ?? 'Pelanggan';

        // 1. Move PPPoE subscriber
        PPPoEUser::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->where('port_number', $fromPort)
            ->update(['port_number' => $toPort]);

        // 2. Move Hotspot subscriber
        HotspotUser::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->where('port_number', $fromPort)
            ->update(['port_number' => $toPort]);

        // 3. Move ONU
        if (!empty($this->selectedPort['onu']['id'])) {
            $onu = Onu::withoutGlobalScopes()->find($this->selectedPort['onu']['id']);
            if ($onu) {
                $onu->pon_port = $toPort;
                $onuAttrs = is_array($onu->attributes) ? $onu->attributes : [];
                $onuAttrs['port_number'] = $toPort;
                $onuAttrs['odp_port'] = $toPort;
                $onu->attributes = $onuAttrs;
                $onu->save();
            }
        }

        // 4. Update ODP attributes
        $attrs = $this->getOdpAttributes();
        $customPorts = $attrs['ports'] ?? [];

        $customPorts[$toPort] = $customPorts[$fromPort] ?? [];
        $customPorts[$toPort]['status'] = 'used';
        $customPorts[$toPort]['updated_at'] = now()->toDateTimeString();

        $customPorts[$fromPort] = [
            'status' => 'available',
            'notes' => "Dipindahkan ke Port {$toPort} pada " . now()->format('d/m/Y H:i'),
            'updated_at' => now()->toDateTimeString(),
        ];

        // 5. Activity log
        $logs = $attrs['port_logs'] ?? [];
        $logs[] = [
            'timestamp' => now()->toDateTimeString(),
            'user' => Auth::user()?->name ?? 'Admin',
            'port' => $toPort,
            'action' => 'move',
            'detail' => "Pemindahan jalur {$customerName} dari Port {$fromPort} ke Port {$toPort}.",
        ];
        $attrs['ports'] = $customPorts;
        $attrs['port_logs'] = array_slice($logs, -50);

        $this->odp->setAttribute('attributes', $attrs);
        $this->odp->save();

        \App\Livewire\Gis\GisMap::flushCache();

        $this->closePortModal();
        session()->flash('success', "Sukses! {$customerName} berhasil dipindahkan dari Port {$fromPort} ke Port {$toPort}.");
    }

    /**
     * Disconnect subscriber relation from a port with authorization.
     */
    public function disconnectPort($portNumber)
    {
        $portNumber = (int) $portNumber;
        $ports = $this->ports;

        if (!isset($ports[$portNumber])) return;

        $p = $ports[$portNumber];
        $customerName = $p['customer']['name'] ?? ($p['onu']['serial_number'] ?? 'Perangkat');

        // Reset PPPoE
        PPPoEUser::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->where('port_number', $portNumber)
            ->update(['port_number' => null]);

        // Reset Hotspot
        HotspotUser::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->where('port_number', $portNumber)
            ->update(['port_number' => null]);

        // Reset ONU
        if (!empty($p['onu']['id'])) {
            $onu = Onu::withoutGlobalScopes()->find($p['onu']['id']);
            if ($onu) {
                $onu->pon_port = null;
                $onuAttrs = is_array($onu->attributes) ? $onu->attributes : [];
                unset($onuAttrs['port_number'], $onuAttrs['odp_port']);
                $onu->attributes = $onuAttrs;
                $onu->save();
            }
        }

        // Update attributes
        $attrs = $this->getOdpAttributes();
        $customPorts = $attrs['ports'] ?? [];
        $customPorts[$portNumber] = [
            'status' => 'available',
            'notes' => "Koneksi diputuskan pada " . now()->format('d/m/Y H:i'),
            'updated_at' => now()->toDateTimeString(),
        ];

        $logs = $attrs['port_logs'] ?? [];
        $logs[] = [
            'timestamp' => now()->toDateTimeString(),
            'user' => Auth::user()?->name ?? 'Admin',
            'port' => $portNumber,
            'action' => 'disconnect',
            'detail' => "Pemutusan relasi {$customerName} pada Port {$portNumber}.",
        ];
        $attrs['ports'] = $customPorts;
        $attrs['port_logs'] = array_slice($logs, -50);

        $this->odp->setAttribute('attributes', $attrs);
        $this->odp->save();

        \App\Livewire\Gis\GisMap::flushCache();

        $this->closePortModal();
        session()->flash('success', "Koneksi pelanggan pada Port {$portNumber} berhasil diputuskan dan port telah siap digunakan kembali.");
    }

    /**
     * Open GenieACS Live Telemetry Modal for an ONT.
     */
    public function openGenieAcsModal($onuId)
    {
        $this->selectedOnuId = $onuId;
        $this->selectedOnu = Onu::withoutGlobalScopes()->with(['acsDevice', 'customerService.customer'])->find($onuId);
        $this->selectedOnuLive = null;
        $this->genieAcsMessage = null;
        $this->showGenieAcsModal = true;

        $this->fetchGenieAcsLive();
    }

    public function closeGenieAcsModal()
    {
        $this->showGenieAcsModal = false;
        $this->selectedOnuId = null;
        $this->selectedOnu = null;
        $this->selectedOnuLive = null;
    }

    public function fetchGenieAcsLive($forceFresh = false)
    {
        if (!$this->selectedOnu) return;

        $this->isLoadingGenieAcs = true;
        try {
            $service = app(GenieAcsDiagnosticService::class);
            $this->selectedOnuLive = $service->getLiveDiagnostics($this->selectedOnu, $forceFresh);
        } catch (\Throwable $e) {
            $this->genieAcsMessage = 'Gagal menghubungi GenieACS REST API: ' . $e->getMessage();
            Log::warning("[OdpShow::fetchGenieAcsLive] Error: " . $e->getMessage());
        } finally {
            $this->isLoadingGenieAcs = false;
        }
    }

    public function refreshGenieAcsLive($onuId)
    {
        $this->selectedOnuId = $onuId;
        $this->selectedOnu = Onu::withoutGlobalScopes()->with(['acsDevice', 'customerService.customer'])->find($onuId);
        $this->showGenieAcsModal = true;
        $this->fetchGenieAcsLive(true);
    }

    public function deletePhoto()
    {
        NodePhotoService::deletePhotos($this->odp);
        $this->odp->refresh();
        \App\Livewire\Gis\GisMap::flushCache();
        session()->flash('success', 'Foto dokumentasi tiang ODP berhasil dihapus!');
    }

    // -------------------------------------------------------------
    // Splitter Management (Tier-2 in ODP)
    // -------------------------------------------------------------
    public function openAddSplitterModal(): void
    {
        $count = Splitter::withoutGlobalScopes()->where('odp_id', $this->odpId)->count() + 1;
        $this->splitterCode = 'SPL-' . ($this->odp->code ?: 'ODP') . '-S' . $count;
        $this->splitterName = 'Splitter Box #' . $count;
        $this->splitterType = 'plc';
        $this->splitterRatio = '1:8';
        $this->splitterPackage = 'abs_box';
        $this->splitterInputPorts = 1;
        $this->splitterPortCount = 8;
        $this->splitterLossDb = 10.50;
        $this->splitterDescription = '';
        $this->showSplitterModal = true;
    }

    public function closeAddSplitterModal(): void
    {
        $this->showSplitterModal = false;
    }

    public function updatedSplitterRatio($ratio): void
    {
        $this->splitterRatio = $ratio;
        $losses = Splitter::STANDARD_LOSSES;
        $this->splitterLossDb = $losses[$ratio] ?? 10.50;

        if (preg_match('/^[12]:(\d+)$/', $ratio, $m)) {
            $this->splitterPortCount = (int) $m[1];
        } elseif (in_array($ratio, ['50:50', '60:40', '70:30', '80:20', '85:15', '90:10'])) {
            $this->splitterPortCount = 2;
            $this->splitterType = 'fbt';
        }
    }

    public function updatedSplitterType($type): void
    {
        $this->splitterType = $type;
        if ($type === 'fbt' && (!str_contains($this->splitterRatio, ':') || str_starts_with($this->splitterRatio, '1:'))) {
            $this->splitterRatio = '70:30';
            $this->splitterPortCount = 2;
            $this->splitterLossDb = 5.60;
        } elseif ($type === 'plc' && !str_starts_with($this->splitterRatio, '1:')) {
            $this->splitterRatio = '1:8';
            $this->splitterPortCount = 8;
            $this->splitterLossDb = 10.50;
        }
    }

    public function createSplitter(): void
    {
        $this->validate([
            'splitterCode' => 'required|string|max:50',
            'splitterName' => 'required|string|max:100',
            'splitterType' => 'required|in:plc,fbt',
            'splitterRatio' => 'required|string|max:20',
            'splitterPortCount' => 'required|integer|min:2|max:64',
            'splitterInputPorts' => 'required|integer|min:1|max:4',
        ]);

        $splitter = Splitter::create([
            'odp_id' => $this->odpId,
            'code' => $this->splitterCode,
            'name' => $this->splitterName,
            'type' => $this->splitterType,
            'ratio_label' => $this->splitterRatio,
            'package_type' => $this->splitterPackage,
            'input_port_count' => $this->splitterInputPorts,
            'port_count' => $this->splitterPortCount,
            'insertion_loss_db' => $this->splitterLossDb,
            'description' => $this->splitterDescription,
            'status' => 'active',
            'branch_id' => $this->odp->branch_id,
            'created_by' => auth()->id(),
        ]);

        $splitter->syncPorts();
        $this->loadOdp();
        $this->closeAddSplitterModal();

        session()->flash('success', "Splitter {$splitter->name} ({$splitter->ratio_label}) berhasil dipasang pada ODP.");
    }

    public function deleteSplitter(int $splitterId): void
    {
        $splitter = Splitter::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->find($splitterId);

        if ($splitter) {
            $name = $splitter->name;
            $splitter->ports()->delete();
            $splitter->delete();
            $this->loadOdp();
            session()->flash('success', "Splitter {$name} berhasil dilepas dari ODP.");
        }
    }

    // -------------------------------------------------------------
    // Splitter Port Modal (Output ke ONU / AP / Pelanggan)
    // -------------------------------------------------------------
    public function openSplitterPortModal(int $portId): void
    {
        $port = SplitterPort::withoutGlobalScopes()->with(['splitter', 'target', 'customer'])->find($portId);
        if (!$port) {
            return;
        }

        $this->selectedSplitterPortId = $port->id;
        $this->selectedSplitterPort = $port;
        $this->spPortFormTargetType = ($port->target_type && str_contains($port->target_type, 'AccessPoint')) ? 'ap' : 'onu';
        $this->spPortFormTargetId = $port->target_id;
        $this->spPortFormCustomerId = $port->customer_id;
        $this->spPortFormStatus = $port->status ?: 'available';
        $this->spPortFormAttenuation = $port->attenuation_db;
        $this->spPortFormCoreColor = $port->core_color ?: '';
        $this->spPortFormNotes = $port->notes ?: '';

        $this->showSplitterPortModal = true;
    }

    public function closeSplitterPortModal(): void
    {
        $this->showSplitterPortModal = false;
        $this->selectedSplitterPortId = null;
        $this->selectedSplitterPort = null;
    }

    public function saveSplitterPort(): void
    {
        if (!$this->selectedSplitterPortId) {
            return;
        }

        $port = SplitterPort::withoutGlobalScopes()->find($this->selectedSplitterPortId);
        if (!$port) {
            return;
        }

        $port->status = $this->spPortFormStatus;
        $port->attenuation_db = $this->spPortFormAttenuation;
        $port->core_color = $this->spPortFormCoreColor;
        $port->notes = $this->spPortFormNotes;

        if ($this->spPortFormStatus === 'used' && $this->spPortFormTargetId) {
            if ($this->spPortFormTargetType === 'onu') {
                $onu = Onu::withoutGlobalScopes()->find($this->spPortFormTargetId);
                if ($onu) {
                    $cust = $onu->customerService?->customer;
                    $port->assignTarget($onu, null, "{$onu->name} [{$onu->serial_number}]", $cust?->id ?: $this->spPortFormCustomerId);
                }
            } elseif ($this->spPortFormTargetType === 'ap') {
                $ap = AccessPoint::withoutGlobalScopes()->find($this->spPortFormTargetId);
                if ($ap) {
                    $port->assignTarget($ap, null, "{$ap->name} [{$ap->ssid}]");
                }
            }
        } elseif ($this->spPortFormStatus === 'available') {
            $port->disconnect();
        } else {
            $port->save();
        }

        $this->loadOdp();
        $this->closeSplitterPortModal();
        session()->flash('success', "Konfigurasi Port #{$port->port_number} berhasil diperbarui.");
    }

    public function disconnectSplitterPort(int $portId): void
    {
        $port = SplitterPort::withoutGlobalScopes()->find($portId);
        if ($port) {
            $port->disconnect();
            $this->loadOdp();
            session()->flash('success', "Koneksi Port #{$port->port_number} berhasil diputus.");
        }
    }

    public function getOdpSplittersProperty()
    {
        return Splitter::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->with(['ports.target', 'ports.customer'])
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getAvailableOnusProperty()
    {
        return Onu::withoutGlobalScopes()
            ->where('odp_id', $this->odpId)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'serial_number']);
    }

    public function getAvailableAccessPointsProperty()
    {
        return AccessPoint::withoutGlobalScopes()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'ssid']);
    }

    public function render()
    {
        return view('livewire.isp.odp.show', [
            'ports' => $this->ports,
            'stats' => $this->stats,
            'connectedCustomers' => $this->connectedCustomers,
            'onus' => $this->onus,
            'tickets' => $this->tickets,
            'portLogs' => $this->portLogs,
            'splitters' => $this->odpSplitters,
        ]);
    }
}
