<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/CDataOltDriver.php';
$content = file_get_contents($file);

$search = <<<PHP
            // Coba fetch RX Power menggunakan OID lama, jika gagal baru fallback ke status saja.
            \$baseOld = \$this->onuInfoOid . '.' . \$ponPort;
            \$rxTest = \$this->snmp->walk(\$baseOld . '.6');
            if (!empty(\$rxTest)) {
                // Artinya OLT ini mendukung OID lama meskipun firmware baru!
                // Lempar exception buatan agar blok ini selesai dan melanjutkan ke logika OID lama di bawah
                throw new \Exception("Switch to old branch SNMP logic");
            }
PHP;

$replace = <<<PHP
            // Coba fetch RX Power menggunakan OID lama, jika gagal baru fallback ke status saja.
            \$baseOld = \$this->onuInfoOid . '.' . \$ponPort;
            \$rxTest = \$this->snmp->walk(\$baseOld . '.6');
            if (!empty(\$rxTest)) {
                goto old_branch_snmp;
            }
PHP;

$search2 = <<<PHP
        \$results   = [];
        \$base      = \$this->onuInfoOid . '.' . \$ponPort;
PHP;

$replace2 = <<<PHP
        old_branch_snmp:
        \$results   = [];
        \$base      = \$this->onuInfoOid . '.' . \$ponPort;
PHP;

$content = str_replace($search, $replace, $content);
$content = str_replace($search2, $replace2, $content);
file_put_contents($file, $content);
echo "Fixed CData exception to goto\n";
