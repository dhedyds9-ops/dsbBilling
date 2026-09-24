<?php

namespace App\Services\Provisioning;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ProvisioningAuditService
{
    /**
     * Log a high-risk provisioning action.
     */
    public static function log(string $action, string $resourceType, int $resourceId, array $oldState = [], array $newState = [], string $status = 'UNKNOWN', ?string $reason = null, ?User $actor = null)
    {
        $actor = $actor ?? auth()->user();
        
        // MASK SECRETS BEFORE LOGGING
        $newState = self::maskSecrets($newState);
        $oldState = self::maskSecrets($oldState);

        DB::table('audit_logs')->insert([
            'auditable_type' => $resourceType,
            'auditable_id' => $resourceId,
            'event' => $action,
            'user_id' => $actor ? $actor->id : null,
            'old_values' => json_encode($oldState),
            'new_values' => json_encode(array_merge($newState, ['status' => $status, 'reason' => $reason])),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'notes' => 'Actor Role: ' . ($actor ? ($actor->roles->first()->name ?? 'N/A') . ' / Job Function: ' . $actor->job_function : 'SYSTEM'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private static function maskSecrets(array $data): array
    {
        $secretKeys = ['password', 'secret', 'token', 'telnet', 'superadmin', 'key', 'credential'];
        
        array_walk_recursive($data, function (&$value, $key) use ($secretKeys) {
            foreach ($secretKeys as $secretKey) {
                if (stripos($key, $secretKey) !== false) {
                    $value = '********';
                    break;
                }
            }
        });

        return $data;
    }
}
