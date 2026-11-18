<?php

namespace App\Services\ISP;

use App\Models\AuditLog;
use App\Models\ISP\NetworkIncident;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Onu;
use App\Models\ISP\Splitter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OdcTopologyService
{
    /**
     * Get summary KPI statistics for an ODC
     */
    public function getSummary(Odc $odc): array
    {
        $totalPorts = max(1, (int) ($odc->port_count ?: 48));
        
        // Load connected ODPs with their child relationships
        $odps = Odp::withoutGlobalScopes()
            ->where('odc_id', $odc->id)
            ->with(['onus.customerService.customer', 'splitters'])
            ->get();

        $odpsCount = $odps->count();

        // Calculate port usage
        $usedPorts = 0;
        $faultPorts = 0;
        $reservedPorts = 0;
        $odpPortMap = [];

        foreach ($odps as $odp) {
            $portNum = $odp->odc_port ?: ($odpPortMap ? max(array_keys($odpPortMap)) + 1 : 1);
            $odpPortMap[$portNum] = $odp;

            if ($odp->status === 'inactive' || $odp->status === 'damaged' || $odp->status === 'fault') {
                $faultPorts++;
            } else {
                $usedPorts++;
            }

            $reservedPorts += (int) ($odp->reserved_port_count ?? 0);
        }

        // Active port count fallback
        if ($usedPorts === 0 && (int) $odc->active_port_count > 0) {
            $usedPorts = min($totalPorts, (int) $odc->active_port_count);
        }

        $availablePorts = max(0, $totalPorts - $usedPorts - $faultPorts);

        // Gather total ONUs & Customers under this ODC
        $onuIds = [];
        $customerIds = [];
        $onlineOnusCount = 0;
        $criticalOnusCount = 0;

        foreach ($odps as $odp) {
            foreach ($odp->onus as $onu) {
                $onuIds[] = $onu->id;
                if ($onu->operational_status === 'online' || ($onu->last_seen_at && $onu->last_seen_at->diffInMinutes(now()) <= 15)) {
                    $onlineOnusCount++;
                }
                if ($onu->rx_power_dbm !== null && (float) $onu->rx_power_dbm < -27.0) {
                    $criticalOnusCount++;
                }

                if ($onu->customerService && $onu->customerService->customer) {
                    $customerIds[$onu->customerService->customer->id] = true;
                }
            }
        }

        $onusCount = count($onuIds);
        $customersCount = count($customerIds);
        $occupancyPercent = round(min(100, ($usedPorts / $totalPorts) * 100), 1);

        return [
            'total_ports' => $totalPorts,
            'used_ports' => $usedPorts,
            'available_ports' => $availablePorts,
            'fault_ports' => $faultPorts,
            'reserved_ports' => $reservedPorts,
            'odps_count' => $odpsCount,
            'onus_count' => $onusCount,
            'online_onus_count' => $onlineOnusCount,
            'offline_onus_count' => max(0, $onusCount - $onlineOnusCount),
            'critical_onus_count' => $criticalOnusCount,
            'customers_count' => $customersCount,
            'occupancy_percent' => $occupancyPercent,
        ];
    }

    /**
     * Build interactive topology nodes and edges:
     * OLT -> ODF/Feeder -> ODC -> Splitter ODC -> ODP -> Splitter ODP -> ONU -> Pelanggan
     */
    public function getTopologyData(Odc $odc): array
    {
        $nodes = [];
        $edges = [];

        $olt = $odc->olt;
        $pop = $odc->pop;

        // 1. OLT Node
        if ($olt) {
            $nodes[] = [
                'id' => 'olt-' . $olt->id,
                'label' => $olt->name . "\n[" . $olt->code . "]\nIP: " . ($olt->ip_address ?: '—'),
                'group' => 'olt',
                'level' => 0,
                'shape' => 'box',
                'color' => [
                    'background' => '#0f172a',
                    'border' => '#0284c7',
                    'highlight' => ['background' => '#1e293b', 'border' => '#38bdf8'],
                ],
                'font' => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 13],
                'metadata' => [
                    'type' => 'olt',
                    'id' => $olt->id,
                    'code' => $olt->code,
                    'name' => $olt->name,
                    'model' => $olt->model ?? 'OLT Chassis',
                    'ip_address' => $olt->ip_address,
                    'pon_ports' => $olt->pon_port_count ?? 16,
                    'status' => $olt->status ?? 'active',
                    'detail_url' => route('isp.olts.show', $olt->id),
                ],
            ];
        }

        // 2. ODF / Feeder Cable Node
        $feederId = 'feeder-' . $odc->id;
        $feederLabel = "ODF / Feeder Cable\nCore PON-1 ➔ Inbound ODC";
        $nodes[] = [
            'id' => $feederId,
            'label' => $feederLabel,
            'group' => 'feeder',
            'level' => 1,
            'shape' => 'box',
            'color' => [
                'background' => '#0f172a',
                'border' => '#06b6d4',
                'highlight' => ['background' => '#1e293b', 'border' => '#22d3ee'],
            ],
            'font' => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 12],
            'metadata' => [
                'type' => 'feeder',
                'id' => $odc->id,
                'code' => 'FDR-' . $odc->code,
                'name' => 'ODF Feeder Trunk Line',
                'status' => 'active',
                'detail_url' => null,
            ],
        ];

        if ($olt) {
            $edges[] = [
                'from' => 'olt-' . $olt->id,
                'to' => $feederId,
                'label' => 'PON 1 (GPON)',
                'color' => ['color' => '#0284c7', 'highlight' => '#00e5ff', 'hover' => '#38bdf8'],
                'width' => 3,
                'arrows' => ['to' => ['enabled' => true, 'scaleFactor' => 0.8]],
            ];
        }

        // 3. ODC Cabinet Node (Target)
        $odcNodeId = 'odc-' . $odc->id;
        $odcStatusColor = match ($odc->status) {
            'active' => '#10b981',
            'warning' => '#f59e0b',
            default => '#ef4444',
        };

        $nodes[] = [
            'id' => $odcNodeId,
            'label' => $odc->name . "\n[" . $odc->code . "]\nKapasitas: " . ($odc->port_count ?: 48) . " Port",
            'group' => 'odc',
            'level' => 2,
            'shape' => 'box',
            'color' => [
                'background' => '#1e1b4b',
                'border' => '#818cf8',
                'highlight' => ['background' => '#312e81', 'border' => '#a5b4fc'],
            ],
            'font' => ['color' => '#ffffff', 'face' => 'JetBrains Mono, monospace', 'size' => 14],
            'metadata' => [
                'type' => 'odc',
                'id' => $odc->id,
                'code' => $odc->code,
                'name' => $odc->name,
                'total_ports' => $odc->port_count ?: 48,
                'active_ports' => $odc->active_port_count ?: 0,
                'status' => $odc->status,
                'address' => $odc->address,
                'coordinates' => ($odc->latitude && $odc->longitude) ? "{$odc->latitude}, {$odc->longitude}" : null,
                'detail_url' => route('isp.odcs.show', $odc->id),
            ],
        ];

        $edges[] = [
            'from' => $feederId,
            'to' => $odcNodeId,
            'label' => 'Inbound Feeder',
            'color' => ['color' => '#06b6d4', 'highlight' => '#00e5ff', 'hover' => '#22d3ee'],
            'width' => 3,
            'arrows' => ['to' => ['enabled' => true, 'scaleFactor' => 0.8]],
        ];

        // 4. ODC Tray Splitters
        $odcSplitters = Splitter::where('odc_id', $odc->id)->get();
        $hasOdcSplitters = $odcSplitters->isNotEmpty();

        foreach ($odcSplitters as $spl) {
            $splNodeId = 'spl-odc-' . $spl->id;
            $nodes[] = [
                'id' => $splNodeId,
                'label' => $spl->name . "\nRatio 1:" . ($spl->ratio ?: 4) . "\n[" . $spl->code . "]",
                'group' => 'splitter_odc',
                'level' => 3,
                'shape' => 'diamond',
                'color' => [
                    'background' => '#0f172a',
                    'border' => '#a855f7',
                    'highlight' => ['background' => '#1e293b', 'border' => '#c084fc'],
                ],
                'font' => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 12],
                'metadata' => [
                    'type' => 'splitter',
                    'tier' => '1st Tier (ODC)',
                    'id' => $spl->id,
                    'code' => $spl->code,
                    'name' => $spl->name,
                    'ratio' => "1:" . ($spl->ratio ?: 4),
                    'ports' => $spl->port_count,
                    'status' => $spl->status,
                    'detail_url' => null,
                ],
            ];

            $edges[] = [
                'from' => $odcNodeId,
                'to' => $splNodeId,
                'label' => 'Tray In',
                'color' => ['color' => '#a855f7', 'highlight' => '#00e5ff'],
                'width' => 2.5,
                'arrows' => ['to' => ['enabled' => true, 'scaleFactor' => 0.8]],
            ];
        }

        // 5. ODP Nodes & downstream elements
        $odps = Odp::withoutGlobalScopes()
            ->where('odc_id', $odc->id)
            ->with(['splitters', 'onus.customerService.customer', 'onus.customerService.pppoeUser', 'onus.customerService.hotspotUser', 'onus.acsDevice'])
            ->get();

        $odpLevel = $hasOdcSplitters ? 4 : 3;

        foreach ($odps as $odpIdx => $odp) {
            $odpNodeId = 'odp-' . $odp->id;
            $odpPort = $odp->odc_port ?: ($odpIdx + 1);

            $odpColor = match ($odp->status) {
                'active' => '#10b981',
                'warning' => '#f59e0b',
                default => '#ef4444',
            };

            $nodes[] = [
                'id' => $odpNodeId,
                'label' => $odp->name . "\n[" . $odp->code . "]\nPort ODC: #" . $odpPort . "\n" . ($odp->used_port_count ?? 0) . "/" . ($odp->port_count ?: 8) . " Port",
                'group' => 'odp',
                'level' => $odpLevel,
                'shape' => 'box',
                'color' => [
                    'background' => '#0f172a',
                    'border' => $odpColor,
                    'highlight' => ['background' => '#1e293b', 'border' => '#34d399'],
                ],
                'font' => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 13],
                'metadata' => [
                    'type' => 'odp',
                    'id' => $odp->id,
                    'code' => $odp->code,
                    'name' => $odp->name,
                    'odc_port' => $odpPort,
                    'split_ratio' => "1:" . ($odp->split_ratio ?: 8),
                    'total_ports' => $odp->port_count ?: 8,
                    'used_ports' => $odp->used_port_count ?? 0,
                    'available_ports' => max(0, ($odp->port_count ?: 8) - ($odp->used_port_count ?? 0) - ($odp->reserved_port_count ?? 0)),
                    'status' => $odp->status,
                    'address' => $odp->address,
                    'coordinates' => ($odp->latitude && $odp->longitude) ? "{$odp->latitude}, {$odp->longitude}" : null,
                    'detail_url' => route('isp.odps.show', $odp->id),
                ],
            ];

            // Connect ODC (or ODC Splitter) to ODP
            $edgeSourceId = $odcNodeId;
            if ($hasOdcSplitters && $odcSplitters->isNotEmpty()) {
                $edgeSourceId = 'spl-odc-' . $odcSplitters->first()->id;
            }

            $edges[] = [
                'from' => $edgeSourceId,
                'to' => $odpNodeId,
                'label' => 'Port ' . $odpPort . ' ➔ IN',
                'color' => ['color' => $odpColor, 'highlight' => '#00e5ff', 'hover' => '#38bdf8'],
                'width' => 2.5,
                'arrows' => ['to' => ['enabled' => true, 'scaleFactor' => 0.8]],
            ];

            // 6. Splitter ODP (2nd Tier)
            $odpSplitters = $odp->splitters;
            $parentForOnu = $odpNodeId;

            if ($odpSplitters->isNotEmpty()) {
                foreach ($odpSplitters as $spOdp) {
                    $spOdpNodeId = 'spl-odp-' . $spOdp->id;
                    $nodes[] = [
                        'id' => $spOdpNodeId,
                        'label' => $spOdp->name . "\nRatio 1:" . ($spOdp->ratio ?: 8),
                        'group' => 'splitter_odp',
                        'level' => $odpLevel + 1,
                        'shape' => 'diamond',
                        'color' => [
                            'background' => '#0f172a',
                            'border' => '#f59e0b',
                            'highlight' => ['background' => '#1e293b', 'border' => '#fbbf24'],
                        ],
                        'font' => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 11],
                        'metadata' => [
                            'type' => 'splitter',
                            'tier' => '2nd Tier (ODP)',
                            'id' => $spOdp->id,
                            'code' => $spOdp->code,
                            'name' => $spOdp->name,
                            'ratio' => "1:" . ($spOdp->ratio ?: 8),
                            'status' => $spOdp->status,
                            'detail_url' => null,
                        ],
                    ];

                    $edges[] = [
                        'from' => $odpNodeId,
                        'to' => $spOdpNodeId,
                        'label' => 'Input',
                        'color' => ['color' => '#f59e0b', 'highlight' => '#00e5ff'],
                        'width' => 2,
                        'arrows' => ['to' => ['enabled' => true, 'scaleFactor' => 0.8]],
                    ];

                    $parentForOnu = $spOdpNodeId;
                }
            }

            // 7. ONUs under ODP
            $onuLevel = $odpSplitters->isNotEmpty() ? $odpLevel + 2 : $odpLevel + 1;
            $custLevel = $onuLevel + 1;

            foreach ($odp->onus as $onuIdx => $onu) {
                $onuNodeId = 'onu-' . $onu->id;
                $isOnline = $onu->operational_status === 'online' || ($onu->last_seen_at && $onu->last_seen_at->diffInMinutes(now()) <= 15);
                
                $onuColor = $isOnline ? '#10b981' : '#ef4444';
                if ($onu->rx_power_dbm !== null && (float) $onu->rx_power_dbm < -27.0) {
                    $onuColor = '#f59e0b'; // degraded/warning
                }

                $onuDropPort = $onu->attributes['port_number'] ?? ($onuIdx + 1);

                $nodes[] = [
                    'id' => $onuNodeId,
                    'label' => ($onu->name ?: 'Modem ONU') . "\nSN: " . $onu->serial_number . "\nRx: " . ($onu->rx_power_dbm ? $onu->rx_power_dbm . ' dBm' : '—') . "\n[" . strtoupper($onu->operational_status ?? 'UNKNOWN') . "]",
                    'group' => 'onu',
                    'level' => $onuLevel,
                    'shape' => 'box',
                    'color' => [
                        'background' => '#0f172a',
                        'border' => $onuColor,
                        'highlight' => ['background' => '#1e293b', 'border' => '#38bdf8'],
                    ],
                    'font' => ['color' => '#f8fafc', 'face' => 'JetBrains Mono, monospace', 'size' => 12],
                    'metadata' => [
                        'type' => 'onu',
                        'id' => $onu->id,
                        'code' => $onu->code,
                        'name' => $onu->name,
                        'serial_number' => $onu->serial_number,
                        'mac_address' => $onu->mac_address,
                        'model' => $onu->model ?? 'ONT/ONU',
                        'drop_port' => $onuDropPort,
                        'rx_power' => $onu->rx_power_dbm ? $onu->rx_power_dbm . ' dBm' : '—',
                        'status' => $onu->status,
                        'operational_status' => $onu->operational_status,
                        'last_seen' => $onu->last_seen_at ? $onu->last_seen_at->format('d M Y H:i') : 'Never',
                        'acs_id' => $onu->acsDevice?->id,
                        'detail_url' => route('isp.onus.show', $onu->id),
                        'acs_url' => $onu->acsDevice ? route('acs.devices.show', $onu->acsDevice->id) : null,
                    ],
                ];

                $edges[] = [
                    'from' => $parentForOnu,
                    'to' => $onuNodeId,
                    'label' => 'Drop Port ' . $onuDropPort,
                    'color' => ['color' => $onuColor, 'highlight' => '#00e5ff'],
                    'width' => 2,
                    'arrows' => ['to' => ['enabled' => true, 'scaleFactor' => 0.8]],
                ];

                // 8. Pelanggan Node
                $cs = $onu->customerService;
                if ($cs && $cs->customer) {
                    $cust = $cs->customer;
                    $custNodeId = 'cust-' . $cust->id;

                    $serviceType = 'PPPoE';
                    if ($cs->hotspotUser) {
                        $serviceType = 'Hotspot';
                    } elseif ($cs->pppoeUser) {
                        $serviceType = 'PPPoE';
                    }

                    $nodes[] = [
                        'id' => $custNodeId,
                        'label' => $cust->name . "\n[" . $cust->code . "]\n" . $serviceType . " · " . ucfirst($cust->status),
                        'group' => 'customer',
                        'level' => $custLevel,
                        'shape' => 'box',
                        'color' => [
                            'background' => '#1e1b4b',
                            'border' => '#c084fc',
                            'highlight' => ['background' => '#312e81', 'border' => '#e9d5ff'],
                        ],
                        'font' => ['color' => '#ffffff', 'face' => 'JetBrains Mono, monospace', 'size' => 12],
                        'metadata' => [
                            'type' => 'customer',
                            'id' => $cust->id,
                            'code' => $cust->code,
                            'name' => $cust->name,
                            'phone' => $cust->phone,
                            'address' => $cust->address,
                            'service_type' => $serviceType,
                            'username' => $cs->username,
                            'status' => $cust->status,
                            'detail_url' => route('crm.customers.show', $cust->id),
                        ],
                    ];

                    $edges[] = [
                        'from' => $onuNodeId,
                        'to' => $custNodeId,
                        'label' => 'LAN / Wi-Fi',
                        'color' => ['color' => '#c084fc', 'highlight' => '#00e5ff'],
                        'width' => 2,
                        'arrows' => ['to' => ['enabled' => true, 'scaleFactor' => 0.8]],
                    ];
                }
            }
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'counts' => [
                'total_nodes' => count($nodes),
                'total_edges' => count($edges),
                'odps' => $odps->count(),
                'onus' => count(array_filter($nodes, fn($n) => $n['group'] === 'onu')),
                'customers' => count(array_filter($nodes, fn($n) => $n['group'] === 'customer')),
            ],
        ];
    }

    /**
     * Get Port Management matrix for all 1..port_count ports
     */
    public function getPortManagement(Odc $odc, ?string $statusFilter = null, ?string $search = null): array
    {
        $totalPorts = max(1, (int) ($odc->port_count ?: 48));
        
        $odps = Odp::withoutGlobalScopes()
            ->where('odc_id', $odc->id)
            ->with(['splitters', 'onus.customerService.customer'])
            ->get();

        $odpPortMap = [];
        foreach ($odps as $idx => $odp) {
            $p = $odp->odc_port ?: ($idx + 1);
            $odpPortMap[$p] = $odp;
        }

        $ports = [];
        for ($i = 1; $i <= $totalPorts; $i++) {
            $odp = $odpPortMap[$i] ?? null;

            if ($odp) {
                $status = match ($odp->status) {
                    'inactive', 'damaged', 'fault' => 'fault',
                    default => 'used',
                };
                $splitRatio = $odp->split_ratio ? "1:{$odp->split_ratio}" : "1:8";
                $downstreamCustomers = 0;
                foreach ($odp->onus as $onu) {
                    if ($onu->customerService && $onu->customerService->customer) {
                        $downstreamCustomers++;
                    }
                }
            } else {
                $status = 'available';
                $splitRatio = '—';
                $downstreamCustomers = 0;
            }

            $portItem = [
                'port_number' => $i,
                'status' => $status,
                'splitter_type' => $splitRatio,
                'odp_id' => $odp?->id,
                'odp_code' => $odp?->code,
                'odp_name' => $odp?->name,
                'odp_status' => $odp?->status,
                'downstream_customers' => $downstreamCustomers,
                'onus_count' => $odp ? $odp->onus->count() : 0,
                'description' => $odp ? "Terkoneksi ke {$odp->name} ({$odp->code})" : "Port Kosong (Siap dialokasikan)",
            ];

            // Apply filters
            if ($statusFilter && $statusFilter !== 'all' && $status !== $statusFilter) {
                continue;
            }

            if ($search) {
                $q = strtolower(trim($search));
                $matchNumber = (string) $i === $q;
                $matchCode = $odp && str_contains(strtolower($odp->code), $q);
                $matchName = $odp && str_contains(strtolower($odp->name), $q);

                if (!$matchNumber && !$matchCode && !$matchName) {
                    continue;
                }
            }

            $ports[] = $portItem;
        }

        return $ports;
    }

    /**
     * Get downstream customer roster under this ODC
     */
    public function getDownstreamCustomers(Odc $odc, ?string $search = null, ?string $typeFilter = null): Collection
    {
        $odpIds = Odp::withoutGlobalScopes()
            ->where('odc_id', $odc->id)
            ->pluck('id');

        if ($odpIds->isEmpty()) {
            return collect();
        }

        $onus = Onu::withoutGlobalScopes()
            ->whereIn('odp_id', $odpIds)
            ->with([
                'odp',
                'acsDevice',
                'customerService.customer',
                'customerService.pppoeUser',
                'customerService.hotspotUser',
            ])
            ->get();

        $rows = collect();

        foreach ($onus as $onu) {
            $cs = $onu->customerService;
            $cust = $cs?->customer;
            if (!$cust) {
                continue;
            }

            $serviceType = 'PPPoE';
            if ($cs->hotspotUser) {
                $serviceType = 'Hotspot';
            } elseif ($cs->pppoeUser) {
                $serviceType = 'PPPoE';
            }

            $isOnline = $onu->operational_status === 'online' || ($onu->last_seen_at && $onu->last_seen_at->diffInMinutes(now()) <= 15);

            $item = [
                'customer_id' => $cust->id,
                'customer_code' => $cust->code,
                'customer_name' => $cust->name,
                'phone' => $cust->phone,
                'address' => $cust->address,
                'service_type' => $serviceType,
                'username' => $cs->username,
                'odp_id' => $onu->odp?->id,
                'odp_code' => $onu->odp?->code,
                'odp_name' => $onu->odp?->name,
                'odp_port' => $onu->attributes['port_number'] ?? $onu->odp_port ?? 1,
                'onu_id' => $onu->id,
                'onu_code' => $onu->code,
                'onu_sn' => $onu->serial_number,
                'onu_model' => $onu->model,
                'rx_power' => $onu->rx_power_dbm,
                'is_online' => $isOnline,
                'operational_status' => $onu->operational_status ?? ($isOnline ? 'online' : 'offline'),
                'customer_status' => $cust->status,
                'acs_device_id' => $onu->acsDevice?->id,
                'detail_url' => route('crm.customers.show', $cust->id),
                'onu_url' => route('isp.onus.show', $onu->id),
                'acs_url' => $onu->acsDevice ? route('acs.devices.show', $onu->acsDevice->id) : null,
            ];

            // Filter search
            if ($search) {
                $q = strtolower(trim($search));
                $matchCode = str_contains(strtolower($cust->code ?? ''), $q);
                $matchName = str_contains(strtolower($cust->name ?? ''), $q);
                $matchSn = str_contains(strtolower($onu->serial_number ?? ''), $q);
                $matchOdp = str_contains(strtolower($onu->odp?->code ?? ''), $q);
                $matchUser = str_contains(strtolower($cs->username ?? ''), $q);

                if (!$matchCode && !$matchName && !$matchSn && !$matchOdp && !$matchUser) {
                    continue;
                }
            }

            // Filter type
            if ($typeFilter && $typeFilter !== 'all') {
                if (strtolower($serviceType) !== strtolower($typeFilter)) {
                    continue;
                }
            }

            $rows->push($item);
        }

        return $rows;
    }

    /**
     * Get timeline history of ODC connection changes, port events & network incidents
     */
    public function getHistory(Odc $odc): array
    {
        $events = [];

        // 1. Audit logs directly on this ODC
        $auditLogs = AuditLog::where('auditable_type', Odc::class)
            ->where('auditable_id', $odc->id)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();

        foreach ($auditLogs as $log) {
            $events[] = [
                'type' => 'audit',
                'badge' => strtoupper($log->event),
                'title' => "Perubahan ODC ({$log->event})",
                'description' => "Diperbarui oleh " . ($log->user?->name ?: 'Sistem'),
                'date' => $log->created_at,
                'user' => $log->user?->name,
                'details' => $log->new_values,
                'icon' => 'history',
                'color' => 'blue',
            ];
        }

        // 2. Audit logs on connected ODPs
        $odpIds = Odp::withoutGlobalScopes()->where('odc_id', $odc->id)->pluck('id');
        if ($odpIds->isNotEmpty()) {
            $odpLogs = AuditLog::where('auditable_type', Odp::class)
                ->whereIn('auditable_id', $odpIds)
                ->with('user')
                ->latest()
                ->limit(20)
                ->get();

            foreach ($odpLogs as $log) {
                $events[] = [
                    'type' => 'odp_event',
                    'badge' => 'ODP ' . strtoupper($log->event),
                    'title' => "Perubahan Koneksi ODP #{$log->auditable_id}",
                    'description' => "Aktivitas teknisi " . ($log->user?->name ?: 'Sistem') . " pada ODP terhubung.",
                    'date' => $log->created_at,
                    'user' => $log->user?->name,
                    'details' => $log->new_values,
                    'icon' => 'settings_ethernet',
                    'color' => 'cyan',
                ];
            }
        }

        // 3. Network incidents
        $incidents = NetworkIncident::where(function ($q) use ($odc, $odpIds) {
            $q->where(fn($sq) => $sq->where('type', 'odc')->where('reference_id', $odc->id))
              ->orWhere(fn($sq) => $sq->where('type', 'odp')->whereIn('reference_id', $odpIds));
        })
        ->latest('started_at')
        ->limit(10)
        ->get();

        foreach ($incidents as $inc) {
            $events[] = [
                'type' => 'incident',
                'badge' => strtoupper($inc->status),
                'title' => "Insiden Jaringan: {$inc->description}",
                'description' => "Dampak: {$inc->impacted_customers_count} pelanggan terdampak.",
                'date' => $inc->started_at,
                'resolved_at' => $inc->resolved_at,
                'user' => 'NOC Alarm',
                'details' => null,
                'icon' => 'warning',
                'color' => $inc->status === 'resolved' ? 'emerald' : 'rose',
            ];
        }

        // Sort all events descending by date
        usort($events, fn($a, $b) => $b['date'] <=> $a['date']);

        return array_slice($events, 0, 30);
    }

    /**
     * Calculate optical link budget & attenuation breakdown for FTTH tracing
     */
    public function calculateLinkBudget(Onu $onu): array
    {
        $txOlt = 3.0; // dBm (Class B+ standard)
        $feederLoss = 0.5; // dB
        $odcSplitterLoss = 7.2; // 1:4 splitter loss ~ 7.2 dB
        $distributionLoss = 1.2; // dB
        $odpSplitterLoss = 10.5; // 1:8 splitter loss ~ 10.5 dB
        $dropLoss = 0.4; // dB
        
        $totalLoss = $feederLoss + $odcSplitterLoss + $distributionLoss + $odpSplitterLoss + $dropLoss;
        $calculatedRx = round($txOlt - $totalLoss, 1);
        $actualRx = $onu->rx_power_dbm !== null ? (float) $onu->rx_power_dbm : $calculatedRx;
        $deviation = round($actualRx - $calculatedRx, 2);

        return [
            'tx_olt' => $txOlt,
            'feeder_loss' => $feederLoss,
            'odc_splitter_loss' => $odcSplitterLoss,
            'distribution_loss' => $distributionLoss,
            'odp_splitter_loss' => $odpSplitterLoss,
            'drop_loss' => $dropLoss,
            'total_attenuation' => round($totalLoss, 1),
            'calculated_rx' => $calculatedRx,
            'actual_rx' => $actualRx,
            'deviation' => $deviation,
            'status' => $actualRx >= -24.0 ? 'ideal' : ($actualRx >= -27.0 ? 'marginal' : 'critical'),
        ];
    }
}
