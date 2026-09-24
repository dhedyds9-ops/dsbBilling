<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/CDataOltDriver.php';
$content = file_get_contents($file);

$searchLoop = <<<PHP
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
PHP;

$replaceLoop = <<<PHP
            // Fallback robust: Loop melalui ONU yang ada di Database untuk PON ini.
            // Karena SNMP C-Data .13 mengembalikan array linear yang kacau (semua port = 0),
            // kita gunakan DB sebagai acuan, lalu mapping RX Power dan Statusnya.
            foreach (\$dbOnus as \$onuId => \$dbOnu) {
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

                // Coba cari status di .13 menggunakan index linear.
                // Index linear CData biasanya: (snmpPort * 256) + onuId.
                // snmpPort = \$ponPort - 1.
                \$linearIndex = ((\$ponPort - 1) * 256) + \$onuId;
                \$searchKey = "1.0." . \$linearIndex;
                \$statusVal = \$statuses[\$searchKey] ?? null;

                // Jika tidak ketemu dengan multiplier 256, coba cari di output mentah 
                // jika OLT hanya menumpuknya (seperti EPON 64/128).
                // Sebagai fallback final: jika RX Power valid dan > -40, anggap online!
                \$isOnline = false;
                if (\$statusVal !== null) {
                    \$isOnline = ((int)\$statusVal === 1);
                } else {
                    if (\$rxVal !== null && \$rxVal > -40) {
                        \$isOnline = true;
                    }
                }

                \$results[] = [
                    'pon_port'         => \$ponPort,
                    'onu_index'        => \$onuId,
                    'serial_number'    => \$dbOnu->serial_number,
                    'mac_address'      => \$dbOnu->mac_address,
                    'rx_power_dbm'     => \$rxVal,
                    'tx_power_dbm'     => \$txVal,
                    'snr_db'           => null,
                    'temperature'      => \$tempVal,
                    'firmware_version' => \$dbOnu->firmware_version,
                    'model'            => \$dbOnu->model,
                    'status'           => \$isOnline ? 'online' : 'offline',
                ];
            }
PHP;

$content = str_replace($searchLoop, $replaceLoop, $content);
file_put_contents($file, $content);
echo "Updated CData loop to use DB as source of truth\n";
