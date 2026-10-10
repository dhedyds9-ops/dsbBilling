<?php

// 1. Add to RouterOSDriver
$file1 = 'app/Integration/MikroTik/Drivers/RouterOSDriver.php';
$content1 = file_get_contents($file1);
$methods1 = <<<'EOT'
    public function getDhcpServers(): array
    {
        try {
            return ->retryEngine->execute(function () {
                return ->connection->query('/ip/dhcp-server/print')->read();
            }) ?? [];
        } catch (\Throwable ) {
            return [];
        }
    }

    public function getDhcpLeases(): array
    {
        try {
            return ->retryEngine->execute(function () {
                return ->connection->query('/ip/dhcp-server/lease/print')->read();
            }) ?? [];
        } catch (\Throwable ) {
            return [];
        }
    }

    public function getFirewallFilters(): array
    {
        try {
            return ->retryEngine->execute(function () {
                return ->connection->query('/ip/firewall/filter/print')->read();
            }) ?? [];
        } catch (\Throwable ) {
            return [];
        }
    }

    public function getFirewallNat(): array
    {
        try {
            return ->retryEngine->execute(function () {
                return ->connection->query('/ip/firewall/nat/print')->read();
            }) ?? [];
        } catch (\Throwable ) {
            return [];
        }
    }

    public function getRoutes(): array
    {
        try {
            return ->retryEngine->execute(function () {
                return ->connection->query('/ip/route/print')->read();
            }) ?? [];
        } catch (\Throwable ) {
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
$methods2 = <<<'EOT'

    public function getDhcpServers(): array
    {
        return ->execute(, function () {
            return ->getDhcpServers();
        }, []);
    }

    public function getDhcpLeases(): array
    {
        return ->execute(, function () {
            return ->getDhcpLeases();
        }, []);
    }

    public function getFirewallFilters(): array
    {
        return ->execute(, function () {
            return ->getFirewallFilters();
        }, []);
    }

    public function getFirewallNat(): array
    {
        return ->execute(, function () {
            return ->getFirewallNat();
        }, []);
    }

    public function getRoutes(): array
    {
        return ->execute(, function () {
            return ->getRoutes();
        }, []);
    }
EOT;

$pos2 = strrpos($content2, '}');
if ($pos2 !== false) {
    $content2 = substr_replace($content2, $methods2 . "\n}", $pos2, 1);
    file_put_contents($file2, $content2);
}

