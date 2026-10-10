<?php
$file = 'app/Livewire/Isp/Router/Show.php';
$content = file_get_contents($file);

// Insert variables
$vars = <<<'EOT'
    public string  = '';
    public string  = '';
    public  = [];
    public  = [];
    public  = [];
    public  = [];
    public  = [];
EOT;
$content = preg_replace('/public \ = \[\];/', "public \ = [];\n" . $vars, $content);

// Insert loadData branches
$loadData = <<<'EOT'
        } elseif (->activeTab === 'dhcp') {
            ->dhcpServers = ->getDhcpServers(->router);
            ->dhcpLeases = ->getDhcpLeases(->router);
        } elseif (->activeTab === 'firewall') {
            ->firewallFilters = ->getFirewallFilters(->router);
            ->firewallNat = ->getFirewallNat(->router);
        } elseif (->activeTab === 'routing') {
            ->routes = ->getRoutes(->router);
        } elseif (->activeTab === 'traffic') {
            ->interfaces = ->getInterfaceStats(->router);
EOT;
$content = preg_replace('/\} elseif \(\->activeTab === \'logs\'\) \{/', $loadData . "\n        } elseif (\->activeTab === 'logs') {", $content);

file_put_contents($file, $content);
