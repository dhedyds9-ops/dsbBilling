<?php
$f = 'app/Services/Adapters/Provisioning/Drivers/HsgqOltDriver.php';
$c = file_get_contents($f);
$old = "return $ports;
        }";
$new = "}

        // 2. Jika MIB HSGQ tidak menghasilkan PON port sama sekali (sering terjadi pada OLT HSGQ GPON OEM), gunakan fallback VSOL
        if (empty($ports)) {
            $statuses = $this->snmp->walk($this->ponPortStatusOid);
            if ($statuses) {
                foreach ($statuses as $idx => $status) {
                    $cleanOid = str_replace('iso', '.1', $idx);
                    $parts = explode('.', $cleanOid);
                    $portIdx = (int)end($parts);
                    $ports[] = [
                        'port_index' => $portIdx,
                        'port_name' => 'GPON 0/0/' . $portIdx,
                        'status' => match ((int)$status) {
                            1, 101 => 'up',
                            2, 102 => 'down',
                            default => 'unknown'
                        },
                    ];
                }
            }
        }
        return $ports;
        }";
$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo 'done';
