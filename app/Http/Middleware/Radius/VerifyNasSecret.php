<?php

namespace App\Http\Middleware\Radius;

use App\Models\ISP\RadiusNas;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyNasSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $nasIp = $this->resolveNasIp($request);
        $nasName = $request->input('nas_name') ?? $request->input('NAS-Identifier');

        if (!$nasIp && !$nasName) {
            Log::warning('Radius ingest: NAS identifier missing', ['req' => $request->only(['nas_ip_address', 'nas_name', 'username'])]);
            return response()->json(['error' => 'NAS identifier required'], 401);
        }

        $nas = $this->findNas($nasIp, $nasName);

        if (!$nas) {
            Log::warning('Radius ingest: NAS tidak terdaftar', ['nas_ip' => $nasIp, 'nas_name' => $nasName]);
            return response()->json(['error' => 'Unknown NAS'], 403);
        }

        $secret = $nas->nas_secret ?? null;
        if ($secret === null || $secret === '') {
            Log::warning("Radius ingest: NAS {$nas->nas_name} secret kosong, allow tanpa validasi HMAC");
            $request->attributes->set('radius_nas', $nas);
            return $next($request);
        }

        $providedSig = $request->header('X-Radius-Signature')
            ?? $request->input('message_authenticator')
            ?? $request->input('Message-Authenticator')
            ?? $request->input('signature');

        if ($providedSig !== null && $providedSig !== '') {
            $payload = $request->getContent();
            $expect = hash_hmac('md5', $payload, $secret);
            $alt = hash_hmac('sha256', $payload, $secret);
            if (!hash_equals($expect, (string)$providedSig) && !hash_equals($alt, (string)$providedSig)) {
                $legacy = md5($payload . $secret);
                if (!hash_equals($legacy, (string)$providedSig)) {
                    Log::warning('Radius ingest: HMAC signature mismatch', [
                        'nas' => $nas->nas_name,
                        'ip' => $nasIp,
                    ]);
                    return response()->json(['error' => 'Invalid NAS signature'], 403);
                }
            }
        }

        $request->attributes->set('radius_nas', $nas);
        return $next($request);
    }

    private function resolveNasIp(Request $request): ?string
    {
        $candidates = [
            $request->input('nas_ip_address'),
            $request->input('NAS-IP-Address'),
            $request->header('X-Forwarded-For') ? explode(',', $request->header('X-Forwarded-For'))[0] : null,
            $request->ip(),
        ];
        foreach ($candidates as $c) {
            $c = trim((string)$c);
            if ($c !== '' && filter_var($c, FILTER_VALIDATE_IP)) {
                return $c;
            }
        }
        return null;
    }

    private function findNas(?string $ip, ?string $name): ?RadiusNas
    {
        try {
            if ($ip) {
                $nas = RadiusNas::active()->where('nas_ip_address', $ip)->first();
                if ($nas) {
                    return $nas;
                }
            }
            if ($name) {
                return RadiusNas::active()->where('nas_name', $name)->first();
            }
        } catch (\Throwable $e) {
            Log::warning('Radius findNas exception', ['err' => $e->getMessage()]);
        }
        return null;
    }
}
