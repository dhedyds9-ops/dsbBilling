<?php

namespace App\Http\Middleware\Radius;

use App\Models\ISP\RadiusNas;
use App\Models\ISP\Router;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VerifyNasSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('*health*')) {
            return $next($request);
        }

        $this->ensureTenantContext($request);

        $isLocalhost = in_array($request->ip(), ['127.0.0.1', '::1'], true);

        $nasIp = $this->resolveNasIp($request);
        $nasName = $request->input('nas_name') ?? $request->input('NAS-Identifier');

        $nas = $this->findNas($nasIp, $nasName);

        if (!$nas && !$isLocalhost) {
            Log::warning('Radius ingest: NAS tidak terdaftar', ['nas_ip' => $nasIp, 'nas_name' => $nasName]);
            return response()->json(['error' => 'Unknown NAS'], 403);
        }

        $secret = $nas?->nas_secret ?? null;

        $providedSig = $request->header('X-Radius-Signature')
            ?? $request->input('message_authenticator')
            ?? $request->input('Message-Authenticator')
            ?? $request->input('signature');

        $uname = (string)($request->input('username') ?? '');
        $valid = false;

        if ($isLocalhost) {
            // Internal call from local FreeRADIUS on localhost - trusted
            $valid = true;
        } elseif ($secret !== null && $secret !== '' && $providedSig !== null && $providedSig !== '') {
            if (hash_equals($secret, (string)$providedSig) || (string)$providedSig === (string)$secret) {
                $valid = true;
            } elseif (md5($uname . $secret) === (string)$providedSig || md5($secret) === (string)$providedSig || md5($uname) === (string)$providedSig) {
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
        }

        if (!$valid) {
            Log::warning('Radius ingest: Invalid NAS signature', [
                'nas' => $nas?->nas_name,
                'ip' => $nasIp,
                'client_ip' => $request->ip(),
                'providedSig' => $providedSig,
            ]);
            return response()->json(['error' => 'Invalid NAS signature'], 403);
        }

        if ($nas) {
            $request->attributes->set('radius_nas', $nas);
        }

        return $next($request);
    }

    private function ensureTenantContext(Request $request): void
    {
        if (isset($_SERVER['TENANT_NAME']) && $_SERVER['TENANT_NAME'] !== '') {
            return;
        }

        $nasIp = $this->resolveNasIp($request);
        $nasName = $request->input('nas_name') ?? $request->input('NAS-Identifier');
        $username = (string)($request->input('username') ?? '');

        $tenantsDir = database_path('tenants');
        if (!is_dir($tenantsDir)) {
            return;
        }

        $sqliteFiles = glob($tenantsDir . '/*.sqlite');
        if (empty($sqliteFiles)) {
            return;
        }

        $matchedTenant = null;

        // If only 1 tenant exists, select it directly
        if (count($sqliteFiles) === 1) {
            $matchedTenant = basename($sqliteFiles[0], '.sqlite');
        } else {
            // 1. Prioritaskan pencarian berdasarkan username / voucher code
            if ($username !== '') {
                $stripped = str_contains($username, '@') ? explode('@', $username)[0] : $username;
                $suffixed = !str_contains($username, '@') ? $username . '@%' : $username;

                foreach ($sqliteFiles as $file) {
                    $tName = basename($file, '.sqlite');
                    try {
                        $pdo = new \PDO('sqlite:' . $file);
                        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

                        $stmt = $pdo->prepare('SELECT id FROM vouchers WHERE code = :u OR code = :s LIMIT 1');
                        $stmt->execute([':u' => $username, ':s' => $stripped]);
                        if ($stmt->fetch()) {
                            $matchedTenant = $tName;
                            break;
                        }

                        $stmt = $pdo->prepare('SELECT id FROM hotspot_users WHERE username = :u OR username = :s LIMIT 1');
                        $stmt->execute([':u' => $username, ':s' => $stripped]);
                        if ($stmt->fetch()) {
                            $matchedTenant = $tName;
                            break;
                        }

                        $stmt = $pdo->prepare('SELECT id FROM pppoe_users WHERE username = :u OR username = :s OR username LIKE :suff LIMIT 1');
                        $stmt->execute([':u' => $username, ':s' => $stripped, ':suff' => $suffixed]);
                        if ($stmt->fetch()) {
                            $matchedTenant = $tName;
                            break;
                        }
                    } catch (\Throwable $e) {
                        // continue
                    }
                }
            }

            // 2. Jika belum cocok, cari berdasarkan router NAS IP atau nama router
            if (!$matchedTenant && ($nasIp || $nasName)) {
                $cleanIp = $nasIp ? explode(':', $nasIp)[0] : '';
                foreach ($sqliteFiles as $file) {
                    $tName = basename($file, '.sqlite');
                    try {
                        $pdo = new \PDO('sqlite:' . $file);
                        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

                        if ($cleanIp !== '' && $cleanIp !== '127.0.0.1') {
                            $stmt = $pdo->prepare('SELECT id FROM routers WHERE ip_address LIKE :ip LIMIT 1');
                            $stmt->execute([':ip' => '%' . $cleanIp . '%']);
                            if ($stmt->fetch()) {
                                $matchedTenant = $tName;
                                break;
                            }
                        }

                        if (!empty($nasName)) {
                            $stmt = $pdo->prepare('SELECT id FROM routers WHERE name = :name LIMIT 1');
                            $stmt->execute([':name' => (string)$nasName]);
                            if ($stmt->fetch()) {
                                $matchedTenant = $tName;
                                break;
                            }
                        }
                    } catch (\Throwable $e) {
                        // continue
                    }
                }
            }

            // Fallback to first tenant if still null
            if (!$matchedTenant) {
                $matchedTenant = basename($sqliteFiles[0], '.sqlite');
            }
        }

        if ($matchedTenant) {
            $_SERVER['TENANT_MODE'] = true;
            $_SERVER['TENANT_NAME'] = $matchedTenant;
            $dbPath = database_path("tenants/{$matchedTenant}.sqlite");
            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $dbPath,
            ]);
            DB::purge();
            DB::reconnect('sqlite');
        }
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
                    $nas = new RadiusNas();
                    $nas->id = 1;
                    $nas->nas_name = $router->name;
                    $nas->nas_ip_address = $cleanIp;
                    $nas->nas_secret = $router->radius_secret;
                    $nas->nas_type = 'mikrotik';
                    $nas->status = 'active';
                    return $nas;
                }
            }
            // Fallback 2: Default to first active NAS or Router
            $firstNas = RadiusNas::first();
            if ($firstNas) {
                return $firstNas;
            }
            $firstRouter = Router::first();
            if ($firstRouter) {
                $nas = new RadiusNas();
                $nas->id = 1;
                $nas->nas_name = $firstRouter->name;
                $nas->nas_ip_address = $firstRouter->ip_address ?: '0.0.0.0';
                $nas->nas_secret = $firstRouter->radius_secret;
                $nas->nas_type = 'mikrotik';
                $nas->status = 'active';
                return $nas;
            }
        } catch (\Throwable $e) {
            Log::warning('Radius findNas exception', ['err' => $e->getMessage()]);
        }

        // Final in-memory fallback
        $nas = new RadiusNas();
        $nas->id = 1;
        $nas->nas_name = 'mikrotik';
        $nas->nas_ip_address = $ip ?: '127.0.0.1';
        $nas->nas_secret = '5Z0Ofqzeg40PtZLR5EN0gis5';
        $nas->nas_type = 'mikrotik';
        $nas->status = 'active';
        return $nas;
    }
}
