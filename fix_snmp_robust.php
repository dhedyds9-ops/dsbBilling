<?php
$file = 'app/Services/Adapters/Provisioning/Support/SnmpClient.php';
$content = file_get_contents($file);

// Revert my bad str_replace
$content = str_replace(
    '$this->timeout * 1000000',
    '$this->timeout',
    $content
);

// Fallback logic
$search = <<<PHP
        if (self::\$extSnmpAvailable) {
            \$result = @snmpget(\$this->host, \$this->community, \$oid, \$this->timeout, \$this->retries);
            if (\$result === false) {
                return false;
            }
            return \$this->parseValue(\$result);
        }
PHP;

$replace = <<<PHP
        if (self::\$extSnmpAvailable) {
            // Coba dengan fungsi bawaan PHP
            // Set SNMP options agar tidak error MIB
            @snmp_set_oid_numeric_print(true);
            @snmp_set_quick_print(true);
            @snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
            
            \$result = @snmpget(\$this->host, \$this->community, \$oid, \$this->timeout, \$this->retries);
            if (\$result !== false) {
                return \$this->parseValue(\$result);
            }
            // Jika gagal (mungkin karena MIB), JANGAN langsung false, tapi fallback ke CLI!
        }
PHP;

$content = str_replace($search, $replace, $content);

$searchWalk = <<<PHP
        if (self::\$extSnmpAvailable) {
            \$result = @snmprealwalk(\$this->host, \$this->community, \$oid, \$this->timeout, \$this->retries);
            if (\$result === false) {
                return [];
            }
            return \$this->parseWalkResult(\$result);
        }
PHP;

$replaceWalk = <<<PHP
        if (self::\$extSnmpAvailable) {
            @snmp_set_oid_numeric_print(true);
            @snmp_set_quick_print(true);
            @snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
            
            \$result = @snmprealwalk(\$this->host, \$this->community, \$oid, \$this->timeout, \$this->retries);
            if (\$result !== false && !empty(\$result)) {
                return \$this->parseWalkResult(\$result);
            }
        }
PHP;

$content = str_replace($searchWalk, $replaceWalk, $content);

file_put_contents($file, $content);
echo "Robust SnmpClient fix applied.\n";
