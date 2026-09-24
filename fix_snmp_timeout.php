<?php
$file = 'app/Services/Adapters/Provisioning/Support/SnmpClient.php';
$content = file_get_contents($file);

$content = str_replace(
    '@snmpget($this->host, $this->community, $oid, $this->timeout, $this->retries)',
    '@snmpget($this->host, $this->community, $oid, $this->timeout * 1000000, $this->retries)',
    $content
);

$content = str_replace(
    '@snmpset($this->host, $this->community, $oid, $type, $value, $this->timeout, $this->retries)',
    '@snmpset($this->host, $this->community, $oid, $type, $value, $this->timeout * 1000000, $this->retries)',
    $content
);

$content = str_replace(
    '@snmpwalk($this->host, $this->community, $oid, $this->timeout, $this->retries)',
    '@snmpwalk($this->host, $this->community, $oid, $this->timeout * 1000000, $this->retries)',
    $content
);

$content = str_replace(
    '@snmprealwalk($this->host, $this->community, $oid, $this->timeout, $this->retries)',
    '@snmprealwalk($this->host, $this->community, $oid, $this->timeout * 1000000, $this->retries)',
    $content
);

file_put_contents($file, $content);
echo "Replaced correctly.\n";
