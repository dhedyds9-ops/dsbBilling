<?php

namespace App\Http\Controllers\Api\Provisioning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Provisioning\RouterProvisioningService;
use Illuminate\Support\Facades\Log;

class RouterBootstrapController extends Controller
{
    public function bootstrap(Request $request, RouterProvisioningService $service)
    {
        $token = $request->input('token');
        $uplink = $request->input('uplink', 'ether1');

        if (!$token) {
            return response('Missing token', 400);
        }

        try {
            Log::info('ROUTER_PROVISIONING_STARTED', ['ip' => $request->ip()]);
            
            $script = $service->consumeTokenAndGenerateScript($token, $uplink);
            
            Log::info('ROUTER_CONFIGURATION_APPLIED', ['ip' => $request->ip()]);
            
            return response($script, 200)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            Log::warning('ROUTER_PROVISIONING_FAILED', ['ip' => $request->ip(), 'msg' => $e->getMessage()]);
            return response($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
