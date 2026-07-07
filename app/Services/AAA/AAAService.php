<?php

namespace App\Services\AAA;

use App\Models\AAA\PPPoEUser;
use App\Models\AAA\HotspotUser;
use App\Models\AAA\RadiusAccounting;
use Illuminate\Support\Facades\Hash;
use Src\Domain\AAA\AAAServiceInterface;

class AAAService implements AAAServiceInterface
{
    public function authenticate(string $username, string $password): bool
    {
        $pppoeUser = PPPoEUser::where('username', $username)
            ->where('status', 'active')
            ->first();

        if ($pppoeUser && Hash::check($password, $pppoeUser->password)) {
            return true;
        }

        $hotspotUser = HotspotUser::where('username', $username)
            ->where('status', 'active')
            ->first();

        return $hotspotUser && Hash::check($password, $hotspotUser->password);
    }

    public function authorize(string $username, string $service): bool
    {
        $pppoeUser = PPPoEUser::with('customerService')->where('username', $username)
            ->where('status', 'active')
            ->first();
        
        if ($pppoeUser && $pppoeUser->customerService) {
            return true;
        }

        $hotspotUser = HotspotUser::with('customerService')->where('username', $username)
            ->where('status', 'active')
            ->first();

        return $hotspotUser && $hotspotUser->customerService;
    }

    public function account(string $username, array $data): bool
    {
        $pppoeUser = PPPoEUser::where('username', $username)->first();
        $hotspotUser = HotspotUser::where('username', $username)->first();
        
        if (!$pppoeUser && !$hotspotUser) {
            return false;
        }

        RadiusAccounting::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'acct_session_id' => $data['acct_session_id'] ?? \Illuminate\Support\Str::random(16),
            'username' => $username,
            'nas_ip_address' => $data['nas_ip_address'] ?? null,
            'nas_port_id' => $data['nas_port_id'] ?? null,
            'framed_ip_address' => $data['framed_ip_address'] ?? null,
            'framed_protocol' => $data['framed_protocol'] ?? null,
            'acct_start_time' => isset($data['acct_start_time']) ? \Carbon\Carbon::parse($data['acct_start_time']) : now(),
            'acct_stop_time' => isset($data['acct_stop_time']) ? \Carbon\Carbon::parse($data['acct_stop_time']) : null,
            'acct_input_octets' => $data['acct_input_octets'] ?? 0,
            'acct_output_octets' => $data['acct_output_octets'] ?? 0,
            'acct_input_packets' => $data['acct_input_packets'] ?? 0,
            'acct_output_packets' => $data['acct_output_packets'] ?? 0,
            'acct_session_time' => $data['acct_session_time'] ?? 0,
            'acct_terminate_cause' => $data['acct_terminate_cause'] ?? null,
            'pppoe_user_id' => $pppoeUser?->id,
            'hotspot_user_id' => $hotspotUser?->id,
            'customer_service_id' => $pppoeUser?->customer_service_id ?? $hotspotUser?->customer_service_id,
        ]);

        return true;
    }
}
