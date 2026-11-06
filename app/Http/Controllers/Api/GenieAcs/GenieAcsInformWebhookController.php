<?php

namespace App\Http\Controllers\Api\GenieAcs;

use App\Http\Controllers\Controller;
use App\Jobs\ACS\ProcessGenieAcsInformEventJob;
use App\Models\ACS\GenieAcsWebhookEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenieAcsInformWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // 1. Validate payload size limit
        $maxBytes = config('genieacs.webhook_max_body_bytes', 65536);
        $rawContent = $request->getContent();
        if (strlen($rawContent) > $maxBytes) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payload exceeds maximum allowed size.',
            ], 413);
        }

        // 2. Validate HMAC Signature
        $secret = (string) config('genieacs.webhook_secret', '');
        if (empty($secret)) {
            Log::error('GenieACS Webhook: Secret is not configured in server environment.');
            return response()->json([
                'status' => 'error',
                'message' => 'Webhook service temporarily unavailable.',
            ], 503);
        }

        $signature = $request->header('X-GenieACS-Signature')
            ?? $request->header('X-Signature')
            ?? $request->header('X-Hub-Signature-256');

        if (!$signature) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing signature header.',
            ], 401);
        }

        if (str_starts_with($signature, 'sha256=')) {
            $signature = substr($signature, 7);
        }

        $expectedSignature = hash_hmac('sha256', $rawContent, $secret);
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('GenieACS Webhook: Signature mismatch detected.', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature.',
            ], 401);
        }

        // 3. Parse JSON Body
        $data = json_decode($rawContent, true);
        if (!is_array($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid JSON body.',
            ], 400);
        }

        // 4. Validate Timestamp & Clock Skew
        $timestampHeader = $request->header('X-GenieACS-Timestamp');
        $rawTimestamp = $timestampHeader ?? ($data['received_at'] ?? $data['timestamp'] ?? null);

        if (!$rawTimestamp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing timestamp.',
            ], 400);
        }

        try {
            $eventTime = is_numeric($rawTimestamp)
                ? (strlen((string) $rawTimestamp) > 10 ? Carbon::createFromTimestampMs($rawTimestamp) : Carbon::createFromTimestamp($rawTimestamp))
                : Carbon::parse($rawTimestamp);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid timestamp format.',
            ], 400);
        }

        $tolerance = (int) config('genieacs.webhook_tolerance_seconds', 300);
        $diff = abs(now()->diffInSeconds($eventTime, false));
        if ($diff > $tolerance) {
            Log::warning('GenieACS Webhook: Request timestamp outside acceptable tolerance window.', [
                'event_time' => $eventTime->toIso8601String(),
                'server_time' => now()->toIso8601String(),
                'diff_seconds' => $diff,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Timestamp expired or clock skew exceeded tolerance limit.',
            ], 400);
        }

        // 5. Validate Required Fields
        $deviceId = trim((string) ($data['device_id'] ?? $data['device'] ?? ''));
        if (empty($deviceId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Field device_id is required.',
            ], 422);
        }

        // Basic sanity check on device_id (prevent control characters / malformed input)
        if (preg_match('/[\x00-\x1F\x7F<>]/', $deviceId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Malformed device_id parameter.',
            ], 422);
        }

        $serialNumber = isset($data['serial_number']) ? trim((string) $data['serial_number']) : null;
        if (!empty($serialNumber) && preg_match('/[\x00-\x1F\x7F<>]/', $serialNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Malformed serial_number parameter.',
            ], 422);
        }

        // Normalize Event Codes
        $eventCodes = [];
        if (isset($data['event_codes']) && is_array($data['event_codes'])) {
            $eventCodes = array_values(array_filter(array_map('trim', $data['event_codes'])));
        } elseif (isset($data['event_codes']) && is_string($data['event_codes'])) {
            $eventCodes = array_values(array_filter(array_map('trim', explode(',', $data['event_codes']))));
        } elseif (isset($data['event']) && is_string($data['event'])) {
            $eventCodes = array_values(array_filter(array_map('trim', explode(',', $data['event']))));
        }

        // Last inform timestamp
        $lastInformAt = null;
        if (!empty($data['last_inform'])) {
            try {
                $lastInformAt = is_numeric($data['last_inform'])
                    ? (strlen((string) $data['last_inform']) > 10 ? Carbon::createFromTimestampMs($data['last_inform']) : Carbon::createFromTimestamp($data['last_inform']))
                    : Carbon::parse($data['last_inform']);
            } catch (\Throwable) {}
        }
        $lastInformAt = $lastInformAt ?? $eventTime;

        // 6. Deterministic Event ID & Idempotency
        $eventId = !empty($data['event_id'])
            ? trim((string) $data['event_id'])
            : hash('sha256', $deviceId . '|' . $lastInformAt->timestamp . '|' . implode(',', $eventCodes));

        // Deduplication check
        $existing = GenieAcsWebhookEvent::where('event_id', $eventId)->first();
        if ($existing) {
            return response()->json([
                'status' => 'duplicate',
                'event_id' => $eventId,
                'message' => 'Event already received and processed/queued.',
            ], 202);
        }

        // 7. Persist Event & Dispatch Job
        try {
            $eventRecord = DB::transaction(function () use ($eventId, $deviceId, $serialNumber, $eventCodes, $data, $lastInformAt, $eventTime) {
                return GenieAcsWebhookEvent::create([
                    'event_id' => $eventId,
                    'device_id' => $deviceId,
                    'serial_number' => $serialNumber,
                    'event_type' => $data['event_type'] ?? 'inform',
                    'event_codes' => $eventCodes,
                    'manufacturer' => $data['manufacturer'] ?? null,
                    'product_class' => $data['product_class'] ?? null,
                    'software_version' => $data['software_version'] ?? null,
                    'ip_address' => $data['ip_address'] ?? null,
                    'last_inform_at' => $lastInformAt,
                    'received_at' => $eventTime,
                    'processing_status' => GenieAcsWebhookEvent::STATUS_QUEUED,
                    'payload' => $data,
                ]);
            });

            // Dispatch job after transaction commits
            ProcessGenieAcsInformEventJob::dispatch($eventRecord->id)->afterCommit();

            return response()->json([
                'status' => 'accepted',
                'event_id' => $eventId,
                'message' => 'Event ingested and queued for processing.',
            ], 202);

        } catch (\Illuminate\Database\QueryException $e) {
            // Race condition unique constraint catch
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE')) {
                return response()->json([
                    'status' => 'duplicate',
                    'event_id' => $eventId,
                    'message' => 'Event already registered.',
                ], 202);
            }
            throw $e;
        }
    }
}
