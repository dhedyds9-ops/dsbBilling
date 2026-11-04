<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpeedtestController extends Controller
{
    /**
     * Measure ping latency.
     */
    public function ping(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => microtime(true),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Stream binary data for download speed measurement.
     */
    public function download(Request $request): StreamedResponse
    {
        // Parameter size in bytes (default 10 MB, capped between 512KB and 25MB)
        $size = (int) $request->query('size', 10 * 1024 * 1024);
        $size = max(512 * 1024, min($size, 25 * 1024 * 1024));

        $chunkSize = 64 * 1024; // 64 KB per chunk
        $chunk = random_bytes($chunkSize);

        return response()->stream(function () use ($size, $chunk, $chunkSize) {
            $sent = 0;
            @set_time_limit(60);

            while ($sent < $size) {
                $toSend = min($chunkSize, $size - $sent);
                if ($toSend < $chunkSize) {
                    echo substr($chunk, 0, $toSend);
                } else {
                    echo $chunk;
                }
                $sent += $toSend;

                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                @flush();

                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => (string) $size,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Receive binary data to measure upload speed.
     */
    public function upload(Request $request)
    {
        $contentLength = (int) $request->header('Content-Length', 0);
        $bytesRead = 0;

        $stream = fopen('php://input', 'r');
        if ($stream) {
            while (!feof($stream)) {
                $buf = fread($stream, 64 * 1024);
                $bytesRead += strlen($buf);
            }
            fclose($stream);
        }

        $totalBytes = max($contentLength, $bytesRead);

        return response()->json([
            'status' => 'ok',
            'bytes_received' => $totalBytes,
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Get client IP, accurate ISP, and local server info.
     */
    public function info(Request $request)
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        $defaultTenantName = ($subdomain && !in_array($subdomain, ['localhost', '127', 'dsbilling', 'www'])) 
            ? ucfirst($subdomain) 
            : config('app.name', 'dsBilling');

        $companyName = Setting::getValue('company.name') ?: $defaultTenantName;
        $companyCity = Setting::getValue('company.city') ?: 'Indonesia';
        $partnerName = Setting::getValue('company.partner_name');

        // Resolve real client IP
        $clientIp = $request->header('CF-Connecting-IP')
            ?: ($request->header('X-Real-IP')
            ?: ($request->header('X-Forwarded-For') ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) : $request->ip()));

        // Check whether IP is private/local
        $isPrivate = filter_var(
            $clientIp, 
            FILTER_VALIDATE_IP, 
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;

        $clientIsp = null;
        $clientAsn = null;
        $clientCity = null;

        if (!$isPrivate) {
            try {
                $geo = Cache::remember('ip_isp_' . $clientIp, 86400, function () use ($clientIp) {
                    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
                    $res = @file_get_contents("http://ip-api.com/json/{$clientIp}?fields=status,isp,org,as,asname,city", false, $ctx);
                    if ($res) {
                        $json = json_decode($res, true);
                        if (($json['status'] ?? '') === 'success') {
                            return $json;
                        }
                    }
                    return null;
                });

                if ($geo) {
                    $isGeneric = function (?string $str): bool {
                        if (empty($str)) return true;
                        $lower = strtolower(trim($str));
                        return str_contains($lower, 'network information center') ||
                               str_contains($lower, 'idnic') ||
                               str_contains($lower, 'apnic') ||
                               str_contains($lower, 'iana') ||
                               str_contains($lower, 'apjii') ||
                               str_contains($lower, 'registry') ||
                               str_starts_with($lower, 'komplek') ||
                               str_starts_with($lower, 'jl.') ||
                               str_starts_with($lower, 'jalan') ||
                               str_starts_with($lower, 'rt ') ||
                               str_starts_with($lower, 'rw ');
                    };

                    $name = null;
                    if (!$isGeneric($geo['org'] ?? null)) {
                        $name = $geo['org'];
                    } elseif (!$isGeneric($geo['isp'] ?? null)) {
                        $name = $geo['isp'];
                    } elseif (!empty($geo['as'])) {
                        $asClean = preg_replace('/^AS\d+\s+/i', '', $geo['as']);
                        if (!$isGeneric($asClean)) {
                            $name = $asClean;
                        }
                    }

                    if (empty($name)) {
                        $name = $geo['org'] ?? ($geo['isp'] ?? null);
                    }

                    $clientIsp = $name;
                    $clientCity = $geo['city'] ?? null;
                    if (!empty($geo['as']) && preg_match('/^(AS\d+)/i', $geo['as'], $m)) {
                        $clientAsn = strtoupper($m[1]);
                    }
                }
            } catch (\Throwable $e) {
                // Silently fallback to browser resolution
            }
        }

        return response()->json([
            'client_ip' => $clientIp,
            'is_private_ip' => $isPrivate,
            'client_isp' => $clientIsp,
            'client_asn' => $clientAsn,
            'client_city' => $clientCity,
            'server_name' => $companyName . ' Edge Node',
            'server_location' => $companyCity,
            'server_partner' => $partnerName,
            'timestamp' => microtime(true),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
