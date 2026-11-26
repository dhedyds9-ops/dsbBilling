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
        $ports = [
            'LAN1' => self::emptyPort('LAN1', 1),
            'LAN2' => self::emptyPort('LAN2', 2),
            'LAN3' => self::emptyPort('LAN3', 3),
            'LAN4' => self::emptyPort('LAN4', 4),
        ];

        // 1. Check physical LAN port existence in TR-098
        $lanDevices = $params['InternetGatewayDevice']['LANDevice'] ?? [];
        if (is_array($lanDevices)) {
            foreach ($lanDevices as $ldIdx => $ldNode) {
                if (!is_numeric($ldIdx) || !is_array($ldNode)) continue;
                $lanConfigs = $ldNode['LANEthernetInterfaceConfig'] ?? [];
                if (is_array($lanConfigs)) {
                    foreach ($lanConfigs as $idx => $node) {
                        if (!is_numeric($idx) || !is_array($node)) continue;
                        $portKey = "LAN{$idx}";
                        if (isset($ports[$portKey])) {
                            $ports[$portKey]['exists'] = true;
                            $speedVal = $node['MaxBitRate']['_value'] ?? $node['MaxBitRate'] ?? ($node['Status']['_value'] ?? 'Auto');
                            $ports[$portKey]['speed'] = is_numeric($speedVal) ? $speedVal . 'M' : (string)$speedVal;
                            $statusVal = $node['Status']['_value'] ?? $node['Status'] ?? 'Up';
                            $ports[$portKey]['status'] = (string)$statusVal;
                            $ports[$portKey]['status_physical'] = (string)$statusVal;
                            $ports[$portKey]['path'] = "InternetGatewayDevice.LANDevice.{$ldIdx}.LANEthernetInterfaceConfig.{$idx}";
                        }
                    }
                }
            }
        }

        // 2. Check physical LAN port existence in TR-181
        if (!collect($ports)->contains('exists', true)) {
            $ethInterfaces = $params['Device']['Ethernet']['Interface'] ?? [];
            if (is_array($ethInterfaces)) {
                foreach ($ethInterfaces as $idx => $node) {
                    if (!is_numeric($idx) || !is_array($node)) continue;
                    $portKey = "LAN{$idx}";
                    if (isset($ports[$portKey])) {
                        $ports[$portKey]['exists'] = true;
                        $ports[$portKey]['speed'] = $node['MaxBitRate']['_value'] ?? 'Auto';
                        $ports[$portKey]['status_physical'] = $node['Status']['_value'] ?? 'Up';
                        $ports[$portKey]['path'] = "Device.Ethernet.Interface.{$idx}";
                    }
                }
            }
        }

        // If no physical LAN detected via TR-069, default LAN1-LAN4 as present
        foreach ($ports as &$p) {
            if (!$p['exists']) {
                $p['exists'] = true; // Most ONUs physically have LAN1..LAN4
            }
        }
        unset($p);

        // 3. Correlate with discovered WAN connections
        foreach ($wans as $wan) {
            $bindStr = $wan['port_bind'] ?? '';
            $wanName = $wan['name'] ?? $wan['id'] ?? 'WAN';
            $wanMode = $wan['mode_label'] ?? $wan['type'] ?? 'PPPoE';
            $vlan = $wan['vlan'] ?? null;
            $fullPath = $wan['fullPath'] ?? '';

            if (empty($bindStr) || $bindStr === '-') {
                continue;
            }

            for ($i = 1; $i <= 4; $i++) {
                $portKey = "LAN{$i}";
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
                    $ports[$portKey]['is_bound'] = true;
                    $ports[$portKey]['bound_wan'] = $wanName;
                    $ports[$portKey]['bound_wan_name'] = $wanName;
                    $ports[$portKey]['bound_wan_path'] = $fullPath;
                    $ports[$portKey]['wan_mode'] = $wanMode;
                    $ports[$portKey]['vlan'] = $vlan;
                    $ports[$portKey]['vlan_id'] = $vlan;
                    $ports[$portKey]['binding_raw'] = $bindStr;
                    $ports[$portKey]['writable'] = (bool)($wan['writable'] ?? true);
                }
            }
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
                $wanName = $matchedWan['name'] ?? 'WAN';
                $vlan = $matchedWan['vlan_id'] ?? ($matchedWan['vlan'] ?? null);

                for ($i = 1; $i <= 4; $i++) {
                    $portKey = "LAN{$i}";
                    if (stripos($bindStr, "LAN{$i}") !== false || stripos($bindStr, "eth{$i}") !== false) {
                        $ports[$portKey]['is_bound'] = true;
                        $ports[$portKey]['bound_wan'] = $wanName;
                        $ports[$portKey]['bound_wan_name'] = $wanName;
                        $ports[$portKey]['bound_wan_path'] = $pathPrefix;
                        $ports[$portKey]['vlan'] = $vlan;
                        $ports[$portKey]['vlan_id'] = $vlan;
                        $ports[$portKey]['binding_raw'] = $bindStr;
                    }
                }
            }
        }

        // 4. Check ZTE Port Binding Table (InternetGatewayDevice.X_ZTE-COM_PortBinding)
        $zteBindingNode = $params['InternetGatewayDevice']['X_ZTE-COM_PortBinding'] ?? null;
        if (is_array($zteBindingNode)) {
            foreach ($zteBindingNode as $bIdx => $bNode) {
                if (!is_array($bNode)) continue;
                $wanIf = $bNode['WANInterface']['_value'] ?? '';
                $lanIf = $bNode['LANInterface']['_value'] ?? '';

                for ($i = 1; $i <= 4; $i++) {
                    $portKey = "LAN{$i}";
                    if (stripos($lanIf, "LAN{$i}") !== false || stripos($lanIf, ".{$i}") !== false) {
                        $matchedWan = null;
                        foreach ($wans as $w) {
                            $wPath = $w['full_path'] ?? ($w['fullPath'] ?? '');
                            if ($wPath && ($wPath === $wanIf || str_contains($wanIf, $wPath))) {
                                $matchedWan = $w;
                                break;
                            }
                        }
                        $ports[$portKey]['is_bound'] = true;
                        $ports[$portKey]['bound_wan'] = $matchedWan['name'] ?? ($wanIf ?: 'ZTE WAN');
                        $ports[$portKey]['bound_wan_name'] = $matchedWan['name'] ?? ($wanIf ?: 'ZTE WAN');
                        $ports[$portKey]['bound_wan_path'] = $wanIf;
                        $ports[$portKey]['vlan'] = $matchedWan['vlan_id'] ?? ($matchedWan['vlan'] ?? null);
                        $ports[$portKey]['vlan_id'] = $matchedWan['vlan_id'] ?? ($matchedWan['vlan'] ?? null);
                        $ports[$portKey]['status_note'] = 'ZTE Table Binding';
                    }
                }
            }
        }

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
            'bound_wan' => 'Unbound',
            'bound_wan_name' => 'Unbound (Tidak Terikat)',
            'bound_wan_path' => null,
            'wan_mode' => 'None',
            'vlan' => null,
            'vlan_id' => null,
            'speed' => 'Auto',
            'status' => 'Unknown',
            'status_physical' => 'Unknown',
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
}
