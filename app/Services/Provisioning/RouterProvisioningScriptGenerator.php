<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Router;
use App\Models\Provisioning\NetworkProfile;
use Illuminate\Support\Str;

class RouterProvisioningScriptGenerator
{
    public function generateScript(Router $router, string $uplinkInterface = 'ether1', array $options = []): string
    {
        $appUrl = rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');
        $rawHost = parse_url($appUrl, PHP_URL_HOST) ?? '127.0.0.1';
        $host = filter_var($rawHost, FILTER_VALIDATE_IP) ? $rawHost : gethostbyname($rawHost);

        // Check if options specify connection_mode or custom host
        $isVpn = (!empty($options['connection_mode']) && $options['connection_mode'] === 'vpn');
        
        if ($isVpn) {
            $host = '10.200.0.1';
        } elseif (!empty($options['host'])) {
            $host = filter_var($options['host'], FILTER_VALIDATE_IP) ? $options['host'] : gethostbyname($options['host']);
        } else {
            // Jika gethostbyname mengembalikan IP local (127.x.x.x akibat /etc/hosts pada VPS),
            // ambil IP Public server yang sesungguhnya agar MikroTik bisa menghubungi RADIUS!
            if (str_starts_with($host, '127.') || $host === '::1' || $host === 'localhost') {
                try {
                    $dnsRecords = @dns_get_record($rawHost, DNS_A);
                    if (!empty($dnsRecords[0]['ip']) && !str_starts_with($dnsRecords[0]['ip'], '127.')) {
                        $host = $dnsRecords[0]['ip'];
                    }
                } catch (\Throwable $e) {}

                if ((str_starts_with($host, '127.') || $host === 'localhost') && !empty($_SERVER['SERVER_ADDR']) && !str_starts_with($_SERVER['SERVER_ADDR'], '127.')) {
                    $host = $_SERVER['SERVER_ADDR'];
                }

                if (str_starts_with($host, '127.') || $host === 'localhost') {
                    $cachedIp = \Illuminate\Support\Facades\Cache::remember('server_public_ip', 86400, function() {
                        try {
                            $ctx = stream_context_create(['http' => ['timeout' => 2]]);
                            $res = @file_get_contents('https://api.ipify.org', false, $ctx);
                            return filter_var(trim($res), FILTER_VALIDATE_IP) ?: null;
                        } catch (\Throwable $e) {
                            return null;
                        }
                    });
                    if ($cachedIp) {
                        $host = $cachedIp;
                    }
                }
            }
        }
        
        $lines = [];
        $lines[] = "# =================================================================";
        $lines[] = "# dsBilling MikroTik Setup & Auto-Provisioning Script";
        $lines[] = "# Router: " . ($router->name ?: 'MikroTik');
        $lines[] = "# Generated at: " . now()->toDateTimeString();
        $lines[] = "# Salin dan tempel perintah ini di: WinBox -> New Terminal";
        $lines[] = "# =================================================================";
        $lines[] = "";

        // 0. Anti-duplikasi pembersihan aturan lama
        $lines[] = ":do { /radius remove [find comment~\"dsbilling\"] } on-error={}";
        $lines[] = ":do { /user remove [find comment~\"dsbilling\"] } on-error={}";
        $lines[] = ":do { /user group remove [find comment~\"dsbilling\"] } on-error={}";
        $lines[] = ":do { /ip firewall filter remove [find comment~\"managed-by=dsbilling\"] } on-error={}";
        $lines[] = ":do { /ip firewall filter remove [find comment~\"dsBilling\"] } on-error={}";
        $lines[] = ":do { /ip firewall nat remove [find comment~\"managed-by=dsbilling\"] } on-error={}";
        $lines[] = ":do { /ip firewall address-list remove [find list=\"ISOLIR_LIST\"] } on-error={}";
        $lines[] = ":do { /ip firewall address-list remove [find list=\"ISOLIR_WHITELIST\"] } on-error={}";
        $lines[] = ":do { /ip proxy access remove [find comment~\"managed-by=dsbilling\"] } on-error={}";
        $lines[] = "";

        // 1. API User Creation
        $apiUsername = $router->username ?? 'api.dsb.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $router->name ?: 'router'));
        $apiPassword = $router->password ?? Str::random(16);
        
        if ($router->exists && ($router->username !== $apiUsername || $router->password !== $apiPassword)) {
            $router->update([
                'username' => $apiUsername,
                'password' => $apiPassword,
            ]);
        }

        $lines[] = ":do { /user group add name=dsb-api policy=api,read,write,policy,test,!local,!telnet,!ssh,!ftp,!reboot,!winbox,!password,!web,!sniff,!sensitive,!romon,!dude comment=\"managed-by=dsbilling\" } on-error={ :put \"Group exists\" }";
        $lines[] = ":do { /user add name=\"{$apiUsername}\" password=\"{$apiPassword}\" group=dsb-api comment=\"managed-by=dsbilling\" } on-error={ :put \"User exists\" }";
        $lines[] = "/ip service set api disabled=no";
        $lines[] = "";

        // 2. RADIUS Client Configuration
        $radiusSecret = env('RADIUS_SECRET', 'dsBillingSecret123');
        if ($router->exists && $router->radius_secret !== $radiusSecret) {
            $router->update(['radius_secret' => $radiusSecret]);
            try {
                $radiusNas = \App\Models\ISP\RadiusNas::withoutGlobalScopes()->firstOrCreate(
                    ['nas_ip_address' => $router->ip_address],
                    [
                        'uuid' => Str::uuid(),
                        'nas_name' => $router->name,
                        'nas_type' => 'mikrotik',
                        'status' => 'active',
                        'created_by' => 1,
                        'updated_by' => 1
                    ]
                );
                $radiusNas->update(['nas_secret' => $radiusSecret]);
            } catch (\Throwable $e) {}
        }

        $lines[] = ":do { /radius add address={$host} secret=\"{$radiusSecret}\" service=ppp,hotspot,login authentication-port=1812 accounting-port=1813 timeout=3000ms comment=\"managed-by=dsbilling\" } on-error={ /radius set [find comment~\"dsbilling\"] address={$host} secret=\"{$radiusSecret}\" service=ppp,hotspot,login authentication-port=1812 accounting-port=1813 timeout=3000ms }";
        $lines[] = ":do { /radius incoming set accept=yes port=3799 } on-error={ :put \"Radius incoming configured\" }";
        $lines[] = ":do { /ip pool add name=pool-pppoe ranges=10.20.20.2-10.20.20.254 comment=\"managed-by=dsbilling\" } on-error={}";
        $lines[] = ":do { /ppp profile set [find name=default] local-address=10.20.20.1 remote-address=pool-pppoe dns-server=8.8.8.8,1.1.1.1 } on-error={}";
        $lines[] = ":do { /ppp aaa set use-radius=yes interim-update=00:02:00 accounting=yes } on-error={ :put \"PPP AAA configured\" }";
        $lines[] = ":do { /ip hotspot profile set use-radius=yes radius-accounting=yes radius-interim-update=00:02:00 login-by=http-pap,cookie,mac-cookie [find] } on-error={}";
        $lines[] = ":do { /ip hotspot user profile set mac-cookie-timeout=1w [find] } on-error={}";
        $lines[] = ":do { /ip firewall nat add chain=srcnat action=masquerade src-address=10.20.20.0/24 comment=\"managed-by=dsbilling: NAT PPPoE\" } on-error={}";
        $lines[] = "";

        // 3. Network Profiles (VLANs and PPPoE Servers)
        $profiles = $router->id ? NetworkProfile::where('router_id', $router->id)->get() : collect();
        
        foreach ($profiles as $profile) {
            if ($profile->vlan_id) {
                $vlanName = "dsb-vlan-{$profile->vlan_id}";
                $lines[] = ":do { /interface vlan add name=\"{$vlanName}\" vlan-id={$profile->vlan_id} interface=\"{$uplinkInterface}\" comment=\"managed-by=dsbilling\" } on-error={ :put \"VLAN {$profile->vlan_id} exists\" }";
                
                if ($profile->type === 'pppoe') {
                    $serverName = "dsb-pppoe-{$profile->vlan_id}";
                    $lines[] = ":do { /interface pppoe-server server add name=\"{$serverName}\" interface=\"{$vlanName}\" service-name=\"{$serverName}\" authentication=pap,chap,mschap1,mschap2 one-session-per-host=yes default-profile=default comment=\"managed-by=dsbilling\" disabled=no } on-error={ :put \"PPPoE Server {$serverName} exists\" }";
                } elseif ($profile->type === 'hotspot') {
                    $lines[] = "# Hotspot interface {$vlanName} prepared.";
                }
            }
        }
        
        $lines[] = "";
        
        // 4. Walled Garden (Isolir) Setup & Halaman Pembayaran
        $billingDomain = $rawHost;
        $lines[] = "# 4. Walled Garden (Isolir) Setup & Halaman Pembayaran";
        $lines[] = "# 4.1 IP Pool & Profile Isolir";
        $lines[] = ":do { /ip pool add name=pool-isolir ranges=10.100.0.2-10.100.0.254 comment=\"managed-by=dsbilling: Pool Isolir\" } on-error={ :put \"Pool Isolir exists\" }";
        $lines[] = ":do { /ppp profile add name=ISOLIR local-address=10.100.0.1 remote-address=pool-isolir address-list=ISOLIR_LIST dns-server=8.8.8.8,1.1.1.1 comment=\"managed-by=dsbilling: Profile Isolir\" } on-error={ /ppp profile set [find name=ISOLIR] local-address=10.100.0.1 remote-address=pool-isolir address-list=ISOLIR_LIST dns-server=8.8.8.8,1.1.1.1 }";
        $lines[] = ":do { /ip firewall nat add chain=srcnat action=masquerade src-address=10.100.0.0/24 comment=\"managed-by=dsbilling: NAT Isolir\" } on-error={}";
        $lines[] = ":do { /ip firewall address-list add list=ISOLIR_LIST address=\"10.100.0.0/24\" comment=\"managed-by=dsbilling: Subnet Isolir\" } on-error={}";
        $lines[] = "";

        $lines[] = "# 4.2 Web Proxy Internal MikroTik untuk Redirect ke Halaman Tagihan";
        $lines[] = ":do { /ip proxy set enabled=yes port=8080 } on-error={}";
        $lines[] = ":do { /ip proxy access remove [find comment~\"managed-by=dsbilling\"] } on-error={}";
        $lines[] = ":do { /ip proxy access add action=deny redirect-to=\"{$billingDomain}/bayar-tagihan\" comment=\"managed-by=dsbilling: Redirect Isolir\" } on-error={ /ip proxy access set [find comment~\"managed-by=dsbilling\"] action=deny redirect-to=\"{$billingDomain}/bayar-tagihan\" }";
        $lines[] = "";

        $lines[] = "# 4.3 Whitelist Billing & Payment Gateway";
        $lines[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"{$billingDomain}\" comment=\"managed-by=dsbilling: Server\" } on-error={ :put \"Whitelist Billing exists\" }";
        if ($host !== $billingDomain) {
            $lines[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"{$host}\" comment=\"managed-by=dsbilling: Host IP\" } on-error={}";
        }
        $lines[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"app.midtrans.com\" comment=\"managed-by=dsbilling: Payment\" } on-error={ :put \"Whitelist Midtrans exists\" }";
        $lines[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"api.midtrans.com\" comment=\"managed-by=dsbilling: Payment\" } on-error={ :put \"Whitelist Midtrans API exists\" }";
        $lines[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"xendit.co\" comment=\"managed-by=dsbilling: Payment\" } on-error={ :put \"Whitelist Xendit exists\" }";
        $lines[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"whatsapp.com\" comment=\"managed-by=dsbilling: WA\" } on-error={ :put \"Whitelist WA exists\" }";
        $lines[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"whatsapp.net\" comment=\"managed-by=dsbilling: WA\" } on-error={ :put \"Whitelist WA Net exists\" }";
        $lines[] = "";

        $lines[] = "# 4.4 Redirect HTTP Port 80 Isolir ke Web Proxy internal";
        $lines[] = ":do { /ip firewall nat add chain=dstnat action=redirect to-ports=8080 protocol=tcp src-address-list=ISOLIR_LIST dst-address-list=!ISOLIR_WHITELIST dst-port=80 comment=\"managed-by=dsbilling: Redirect Isolir HTTP to Proxy\" } on-error={ :put \"NAT Isolir HTTP Proxy exists\" }";
        $lines[] = "";

        $lines[] = "# 4.5 Filter Rules: Allow DNS, Allow Whitelist, Drop Semua Akses Internet Lain";
        $lines[] = ":do { /ip firewall filter add chain=forward action=accept protocol=udp src-address-list=ISOLIR_LIST dst-port=53 comment=\"managed-by=dsbilling: Allow Isolir DNS\" } on-error={ :put \"Filter DNS UDP exists\" }";
        $lines[] = ":do { /ip firewall filter add chain=forward action=accept protocol=tcp src-address-list=ISOLIR_LIST dst-port=53 comment=\"managed-by=dsbilling: Allow Isolir DNS\" } on-error={ :put \"Filter DNS TCP exists\" }";
        $lines[] = ":do { /ip firewall filter add chain=forward action=accept src-address-list=ISOLIR_LIST dst-address-list=ISOLIR_WHITELIST comment=\"managed-by=dsbilling: Allow Isolir Whitelist\" } on-error={ :put \"Filter Whitelist exists\" }";
        $lines[] = ":do { /ip firewall filter add chain=forward action=drop src-address-list=ISOLIR_LIST comment=\"managed-by=dsbilling: Drop other Isolir Traffic\" } on-error={ :put \"Filter Drop exists\" }";
        $lines[] = "";
        $lines[] = "# 4.6 Prioritas Filter & NAT: Pindahkan ke baris teratas sebelum FastTrack / Established";
        $lines[] = ":do { /ip firewall filter move [find comment~\"managed-by=dsbilling: Allow Isolir\"] 0 } on-error={}";
        $lines[] = ":do { /ip firewall filter move [find comment~\"managed-by=dsbilling: Drop other Isolir\"] 1 } on-error={}";
        $lines[] = ":do { /ip firewall nat move [find comment~\"managed-by=dsbilling: Redirect Isolir\"] 0 } on-error={}";
        $lines[] = "";

        $lines[] = ":put \"==================================================\";";
        $lines[] = ":put \"[SUCCESS] Konfigurasi dsBilling Berhasil Diterapkan!\";";
        $lines[] = ":put \"==================================================\";";

        return implode("\n", $lines);
    }
}
