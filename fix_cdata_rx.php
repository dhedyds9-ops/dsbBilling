<?php
$file = 'app/Services/Adapters/Provisioning/Drivers/CDataOltDriver.php';
$content = file_get_contents($file);

$search = <<<PHP
            if (\$this->cachedOnuStatuses === null) {
                \$this->cachedOnuStatuses = \$this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.13') ?: [];
            }
PHP;

$replace = <<<PHP
            // Coba fetch RX Power menggunakan OID lama, jika gagal baru fallback ke status saja.
            \$baseOld = \$this->onuInfoOid . '.' . \$ponPort;
            \$rxTest = \$this->snmp->walk(\$baseOld . '.6');
            if (!empty(\$rxTest)) {
                // Artinya OLT ini mendukung OID lama meskipun firmware baru!
                // Lempar exception buatan agar blok ini selesai dan melanjutkan ke logika OID lama di bawah
                throw new \Exception("Switch to old branch SNMP logic");
            }

            if (\$this->cachedOnuStatuses === null) {
                \$this->cachedOnuStatuses = \$this->snmp->walk('.1.3.6.1.4.1.34592.1.3.100.9.2.1.13') ?: [];
            }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added fallback exception for CData rx power\n";
