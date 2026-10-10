<?php

// 1. Add to RouterOSDriver
$file1 = 'app/Integration/MikroTik/Drivers/RouterOSDriver.php';
$content1 = file_get_contents($file1);
$methods1 = <<<EOT
    public function getDhcpServers(): array
    {
        try {
            return $this->retryEngine->execute(function () {
                return $this->connection->query('/ip/dhcp-server/print')->read();
            }) ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getDhcpLeases(): array
    {
        try {
            return $this->retryEngine->execute(function () {
                return $this->connection->query('/ip/dhcp-server/lease/print')->read();
            }) ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getFirewallFilters(): array
    {
        try {
            return $this->retryEngine->execute(function () {
                return $this->connection->query('/ip/firewall/filter/print')->read();
            }) ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getFirewallNat(): array
    {
        try {
            return $this->retryEngine->execute(function () {
                return $this->connection->query('/ip/firewall/nat/print')->read();
            }) ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getRoutes(): array
    {
        try {
            return $this->retryEngine->execute(function () {
                return $this->connection->query('/ip/route/print')->read();
            }) ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }
EOT;

// insert right before the last closing brace
$pos1 = strrpos($content1, '}');
if ($pos1 !== false) {
    $content1 = substr_replace($content1, $methods1 . "\n}", $pos1, 1);
    file_put_contents($file1, $content1);
}

// 2. Add to MikroTikDriver wrapper
$file2 = 'app/Services/Adapters/Monitoring/MikroTikDriver.php';
$content2 = file_get_contents($file2);
$methods2 = <<<EOT

    public function getDhcpServers($device): array
    {
        return $this->execute($device, function ($driver) {
            return $driver->getDhcpServers();
        }, []);
    }

    public function getDhcpLeases($device): array
    {
        return $this->execute($device, function ($driver) {
            return $driver->getDhcpLeases();
        }, []);
    }

    public function getFirewallFilters($device): array
    {
        return $this->execute($device, function ($driver) {
            return $driver->getFirewallFilters();
        }, []);
    }

    public function getFirewallNat($device): array
    {
        return $this->execute($device, function ($driver) {
            return $driver->getFirewallNat();
        }, []);
    }

    public function getRoutes($device): array
    {
        return $this->execute($device, function ($driver) {
            return $driver->getRoutes();
        }, []);
    }
EOT;

$pos2 = strrpos($content2, '}');
if ($pos2 !== false) {
    $content2 = substr_replace($content2, $methods2 . "\n}", $pos2, 1);
    file_put_contents($file2, $content2);
}

