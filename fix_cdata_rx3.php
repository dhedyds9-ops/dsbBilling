<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/CDataOltDriver.php';
$content = file_get_contents($file);

// Remove the goto block
$searchGoto = <<<PHP
            // Coba fetch RX Power menggunakan OID lama, jika gagal baru fallback ke status saja.
            \$baseOld = \$this->onuInfoOid . '.' . \$ponPort;
            \$rxTest = \$this->snmp->walk(\$baseOld . '.6');
            if (!empty(\$rxTest)) {
                goto old_branch_snmp;
            }
PHP;
$content = str_replace($searchGoto, '', $content);

// Remove the label
$searchLabel = <<<PHP
        old_branch_snmp:
        \$results   = [];
PHP;
$content = str_replace($searchLabel, '        $results   = [];', $content);

// Now, insert the old-branch fetch logic into the new-branch fallback loop!
$searchLoop = <<<PHP
            \$dbOnus = Onu::where('olt_id', \$this->olt->id)
                ->where('pon_port', \$ponPort)
                ->get()
                ->keyBy('onu_id_on_olt');

            foreach (\$statuses as \$idx => \$statusVal) {
                \$cleanOid = str_replace('iso', '.1', \$idx);
                \$parts = explode('.', \$cleanOid);
                if (count(\$parts) >= 3) {
                    \$onuId = (int)array_pop(\$parts);
                    \$port = (int)array_pop(\$parts);
                    \$slot = (int)array_pop(\$parts);
                    
                    if (\$port === \$snmpPort) {
                        \$dbOnu = \$dbOnus->get(\$onuId);
                        \$serial = \$dbOnu ? \$dbOnu->serial_number : "CDATA-GPON-{\$ponPort}-{\$onuId}";
                        \$mac = \$dbOnu ? \$dbOnu->mac_address : null;
                        
                        \$results[] = [
                            'pon_port'         => \$ponPort,
                            'onu_index'        => \$onuId,
                            'serial_number'    => \$serial,
                            'mac_address'      => \$mac,
                            'rx_power_dbm'     => null,
                            'tx_power_dbm'     => null,
                            'snr_db'           => null,
                            'temperature'      => null,
                            'firmware_version' => \$dbOnu ? \$dbOnu->firmware_version : null,
                            'model'            => \$dbOnu ? \$dbOnu->model : null,
                            'status'           => match ((int)\$statusVal) {
                                1       => 'online',
                                default => 'offline'
                            },
                        ];
                    }
                }
            }
            return \$results;
PHP;

$replaceLoop = <<<PHP
            \$dbOnus = Onu::where('olt_id', \$this->olt->id)
                ->where('pon_port', \$ponPort)
                ->get()
                ->keyBy('onu_id_on_olt');

            \$baseOld = \$this->onuInfoOid . '.' . \$ponPort;
            \$rxRaw   = \$this->snmp->walk(\$baseOld . '.6') ?: [];
            \$txRaw   = \$this->snmp->walk(\$baseOld . '.7') ?: [];
            if (empty(\$txRaw)) \$txRaw = \$this->snmp->walk(\$baseOld . '.5') ?: [];
            \$tempRaw = \$this->snmp->walk(\$baseOld . '.8') ?: [];

            foreach (\$statuses as \$idx => \$statusVal) {
                \$cleanOid = str_replace('iso', '.1', \$idx);
                \$parts = explode('.', \$cleanOid);
                if (count(\$parts) >= 3) {
                    \$onuId = (int)array_pop(\$parts);
                    \$port = (int)array_pop(\$parts);
                    \$slot = (int)array_pop(\$parts);
                    
                    if (\$port === \$snmpPort) {
                        \$dbOnu = \$dbOnus->get(\$onuId);
                        \$serial = \$dbOnu ? \$dbOnu->serial_number : "CDATA-GPON-{\$ponPort}-{\$onuId}";
                        \$mac = \$dbOnu ? \$dbOnu->mac_address : null;
                        
                        \$rxVal = null;
                        \$rxRawVal = (int)(\$rxRaw[\$onuId] ?? 0);
                        if (\$rxRawVal < 0 && \$rxRawVal > -65536) {
                            \$rxVal = round(\$rxRawVal / 100, 2);
                        } elseif (\$rxRawVal > 65536) {
                            \$rxVal = round(10 * log10(\$rxRawVal / 10000), 2);
                        } elseif (\$rxRawVal > 0 && \$rxRawVal < 2000) {
                            \$rxVal = round(\$rxRawVal / 10 - 50, 2);
                        }

                        \$txVal = null;
                        \$txRawVal = (int)(\$txRaw[\$onuId] ?? 0);
                        if (\$txRawVal !== 0) {
                            if (\$txRawVal < 0 && \$txRawVal > -65536) {
                                \$txVal = round(\$txRawVal / 100, 2);
                            } elseif (\$txRawVal > 65536) {
                                \$txVal = round(10 * log10(\$txRawVal / 10000), 2);
                            } elseif (\$txRawVal > 0 && \$txRawVal < 2000) {
                                \$txVal = round(\$txRawVal / 10 - 50, 2);
                            }
                        }

                        \$tempVal = null;
                        if (!empty(\$tempRaw[\$onuId]) && is_numeric(\$tempRaw[\$onuId])) {
                            \$t = (int)\$tempRaw[\$onuId];
                            if (\$t !== 0 && \$t < 65535) {
                                \$tempVal = \$t > 1000 ? round(\$t / 100, 1) : \$t;
                            }
                        }

                        \$results[] = [
                            'pon_port'         => \$ponPort,
                            'onu_index'        => \$onuId,
                            'serial_number'    => \$serial,
                            'mac_address'      => \$mac,
                            'rx_power_dbm'     => \$rxVal,
                            'tx_power_dbm'     => \$txVal,
                            'snr_db'           => null,
                            'temperature'      => \$tempVal,
                            'firmware_version' => \$dbOnu ? \$dbOnu->firmware_version : null,
                            'model'            => \$dbOnu ? \$dbOnu->model : null,
                            'status'           => match ((int)\$statusVal) {
                                1       => 'online',
                                default => 'offline'
                            },
                        ];
                    }
                }
            }
            return \$results;
PHP;

$content = str_replace($searchLoop, $replaceLoop, $content);
file_put_contents($file, $content);
echo "Integrated power logic into new branch fallback\n";
