<?php
$f = 'app/Services/Adapters/Provisioning/Drivers/HsgqOltDriver.php';
$c = file_get_contents($f);
$old = <<<OLD
            }
            return $ports;
        }

        $statuses = $this->snmp->walk($this->ponPortStatusOid);
        $ports = [];
        foreach ($statuses as $idx => $status) {
            $ports[] = [
                'port_index' => $idx,
                'port_name' => "PON $idx",
                'status' => match ((int)$status) {
                    1, 101 => 'up',
                    2, 102 => 'down',
                    default => 'unknown'
                },
            ];
        }
        return $ports;
    }
OLD;

$new = <<<NEW
            }
        }
        
        if (empty($ports)) {
            $statuses = $this->snmp->walk($this->ponPortStatusOid);
            foreach ($statuses as $idx => $status) {
                $ports[] = [
                    'port_index' => $idx,
                    'port_name' => "GPON 0/0/$idx",
                    'status' => match ((int)$status) {
                        1, 101 => 'up',
                        2, 102 => 'down',
                        default => 'unknown'
                    },
                ];
            }
        }
        return $ports;
    }
NEW;

$c = str_replace(str_replace("\r\n", "\n", $old), str_replace("\r\n", "\n", $new), str_replace("\r\n", "\n", $c));
file_put_contents($f, $c);
