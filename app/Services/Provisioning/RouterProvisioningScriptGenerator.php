<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Router;
use App\Models\Provisioning\NetworkProfile;
use Illuminate\Support\Str;

class RouterProvisioningScriptGenerator
{
    public function generateScript(Router $router, string $uplinkInterface = 'ether1'): string
    {
        $appUrl = rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');
        // If appUrl is localhost, we need an accessible IP. For testing, we use $_SERVER['HTTP_HOST'] or a fallback.
        $host = parse_url($appUrl, PHP_URL_HOST) ?? '127.0.0.1';
        
        $lines = [];
        $lines[] = "# dsBilling MikroTik Auto-Provisioning Script";
        $lines[] = "# Generated at: " . now()->toDateTimeString();
        $lines[] = "# Router: " . $router->name;
        $lines[] = "";

        // 1. API User Creation
        $apiUsername = $router->username ?? 'api.dsb.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $router->name));
        $apiPassword = $router->password ?? Str::random(16);
        
        // Ensure router has these credentials saved (usually they are generated in the Create component, but just in case)
        if ($router->username !== $apiUsername || $router->password !== $apiPassword) {
            $router->update([
                'username' => $apiUsername,
                'password' => $apiPassword,
            ]);
        }

        $lines[] = "/user group add name=dsb-api policy=api,read,write,policy,test,!local,!telnet,!ssh,!ftp,!reboot,!winbox,!password,!web,!sniff,!sensitive,!romon,!dude comment=\"managed-by=dsbilling\" || :put \"Group exists\"";
        $lines[] = "/user add name=\"{$apiUsername}\" password=\"{$apiPassword}\" group=dsb-api comment=\"managed-by=dsbilling\" || :put \"User exists\"";
        $lines[] = "/ip service set api disabled=no";
        $lines[] = "";

        // 2. RADIUS Client Configuration
        // Router radius_secret is the source of truth
        $radiusSecret = $router->radius_secret ?? Str::random(32);
        if ($router->radius_secret !== $radiusSecret) {
            $router->update(['radius_secret' => $radiusSecret]);
            // Sync to RadiusNas
            $radiusNas = \App\Models\ISP\RadiusNas::firstOrCreate(
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
        }

        $lines[] = "/radius add address=\"{$host}\" secret=\"{$radiusSecret}\" service=pppoe,hotspot comment=\"managed-by=dsbilling\" || :put \"Radius exists\"";
        $lines[] = "/radius incoming set accept=yes port=3799 || :put \"Radius incoming configured\"";
        $lines[] = "/ppp aaa set use-radius=yes interim-update=5m accounting=yes || :put \"PPP AAA configured\"";
        $lines[] = "";

        // 3. Network Profiles (VLANs and PPPoE Servers)
        $profiles = NetworkProfile::where('router_id', $router->id)->get();
        
        foreach ($profiles as $profile) {
            if ($profile->vlan_id) {
                $vlanName = "dsb-vlan-{$profile->vlan_id}";
                $lines[] = "/interface vlan add name=\"{$vlanName}\" vlan-id={$profile->vlan_id} interface=\"{$uplinkInterface}\" comment=\"managed-by=dsbilling\" || :put \"VLAN {$profile->vlan_id} exists\"";
                
                if ($profile->type === 'pppoe') {
                    $serverName = "dsb-pppoe-{$profile->vlan_id}";
                    $lines[] = "/interface pppoe-server server add name=\"{$serverName}\" interface=\"{$vlanName}\" service-name=\"{$serverName}\" authentication=pap,chap,mschap1,mschap2 one-session-per-host=yes default-profile=default comment=\"managed-by=dsbilling\" disabled=no || :put \"PPPoE Server {$serverName} exists\"";
                } elseif ($profile->type === 'hotspot') {
                    // Basic Hotspot bridge preparation (Actual Hotspot setup is complex and usually requires IP pool/DHCP)
                    // We prepare the interface.
                    $lines[] = "# Hotspot interface {$vlanName} prepared.";
                }
            }
        }
        
        $lines[] = "";
        
        // 4. VPN Failover Configuration (HA Setup)
        // Check if application has VPN servers configured in .env (e.g., DSB_VPN_SERVERS=ip1,ip2)
        $vpnServersStr = env('DSB_VPN_SERVERS', '');
        $vpnServers = array_filter(array_map('trim', explode(',', $vpnServersStr)));
        
        if (count($vpnServers) > 0) {
            $vpnUsername = "dsb_" . ($router->code ?? strtolower(Str::slug($router->name, '')));
            $vpnPassword = substr($radiusSecret, 0, 10); // Simple deterministic password
            $vpnInternalTarget = env('DSB_VPN_RADIUS_IP', '172.31.1.216'); // The Radius IP inside the VPN
            $vpnInternalSrc = $router->vpn_ip ?? '172.30.8.x';
            
            $lines[] = "# 4. VPN Failover Configuration (High Availability)";
            $lines[] = "/ppp profile add name=DSB-VPN use-encryption=yes change-tcp-mss=yes comment=\"managed-by=dsbilling\" || :put \"VPN Profile exists\"";
            
            foreach ($vpnServers as $index => $serverIp) {
                $num = $index + 1;
                $disabled = $num === 1 ? 'no' : 'yes'; // Only enable the first one by default
                $lines[] = "/interface ovpn-client add disabled={$disabled} connect-to={$serverIp} name=\"DSB-VPN-{$num}\" profile=DSB-VPN user=\"{$vpnUsername}\" password=\"{$vpnPassword}\" comment=\"DSB VPN Server {$num}\" || :put \"VPN {$num} exists\"";
            }
            
            $lines[] = "/system scheduler remove [find name=\"dsb_vpn_failover\"] || :put \"Old scheduler removed\"";
            $lines[] = "/system scheduler add interval=10s name=dsb_vpn_failover on-event=\"{\\r\\
    \\n:global dsbLastVpnIndex\\r\\
    \\n:local targetIP \\\"{$vpnInternalTarget}\\\"\\r\\
    \\n:local vpnIfaces [/interface find name~\\\"^DSB-VPN-\\\"]\\r\\
    \\n:local vpnCount [:len \$vpnIfaces]\\r\\
    \\n:if (\$vpnCount = 0) do={\\r\\
    \\n    :return\\r\\
    \\n}\\r\\
    \\n:if ([:typeof \$dsbLastVpnIndex] = \\\"nothing\\\") do={\\r\\
    \\n    :set dsbLastVpnIndex 0\\r\\
    \\n}\\r\\
    \\n:local pingResult [/ping \$targetIP count=5]\\r\\
    \\n:if (\$pingResult = 0) do={\\r\\
    \\n    :set dsbLastVpnIndex ((\$dsbLastVpnIndex + 1) % \$vpnCount)\\r\\
    \\n    /interface set \$vpnIfaces disabled=yes\\r\\
    \\n    :local selectedIface (\$vpnIfaces->\$dsbLastVpnIndex)\\r\\
    \\n    /interface set \$selectedIface disabled=no\\r\\
    \\n    :log warning \\\"DSB VPN Failover: Switching to VPN \$(\$dsbLastVpnIndex + 1)\\\"\\r\\
    \\n}\\r\\
}\" policy=read,write,policy,test start-time=startup comment=\"managed-by=dsbilling\"";
            $lines[] = "";
        }
        
        // 5. Walled Garden (Isolir) Firewall Setup
        $billingPort = parse_url($appUrl, PHP_URL_PORT) ?? 80;
        $lines[] = "# 5. Walled Garden (Isolir) Firewall Setup";
        $lines[] = "/ip firewall address-list add list=ISOLIR_WHITELIST address=\"{$host}\" comment=\"managed-by=dsbilling: Server\" || :put \"Whitelist Billing exists\"";
        $lines[] = "/ip firewall address-list add list=ISOLIR_WHITELIST address=\"app.midtrans.com\" comment=\"managed-by=dsbilling: Payment\" || :put \"Whitelist Midtrans exists\"";
        $lines[] = "/ip firewall address-list add list=ISOLIR_WHITELIST address=\"api.midtrans.com\" comment=\"managed-by=dsbilling: Payment\" || :put \"Whitelist Midtrans API exists\"";
        $lines[] = "/ip firewall address-list add list=ISOLIR_WHITELIST address=\"xendit.co\" comment=\"managed-by=dsbilling: Payment\" || :put \"Whitelist Xendit exists\"";
        $lines[] = "/ip firewall address-list add list=ISOLIR_WHITELIST address=\"whatsapp.com\" comment=\"managed-by=dsbilling: WA\" || :put \"Whitelist WA exists\"";
        $lines[] = "/ip firewall address-list add list=ISOLIR_WHITELIST address=\"whatsapp.net\" comment=\"managed-by=dsbilling: WA\" || :put \"Whitelist WA Net exists\"";
        $lines[] = "";
        $lines[] = "/ip firewall nat add chain=dstnat action=dst-nat to-addresses={$host} to-ports={$billingPort} protocol=tcp src-address-list=ISOLIR_LIST dst-address-list=!ISOLIR_WHITELIST dst-port=80 comment=\"managed-by=dsbilling: Redirect Isolir HTTP\" || :put \"NAT Isolir HTTP exists\"";
        $lines[] = "/ip firewall nat add chain=dstnat action=dst-nat to-addresses={$host} to-ports={$billingPort} protocol=tcp src-address-list=ISOLIR_LIST dst-address-list=!ISOLIR_WHITELIST dst-port=443 comment=\"managed-by=dsbilling: Redirect Isolir HTTPS\" || :put \"NAT Isolir HTTPS exists\"";
        $lines[] = "";
        $lines[] = "/ip firewall filter add chain=forward action=accept protocol=udp src-address-list=ISOLIR_LIST dst-port=53 comment=\"managed-by=dsbilling: Allow Isolir DNS\" || :put \"Filter DNS UDP exists\"";
        $lines[] = "/ip firewall filter add chain=forward action=accept protocol=tcp src-address-list=ISOLIR_LIST dst-port=53 comment=\"managed-by=dsbilling: Allow Isolir DNS\" || :put \"Filter DNS TCP exists\"";
        $lines[] = "/ip firewall filter add chain=forward action=accept src-address-list=ISOLIR_LIST dst-address-list=ISOLIR_WHITELIST comment=\"managed-by=dsbilling: Allow Isolir Whitelist\" || :put \"Filter Whitelist exists\"";
        $lines[] = "/ip firewall filter add chain=forward action=drop src-address-list=ISOLIR_LIST comment=\"managed-by=dsbilling: Drop other Isolir Traffic\" || :put \"Filter Drop exists\"";
        $lines[] = "";

        $lines[] = ":put \"\";";
        $lines[] = ":put \"[SUCCESS] Router Auto-Provisioning Complete.\";";

        return implode("\n", $lines);
    }
}
