<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
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
            // Prevent script timing out during high throughput / slow client
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

                // If connection aborted, break early
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
     * Get client IP and local server info.
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

        return response()->json([
            'client_ip' => $request->ip(),
            'server_name' => $companyName . ' Edge Node',
            'server_location' => $companyCity,
            'timestamp' => microtime(true),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
