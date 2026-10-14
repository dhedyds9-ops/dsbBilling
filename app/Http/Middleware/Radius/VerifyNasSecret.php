<?php

namespace AppHttpMiddlewareRadius;

use AppModelsISPRadiusNas;
use AppModelsISPRouter;
use Closure;
use IlluminateHttpRequest;
use IlluminateSupportFacadesLog;
use IlluminateSupportStr;
use SymfonyComponentHttpFoundationResponse;

class VerifyNasSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $nasIp = $this->resolveNasIp($request);
        $nasName = $request->input('nas_name') ?? $request->input('NAS-Identifier');

        $nas = $this->findNas($nasIp, $nasName);

        if (!$nas) {
            Log::warning('Radius ingest: NAS tidak terdaftar', ['nas_ip' => $nasIp, 'nas_name' => $nasName]);
            return response()->json(['error' => 'Unknown NAS'], 403);
        }

        $secret = $nas->nas_secret ?? null;
        if ($secret === null || $secret === '') {
            Log::warning("Radius ingest: NAS {$nas->nas_name} secret kosong, reject request");
            return response()->json(['error' => 'HMAC validation failed: secret empty'], 403);
        }

        $providedSig = $request->header('X-Radius-Signature')
            ?? $request->input('message_authenticator')
            ?? $request->input('Message-Authenticator')
            ?? $request->input('signature');

        $uname = (string)($request->input('username') ?? '');
        $valid = false;

        if ($providedSig !== null && $providedSig !== '') {
            if (hash_equals($secret, (string)$providedSig) || (string)$providedSig === (string)$secret) {
                $valid = true;
            } elseif (md5($uname . $secret) === (string)$providedSig || md5($secret) === (string)$providedSig) {
                $valid = true;
            } else {
                $payload = $request->getContent();
                $expect = hash_hmac('md5', $payload, $secret);
                $alt = hash_hmac('sha256', $payload, $secret);
                $legacy = md5($payload . $secret);
                if (hash_equals($expect, (string)$providedSig) || hash_equals($alt, (string)$providedSig) || hash_equals($legacy, (string)$providedSig)) {
                    $valid = true;
                }
            }
        } elseif ($request->ip() === '127.0.0.1' || $request->ip() === '::1') {
            // Internal call from local FreeRADIUS on localhost
            $valid = true;
        }

        if (!$valid) {
            Log::warning('Radius ingest: Invalid NAS signature', [
                'nas' => $nas->nas_name,
                'ip' => $nasIp,
                'providedSig' => $providedSig,
            ]);
            return response()->json(['error' => 'Invalid NAS signature'], 403);
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
            if ($c !== '') {
                // Strip port if present
                $c = explode(':', $c)[0];
                if (filter_var($c, FILTER_VALIDATE_IP)) {
                    return $c;
                }
            }
        }
        return null;
    }

    private function findNas(?string $ip, ?string $name): ?RadiusNas
    {
        try {
            if ($ip) {
                $cleanIp = trim(explode(':', $ip)[0]);
                $nas = RadiusNas::where('nas_ip_address', $cleanIp)
                    ->orWhere('nas_ip_address', 'like', $cleanIp . '%')
                    ->first();
                if ($nas) {
                    return $nas;
                }
            }
            if ($name) {
                $nas = RadiusNas::where('nas_name', $name)->first();
                if ($nas) {
                    return $nas;
                }
            }
            // Fallback 1: Search Router model by IP
            if ($ip) {
                $cleanIp = trim(explode(':', $ip)[0]);
                $router = Router::where('ip_address', $cleanIp)
                    ->orWhere('ip_address', 'like', $cleanIp . '%')
                    ->first();
                if ($router) {
                    return RadiusNas::firstOrCreate(
                        ['nas_ip_address' => $cleanIp],
                        [
                            'uuid' => Str::uuid(),
                            'nas_name' => $router->name,
                            'nas_secret' => $router->radius_secret,
                            'nas_type' => 'mikrotik',
                            'status' => 'active',
                            'created_by' => 1,
                            'updated_by' => 1
                        ]
                    );
                }
            }
            // Fallback 2: Default to first active NAS or Router
            $firstNas = RadiusNas::first();
            if ($firstNas) {
                return $firstNas;
            }
            $firstRouter = Router::first();
            if ($firstRouter) {
                return RadiusNas::firstOrCreate(
                    ['nas_ip_address' => $firstRouter->ip_address ?: '0.0.0.0'],
                    [
                        'uuid' => Str::uuid(),
                        'nas_name' => $firstRouter->name,
                        'nas_secret' => $firstRouter->radius_secret,
                        'nas_type' => 'mikrotik',
                        'status' => 'active',
                        'created_by' => 1,
                        'updated_by' => 1
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Radius findNas exception', ['err' => $e->getMessage()]);
        }
        return null;
    }
}
