<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Router;
use App\Models\Provisioning\RouterProvisioningSession;
use Illuminate\Support\Str;

class RouterProvisioningService
{
    private RouterProvisioningScriptGenerator $scriptGenerator;

    public function __construct(RouterProvisioningScriptGenerator $scriptGenerator)
    {
        $this->scriptGenerator = $scriptGenerator;
    }

    public function generateSession(Router $router, int $userId): RouterProvisioningSession
    {
        // Revoke all pending sessions for this router
        RouterProvisioningSession::where('router_id', $router->id)
            ->whereIn('status', ['PENDING', 'GENERATED'])
            ->update(['status' => 'REVOKED', 'revoked_at' => now()]);

        // Cryptographically secure token
        $rawToken = Str::random(40);
        $tokenHash = hash('sha256', $rawToken);

        $session = RouterProvisioningSession::create([
            'uuid' => (string) Str::uuid(),
            'router_id' => $router->id,
            'token_hash' => $tokenHash,
            'status' => 'GENERATED',
            'expires_at' => now()->addMinutes(15),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Attach the raw token dynamically (not saved to DB) so the UI can display it ONCE
        $session->raw_token = $rawToken;

        return $session;
    }

    public function consumeTokenAndGenerateScript(string $rawToken, string $uplinkInterface = 'ether1'): string
    {
        $tokenHash = hash('sha256', $rawToken);

        $session = RouterProvisioningSession::where('token_hash', $tokenHash)->first();

        if (!$session) {
            abort(404, 'PROVISIONING_TOKEN_INVALID');
        }

        if (!$session->isValid()) {
            abort(403, 'PROVISIONING_TOKEN_EXPIRED_OR_USED');
        }

        $session->update([
            'status' => 'BOOTSTRAPPED',
            'used_at' => now(),
        ]);

        $router = $session->router;
        
        return $this->scriptGenerator->generateScript($router, $uplinkInterface);
    }
}
