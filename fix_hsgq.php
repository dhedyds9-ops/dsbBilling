<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/HsgqOltDriver.php';
$content = file_get_contents($file);

$search = <<<PHP
                foreach (\$rawSerials as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    \$onuIndex = (int)end(\$parts);
                    \$this->cachedOnuSerials[\$onuIndex] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawStatuses as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    \$onuIndex = (int)end(\$parts);
                    \$this->cachedOnuStatuses[\$onuIndex] = (int)\$val;
                }
                foreach (\$rawNames as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    \$onuIndex = (int)end(\$parts);
                    \$this->cachedOnuNames[\$onuIndex] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawModels as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    \$onuIndex = (int)end(\$parts);
                    \$this->cachedOnuModels[\$onuIndex] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawFws as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    \$onuIndex = (int)end(\$parts);
                    \$this->cachedOnuFws[\$onuIndex] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawRx as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    if (count(\$parts) >= 3) {
                        \$onuIndex = (int)\$parts[count(\$parts) - 3];
                        \$rawVal = (int)\$val;
                        if (\$rawVal !== -4000) {
                            \$this->cachedOnuRx[\$onuIndex] = round(\$rawVal / 100, 2);
                        }
                    }
                }
                foreach (\$rawTx as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    if (count(\$parts) >= 3) {
                        \$onuIndex = (int)\$parts[count(\$parts) - 3];
                        \$this->cachedOnuTx[\$onuIndex] = round((int)\$val / 100, 2);
                    }
                }
                foreach (\$rawTemps as \$idx => \$val) {
                    \$cleanOid = str_replace('iso', '.1', \$idx);
                    \$parts = explode('.', \$cleanOid);
                    if (count(\$parts) >= 3) {
                        \$onuIndex = (int)\$parts[count(\$parts) - 3];
                        \$this->cachedOnuTemps[\$onuIndex] = round((int)\$val / 10, 1);
                    }
                }
PHP;

$replace = <<<PHP
                \$parseIdx = function(\$idx) {
                    \$parts = explode('.', \$idx);
                    if (count(\$parts) >= 2) {
                        \$onuId = (int)array_pop(\$parts);
                        \$port = (int)array_pop(\$parts);
                        return \$port + \$onuId;
                    }
                    return (int)\$idx;
                };

                foreach (\$rawSerials as \$idx => \$val) {
                    \$this->cachedOnuSerials[\$parseIdx(\$idx)] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawStatuses as \$idx => \$val) {
                    \$this->cachedOnuStatuses[\$parseIdx(\$idx)] = (int)\$val;
                }
                foreach (\$rawNames as \$idx => \$val) {
                    \$this->cachedOnuNames[\$parseIdx(\$idx)] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawModels as \$idx => \$val) {
                    \$this->cachedOnuModels[\$parseIdx(\$idx)] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawFws as \$idx => \$val) {
                    \$this->cachedOnuFws[\$parseIdx(\$idx)] = \$this->cleanSnmpString(\$val);
                }
                foreach (\$rawRx as \$idx => \$val) {
                    \$rawVal = (int)\$val;
                    if (\$rawVal !== -4000) {
                        \$this->cachedOnuRx[\$parseIdx(\$idx)] = round(\$rawVal / 100, 2);
                    }
                }
                foreach (\$rawTx as \$idx => \$val) {
                    \$this->cachedOnuTx[\$parseIdx(\$idx)] = round((int)\$val / 100, 2);
                }
                foreach (\$rawTemps as \$idx => \$val) {
                    \$this->cachedOnuTemps[\$parseIdx(\$idx)] = round((int)\$val / 10, 1);
                }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed HSGQ array parsers\n";
