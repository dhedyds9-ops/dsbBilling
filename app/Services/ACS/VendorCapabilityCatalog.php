<?php

namespace App\Services\ACS;

/**
 * VendorCapabilityCatalog
 *
 * Single Source of Truth for Multi-Vendor TR-069 & TR-181 Parameter Mappings,
 * Hardware Capabilities, and Verification Rules across ZTE, Huawei, FiberHome, CDATA, and Generic ONUs.
 */
class VendorCapabilityCatalog
{
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_DISCOVERED = 'Discovered';
    public const STATUS_VERIFIED_READ = 'Verified Read';
    public const STATUS_VERIFIED_WRITE = 'Verified Write';
    public const STATUS_UNSUPPORTED = 'Unsupported';
    public const STATUS_FAILED = 'Failed';

    public const STATUS_PHYSICALLY_VERIFIED = 'PHYSICALLY_VERIFIED';
    public const STATUS_NOT_PHYSICALLY_VERIFIED = 'NOT_PHYSICALLY_VERIFIED';

    /**
     * Pre-defined vendor profiles with tested firmware and known parameter paths.
     */
    protected static array $catalog = [
        'zte' => [
            'vendor_name' => 'ZTE',
            'oui' => ['00259E', '0019C6', '002293', 'ZTEG', '5A544547'],
            'models' => [
                'F670L' => [
                    'product_class' => 'F670L',
                    'data_model' => 'TR-098',
                    'hardware_versions' => ['V9.0', 'V1.0', 'V2.0'],
                    'software_versions' => ['V9.0.10P1N1', 'V9.0.10P2N1', 'V1.1.10P1T1'],
                    'bands' => ['2.4GHz', '5GHz'],
                    'max_ssids_24' => 4,
                    'max_ssids_5' => 4,
                    'lan_ports' => 4,
                    'optical' => [
                        'rx_path' => 'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower',
                        'tx_path' => 'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower',
                        'temp_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverTemperature',
                        'voltage_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverVoltage',
                        'bias_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverBiasCurrent',
                        'swap_rx_tx' => true, // ZTE firmware inversion
                        'rx_unit' => '0.01 dBm / dBm',
                        'formula' => 'val > 0 && val <= 6.0 ? tx_swapped : (val >= 60 ? val / 100 : val)',
                    ],
                    'wifi' => [
                        'base_path_24' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_24' => [1, 2, 3, 4],
                        'base_path_5' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_5' => [5, 6, 7, 8],
                        'rf_band_param' => 'X_ZTE-COM_RFBand',
                        'channel_param' => 'Channel',
                    ],
                    'wan' => [
                        'vlan_param' => 'X_ZTE-COM_VLANID',
                        'vlan_enable_param' => 'X_ZTE-COM_VLANEnable',
                        'service_list_param' => 'X_ZTE-COM_ServiceList',
                        'port_bind_param' => 'X_ZTE-COM_PortBind',
                    ],
                    'status' => self::STATUS_VERIFIED_WRITE,
                    'physical_verification' => self::STATUS_PHYSICALLY_VERIFIED,
                ],
                'F609' => [
                    'product_class' => 'F609',
                    'data_model' => 'TR-098',
                    'hardware_versions' => ['V3.0', 'V4.0', 'V5.3', 'V9.0'],
                    'software_versions' => ['V5.3.0P1T2', 'V7.0.10P1N1', 'V9.0.10P1N1'],
                    'bands' => ['2.4GHz'],
                    'max_ssids_24' => 4,
                    'max_ssids_5' => 0,
                    'lan_ports' => 4,
                    'optical' => [
                        'rx_path' => 'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.RXPower',
                        'tx_path' => 'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_WANPONInterfaceConfig.TXPower',
                        'temp_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverTemperature',
                        'voltage_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverVoltage',
                        'bias_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverBiasCurrent',
                        'swap_rx_tx' => true,
                        'rx_unit' => '0.01 dBm / dBm',
                        'formula' => 'val > 0 && val <= 6.0 ? tx_swapped : (val >= 60 ? val / 100 : val)',
                    ],
                    'wifi' => [
                        'base_path_24' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_24' => [1, 2, 3, 4],
                        'base_path_5' => null,
                        'instances_5' => [],
                        'rf_band_param' => 'X_ZTE-COM_RFBand',
                        'channel_param' => 'Channel',
                    ],
                    'wan' => [
                        'vlan_param' => 'X_ZTE-COM_VLANID',
                        'vlan_enable_param' => 'X_ZTE-COM_VLANEnable',
                        'service_list_param' => 'X_ZTE-COM_ServiceList',
                        'port_bind_param' => 'X_ZTE-COM_PortBind',
                    ],
                    'status' => self::STATUS_VERIFIED_WRITE,
                    'physical_verification' => self::STATUS_PHYSICALLY_VERIFIED,
                ],
            ],
        ],

        'huawei' => [
            'vendor_name' => 'Huawei',
            'oui' => ['00259E', '001E10', '000FB5', 'HWTC', '48575443'],
            'models' => [
                'HG8245H' => [
                    'product_class' => 'HG8245H',
                    'data_model' => 'TR-098',
                    'hardware_versions' => ['V3.0', 'V1.0', 'V5.0'],
                    'software_versions' => ['V3R017C10S115', 'V3R015C10S106'],
                    'bands' => ['2.4GHz'],
                    'max_ssids_24' => 4,
                    'max_ssids_5' => 0,
                    'lan_ports' => 4,
                    'optical' => [
                        'rx_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower',
                        'tx_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower',
                        'temp_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TransceiverTemperature',
                        'voltage_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.Voltage',
                        'bias_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.BiasCurrent',
                        'swap_rx_tx' => false,
                        'rx_unit' => '0.01 dBm / dBm',
                        'formula' => 'val >= 60 ? val / 100 : val',
                    ],
                    'wifi' => [
                        'base_path_24' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_24' => [1, 2, 3, 4],
                        'base_path_5' => null,
                        'instances_5' => [],
                        'rf_band_param' => 'X_HW_RFBand',
                        'channel_param' => 'Channel',
                    ],
                    'wan' => [
                        'vlan_param' => 'X_HW_VLAN',
                        'vlan_enable_param' => null,
                        'service_list_param' => 'X_HW_ServiceList',
                        'port_bind_param' => 'X_HW_LANBinding',
                    ],
                    'status' => self::STATUS_VERIFIED_WRITE,
                    'physical_verification' => self::STATUS_PHYSICALLY_VERIFIED,
                ],
                'EG8141H5' => [
                    'product_class' => 'EG8141H5',
                    'data_model' => 'TR-098',
                    'hardware_versions' => ['V1.0', 'V2.0'],
                    'software_versions' => ['V5R019C00S105', 'V5R020C00S125'],
                    'bands' => ['2.4GHz'],
                    'max_ssids_24' => 4,
                    'max_ssids_5' => 0,
                    'lan_ports' => 4,
                    'optical' => [
                        'rx_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.RXPower',
                        'tx_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.TXPower',
                        'temp_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TransceiverTemperature',
                        'voltage_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.Voltage',
                        'bias_path' => 'InternetGatewayDevice.WANDevice.1.X_HW_PONInterfaceConfig.BiasCurrent',
                        'swap_rx_tx' => false,
                        'rx_unit' => '0.01 dBm / dBm',
                        'formula' => 'val >= 60 ? val / 100 : val',
                    ],
                    'wifi' => [
                        'base_path_24' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_24' => [1, 2, 3, 4],
                        'base_path_5' => null,
                        'instances_5' => [],
                        'rf_band_param' => 'X_HW_RFBand',
                        'channel_param' => 'Channel',
                    ],
                    'wan' => [
                        'vlan_param' => 'X_HW_VLAN',
                        'vlan_enable_param' => null,
                        'service_list_param' => 'X_HW_ServiceList',
                        'port_bind_param' => 'X_HW_LANBinding',
                    ],
                    'status' => self::STATUS_VERIFIED_WRITE,
                    'physical_verification' => self::STATUS_PHYSICALLY_VERIFIED,
                ],
            ],
        ],

        'fiberhome' => [
            'vendor_name' => 'FiberHome',
            'oui' => ['46485454', 'FHTT', '000A17'],
            'models' => [
                'HG6145F' => [
                    'product_class' => 'HG6145F',
                    'data_model' => 'TR-098',
                    'hardware_versions' => ['W201', 'W200'],
                    'software_versions' => ['RP0105', 'RP0102'],
                    'bands' => ['2.4GHz', '5GHz'],
                    'max_ssids_24' => 4,
                    'max_ssids_5' => 4,
                    'lan_ports' => 4,
                    'optical' => [
                        'rx_path' => 'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower',
                        'tx_path' => 'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower',
                        'temp_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TransceiverTemperature',
                        'voltage_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TransceiverVoltage',
                        'bias_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TransceiverBiasCurrent',
                        'swap_rx_tx' => false,
                        'rx_unit' => '0.01 dBm / dBm',
                        'formula' => 'val >= 60 ? val / 100 : val',
                    ],
                    'wifi' => [
                        'base_path_24' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_24' => [1, 2, 3, 4],
                        'base_path_5' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_5' => [5, 6, 7, 8],
                        'rf_band_param' => 'X_FH_Band',
                        'channel_param' => 'Channel',
                    ],
                    'wan' => [
                        'vlan_param' => 'VLANIDMark',
                        'vlan_enable_param' => null,
                        'service_list_param' => 'X_FH_ServiceList',
                        'port_bind_param' => 'X_FH_LanInterface',
                    ],
                    'status' => self::STATUS_VERIFIED_WRITE,
                    'physical_verification' => self::STATUS_PHYSICALLY_VERIFIED,
                ],
                'HG6045F3' => [
                    'product_class' => 'HG6045F3',
                    'data_model' => 'TR-098',
                    'hardware_versions' => ['V1.0', 'W101'],
                    'software_versions' => ['RP0102', 'RP0101'],
                    'bands' => ['2.4GHz'],
                    'max_ssids_24' => 4,
                    'max_ssids_5' => 0,
                    'lan_ports' => 1,
                    'optical' => [
                        'rx_path' => 'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.RXPower',
                        'tx_path' => 'InternetGatewayDevice.WANDevice.1.X_FH_GponInterfaceConfig.TXPower',
                        'temp_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TransceiverTemperature',
                        'voltage_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TransceiverVoltage',
                        'bias_path' => 'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TransceiverBiasCurrent',
                        'swap_rx_tx' => false,
                        'rx_unit' => '0.01 dBm / dBm',
                        'formula' => 'val >= 60 ? val / 100 : val',
                    ],
                    'wifi' => [
                        'base_path_24' => 'InternetGatewayDevice.LANDevice.1.WLANConfiguration',
                        'instances_24' => [1, 2, 3, 4],
                        'base_path_5' => null,
                        'instances_5' => [],
                        'rf_band_param' => 'X_FH_Band',
                        'channel_param' => 'Channel',
                    ],
                    'wan' => [
                        'vlan_param' => 'VLANIDMark',
                        'vlan_enable_param' => null,
                        'service_list_param' => 'X_FH_ServiceList',
                        'port_bind_param' => 'X_FH_LanInterface',
                    ],
                    'status' => self::STATUS_VERIFIED_WRITE,
                    'physical_verification' => self::STATUS_PHYSICALLY_VERIFIED,
                ],
            ],
        ],

        'generic_tr181' => [
            'vendor_name' => 'Generic TR-181',
            'oui' => [],
            'models' => [
                'TR181_DEFAULT' => [
                    'product_class' => 'Generic TR-181 Device',
                    'data_model' => 'TR-181',
                    'bands' => ['2.4GHz', '5GHz'],
                    'lan_ports' => 4,
                    'optical' => [
                        'rx_path' => 'Device.Optical.Interface.1.RxPower',
                        'tx_path' => 'Device.Optical.Interface.1.TxPower',
                        'temp_path' => 'Device.Optical.1.Transceiver.Temperature',
                        'voltage_path' => 'Device.Optical.1.Transceiver.Voltage',
                        'bias_path' => 'Device.Optical.1.Transceiver.BiasCurrent',
                        'swap_rx_tx' => false,
                        'rx_unit' => 'dBm',
                        'formula' => 'val',
                    ],
                    'wifi' => [
                        'radio_path' => 'Device.WiFi.Radio',
                        'ssid_path' => 'Device.WiFi.SSID',
                        'ap_path' => 'Device.WiFi.AccessPoint',
                    ],
                    'wan' => [
                        'ppp_path' => 'Device.PPP.Interface',
                        'ip_path' => 'Device.IP.Interface',
                    ],
                    'status' => self::STATUS_DISCOVERED,
                    'physical_verification' => self::STATUS_NOT_PHYSICALLY_VERIFIED,
                ],
            ],
        ],
    ];

    /**
     * Resolve vendor profile from device identity.
     */
    public static function resolveProfile(?string $manufacturer, ?string $productClass, ?string $serialNumber): ?array
    {
        $mfg = strtolower(trim((string)$manufacturer));
        $pc = strtoupper(trim((string)$productClass));
        $sn = strtoupper(trim((string)$serialNumber));

        $vendorKey = null;
        if (str_contains($mfg, 'zte') || str_starts_with($sn, 'ZTEG') || str_starts_with($sn, '5A544547')) {
            $vendorKey = 'zte';
        } elseif (str_contains($mfg, 'huawei') || str_starts_with($sn, 'HWTC') || str_starts_with($sn, '48575443')) {
            $vendorKey = 'huawei';
        } elseif (str_contains($mfg, 'fiberhome') || str_starts_with($sn, 'FHTT') || str_starts_with($sn, '46485454')) {
            $vendorKey = 'fiberhome';
        }

        if (!$vendorKey || !isset(self::$catalog[$vendorKey])) {
            return null;
        }

        $models = self::$catalog[$vendorKey]['models'];
        foreach ($models as $modelKey => $profile) {
            if ($pc !== '' && (str_contains($pc, strtoupper($modelKey)) || str_contains(strtoupper($modelKey), $pc))) {
                return array_merge($profile, ['vendor_key' => $vendorKey, 'matched_model' => $modelKey]);
            }
        }

        // Return first model as representative vendor baseline
        $firstKey = array_key_first($models);
        return array_merge($models[$firstKey], ['vendor_key' => $vendorKey, 'matched_model' => $firstKey]);
    }

    /**
     * Return entire catalog for documentation and auditing.
     */
    public static function getCatalog(): array
    {
        return self::$catalog;
    }

    /**
     * Check whether a device model is physically verified with live hardware lab testing.
     */
    public static function isPhysicallyVerified(?string $manufacturer, ?string $productClass, ?string $serialNumber): bool
    {
        $profile = self::resolveProfile($manufacturer, $productClass, $serialNumber);
        return ($profile['physical_verification'] ?? null) === self::STATUS_PHYSICALLY_VERIFIED;
    }

    /**
     * Get the verified physical LAN port count for a device (e.g. 1 for HG6045F3, 4 for F670L).
     */
    public static function getPhysicalLanPortCount(?string $manufacturer, ?string $productClass, ?string $serialNumber): int
    {
        $profile = self::resolveProfile($manufacturer, $productClass, $serialNumber);
        return (int)($profile['lan_ports'] ?? 4);
    }
}
