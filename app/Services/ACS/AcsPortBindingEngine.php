<?php

namespace App\Services\ACS;

/**
 * AcsPortBindingEngine
 *
 * Discovers and normalizes physical LAN port binding relationships
 * across Huawei, ZTE, FiberHome, and TR-181 Bridging models.
 */
class AcsPortBindingEngine
{
    /**
     * Discover Port Binding across TR-098 and TR-181 for LAN1 - LAN4 and Wi-Fi.
     */
    public static function discover(array $params, array $wans = [], string $vendor = 'generic'): array
    {
        // 1. Detect actual physical port instances from TR-098 or TR-181
        $detectedPortIndices = [];
        $lanDevices = $params['InternetGatewayDevice']['LANDevice'] ?? [];
        if (is_array($lanDevices)) {
            foreach ($lanDevices as $ldIdx => $ldNode) {
                if (!is_numeric($ldIdx) || !is_array($ldNode)) continue;
                $lanConfigs = $ldNode['LANEthernetInterfaceConfig'] ?? [];
                if (is_array($lanConfigs)) {
                    foreach ($lanConfigs as $idx => $node) {
                        if (!is_numeric($idx) || !is_array($node)) continue;
                        $detectedPortIndices[(int)$idx] = [
                            'path' => "InternetGatewayDevice.LANDevice.{$ldIdx}.LANEthernetInterfaceConfig.{$idx}",
                            'node' => $node,
                        ];
                    }
                }
            }
        }

        if (empty($detectedPortIndices)) {
            $ethInterfaces = $params['Device']['Ethernet']['Interface'] ?? [];
            if (is_array($ethInterfaces)) {
                foreach ($ethInterfaces as $idx => $node) {
                    if (!is_numeric($idx) || !is_array($node)) continue;
                    $detectedPortIndices[(int)$idx] = [
                        'path' => "Device.Ethernet.Interface.{$idx}",
                        'node' => $node,
                    ];
                }
            }
        }

        // 2. Resolve port count: use exact detected TR-069 instances, or fallback to catalog specification
        if ($vendor === 'generic') {
            $detected = AcsTelemetryParser::detectVendor($params);
            if ($detected !== 'generic') {
                $vendor = $detected;
            }
        }
        $model = AcsTelemetryParser::parseModelName($params);
        $sn = AcsTelemetryParser::parseSerialNumber($params);
        $catalogCount = VendorCapabilityCatalog::getPhysicalLanPortCount($vendor, $model, $sn);

        $ports = [];
        if (!empty($detectedPortIndices)) {
            ksort($detectedPortIndices);
            foreach ($detectedPortIndices as $idx => $meta) {
                $portKey = "LAN{$idx}";
                $node = $meta['node'];
                $port = self::emptyPort($portKey, $idx);
                $port['exists'] = true;
                $speedVal = $node['MaxBitRate']['_value'] ?? $node['MaxBitRate'] ?? ($node['Status']['_value'] ?? 'Auto');
                $port['speed'] = is_numeric($speedVal) ? $speedVal . 'M' : (string)$speedVal;
                $statusVal = $node['Status']['_value'] ?? $node['Status'] ?? 'Unknown';
                $port['status'] = (string)$statusVal;
                $port['status_physical'] = (string)$statusVal;
                $port['physical_status'] = (strcasecmp($statusVal, 'Up') === 0) ? 'Link Up' : ((strcasecmp($statusVal, 'Down') === 0 || strcasecmp($statusVal, 'Dormant') === 0) ? 'Link Down' : (string)$statusVal);
                $port['path'] = $meta['path'];
                $ports[$portKey] = $port;
            }
        } else {
            // Not reported in TR-069 telemetry: create ports according to catalog specification (e.g. 1 for HG6045F3, 4 for F670L)
            for ($i = 1; $i <= $catalogCount; $i++) {
                $portKey = "LAN{$i}";
                $port = self::emptyPort($portKey, $i);
                $port['exists'] = true;
                $ports[$portKey] = $port;
            }
        }

        // 3. Correlate with discovered WAN connections
        foreach ($wans as $wan) {
            $bindStr = $wan['port_bind'] ?? '';
            $wanName = $wan['name'] ?? $wan['id'] ?? 'WAN';
            $wanMode = $wan['mode_label'] ?? $wan['type'] ?? 'PPPoE';
            $vlan = $wan['vlan'] ?? null;
            $fullPath = $wan['fullPath'] ?? ($wan['full_path'] ?? '');

            if (empty($bindStr) || $bindStr === '-') {
                continue;
            }

            foreach ($ports as $portKey => &$port) {
                $i = $port['index'];
                $patterns = [
                    "LAN{$i}",
                    "lan{$i}",
                    "LANEthernetInterfaceConfig.{$i}",
                    "Ethernet.Interface.{$i}",
                    "eth{$i}",
                    "port{$i}",
                ];

                $matched = false;
                foreach ($patterns as $pat) {
                    if (stripos($bindStr, $pat) !== false) {
                        $matched = true;
                        break;
                    }
                }

                if ($matched) {
                    $port['is_bound'] = true;
                    $port['binding_status'] = 'Bound';
                    $port['bound_wan'] = $wanName;
                    $port['bound_wan_name'] = $wanName;
                    $port['bound_wan_path'] = $fullPath;
                    $port['wan_mode'] = $wanMode;
                    $port['vlan'] = $vlan;
                    $port['vlan_id'] = $vlan;
                    $port['binding_raw'] = $bindStr;
                    $port['writable'] = (bool)($wan['writable'] ?? true);
                }
            }
            unset($port);
        }

        // 3b. Scan direct parameter tree for vendor inline binding if not yet resolved from $wans
        $flat = AcsTelemetryParser::flatten($params);
        foreach ($flat as $k => $v) {
            if ((str_contains($k, 'LANBIND') || str_contains($k, 'LanInterface') || str_contains($k, 'PortBind')) && is_scalar($v) && !empty($v)) {
                $bindStr = (string)$v;
                $pathPrefix = substr($k, 0, strrpos($k, '.'));
                $matchedWan = null;
                foreach ($wans as $w) {
                    $wPath = $w['full_path'] ?? ($w['fullPath'] ?? '');
                    if ($wPath && (str_starts_with($pathPrefix, $wPath) || str_starts_with($wPath, $pathPrefix))) {
                        $matchedWan = $w;
                        break;
                    }
                }
                $wanName = $matchedWan['name'] ?? ($flat["{$pathPrefix}.Name"] ?? ($flat["{$pathPrefix}._name"] ?? 'WAN'));
                $vlan = $matchedWan['vlan_id'] ?? ($matchedWan['vlan'] ?? ($flat["{$pathPrefix}.X_HW_VLAN"] ?? ($flat["{$pathPrefix}.X_ZTE-COM_VLANID"] ?? ($flat["{$pathPrefix}.VLANIDMark"] ?? null))));

                foreach ($ports as $portKey => &$port) {
                    $i = $port['index'];
                    if (stripos($bindStr, "LAN{$i}") !== false || stripos($bindStr, "eth{$i}") !== false) {
                        $port['is_bound'] = true;
                        $port['binding_status'] = 'Bound';
                        $port['bound_wan'] = $wanName;
                        $port['bound_wan_name'] = $wanName;
                        $port['bound_wan_path'] = $pathPrefix;
                        $port['vlan'] = $vlan;
                        $port['vlan_id'] = $vlan;
                        $port['binding_raw'] = $bindStr;
                    }
                }
                unset($port);
            }
        }

        // 4. Check ZTE Port Binding Table (InternetGatewayDevice.X_ZTE-COM_PortBinding)
        $zteBindingNode = $params['InternetGatewayDevice']['X_ZTE-COM_PortBinding'] ?? null;
        if (is_array($zteBindingNode)) {
            foreach ($zteBindingNode as $bIdx => $bNode) {
                if (!is_array($bNode)) continue;
                $wanIf = $bNode['WANInterface']['_value'] ?? '';
                $lanIf = $bNode['LANInterface']['_value'] ?? '';

                foreach ($ports as $portKey => &$port) {
                    $i = $port['index'];
                    if (stripos($lanIf, "LAN{$i}") !== false || stripos($lanIf, ".{$i}") !== false) {
                        $matchedWan = null;
                        foreach ($wans as $w) {
                            $wPath = $w['full_path'] ?? ($w['fullPath'] ?? '');
                            if ($wPath && ($wPath === $wanIf || str_contains($wanIf, $wPath))) {
                                $matchedWan = $w;
                                break;
                            }
                        }
                        $port['is_bound'] = true;
                        $port['binding_status'] = 'Bound';
                        $port['bound_wan'] = $matchedWan['name'] ?? ($wanIf ?: 'ZTE WAN');
                        $port['bound_wan_name'] = $matchedWan['name'] ?? ($wanIf ?: 'ZTE WAN');
                        $port['bound_wan_path'] = $wanIf;
                        $port['vlan'] = $matchedWan['vlan_id'] ?? ($matchedWan['vlan'] ?? null);
                        $port['vlan_id'] = $matchedWan['vlan_id'] ?? ($matchedWan['vlan'] ?? null);
                        $port['status_note'] = 'ZTE Table Binding';
                    }
                }
                unset($port);
            }
        }

        // Final normalization: ensure binding_status and status_display are populated
        foreach ($ports as &$p) {
            $p['binding_status'] = $p['is_bound'] ? 'Bound' : 'Unbound';
            $phys = $p['physical_status'] ?? ($p['status_physical'] ?? 'Unknown');
            $p['status_display'] = "{$p['binding_status']} ({$phys})";
        }
        unset($p);

        return [
            'ports' => $ports,
            'lan_ports' => array_values($ports),
            'total_lan_ports' => count($ports),
            'vendor' => $vendor,
            'summary' => self::buildSummary($ports),
        ];
    }

    private static function emptyPort(string $name, int $index): array
    {
        return [
            'name' => $name,
            'index' => $index,
            'port' => $index,
            'exists' => false,
            'is_bound' => false,
            'binding_status' => 'Unbound',
            'bound_wan' => 'Unbound',
            'bound_wan_name' => 'Unbound (Tidak Terikat)',
            'bound_wan_path' => null,
            'wan_mode' => 'None',
            'vlan' => null,
            'vlan_id' => null,
            'speed' => 'Auto',
            'status' => 'Unknown',
            'status_physical' => 'Unknown',
            'physical_status' => 'Unknown',
            'status_display' => 'Unbound (Unknown)',
            'path' => null,
            'binding_raw' => null,
            'writable' => true,
        ];
    }

    private static function buildSummary(array $ports): string
    {
        $parts = [];
        foreach ($ports as $k => $p) {
            if ($p['is_bound']) {
                $vlanStr = $p['vlan'] ? " (VLAN {$p['vlan']})" : '';
                $parts[] = "{$k} → {$p['bound_wan_name']}{$vlanStr}";
            } else {
                $parts[] = "{$k} (Bebas)";
            }
        }
        return implode(' | ', $parts);
    }

    /**
     * Discover physical ports as a sequential list of port objects.
     */
    public static function discoverPhysicalPorts(array $params, array $wans = [], string $vendor = 'generic'): array
    {
        $res = self::discover($params, $wans, $vendor);
        return $res['lan_ports'];
    }
}
