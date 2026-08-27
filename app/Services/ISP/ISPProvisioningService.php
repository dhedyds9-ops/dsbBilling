<?php

namespace App\Services\ISP;

use App\Integration\MikroTik\Services\RouterOSService;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Router;
use Illuminate\Support\Facades\Log;
use Throwable;

class ISPProvisioningService
{
    public function __construct(
        private RouterOSService $routerOSService
    ) {}

    public function suspendCustomerService(CustomerService $customerService): array
    {
        $result = [
            'success' => false,
            'disabled' => 0,
            'kicked' => 0,
            'routers_checked' => 0,
            'errors' => [],
            'service_type' => null,
            'usernames' => [],
        ];

        try {
            $pppoeUser = $customerService->pppoeUser()->first();
            $hotspotUser = $customerService->hotspotUser()->first();

            $targets = [];

            if ($pppoeUser && !empty($pppoeUser->username)) {
                $targets[] = [
                    'kind' => 'pppoe',
                    'username' => $pppoeUser->username,
                ];
                $result['service_type'] = 'pppoe';
                $result['usernames'][] = $pppoeUser->username;
            }

            if ($hotspotUser && !empty($hotspotUser->username)) {
                $targets[] = [
                    'kind' => 'hotspot',
                    'username' => $hotspotUser->username,
                ];
                $result['service_type'] = $result['service_type'] ?? 'hotspot';
                $result['usernames'][] = $hotspotUser->username;
            }

            if (empty($targets)) {
                $csUsername = $customerService->username;
                if (!empty($csUsername)) {
                    $serviceType = $customerService->service?->type;
                    $kind = ($serviceType && str_contains(strtolower($serviceType), 'hotspot')) ? 'hotspot' : 'pppoe';
                    $targets[] = [
                        'kind' => $kind,
                        'username' => $csUsername,
                    ];
                    $result['service_type'] = $kind;
                    $result['usernames'][] = $csUsername;
                }
            }

            if (empty($targets)) {
                $result['errors'][] = 'Tidak ada username PPPoE / Hotspot yang ditemukan untuk CustomerService #' . $customerService->id;
                return $result;
            }

            $routers = Router::active()->get();
            if ($routers->isEmpty()) {
                $result['errors'][] = 'Tidak ada Router MikroTik aktif.';
                return $result;
            }

            foreach ($routers as $router) {
                try {
                    $driver = $this->routerOSService->getDriver($router);
                    if (!$driver->connect()) {
                        continue;
                    }
                    $result['routers_checked']++;
                } catch (Throwable $e) {
                    Log::warning('ISPProvisioning skip router connect gagal', [
                        'router_id' => $router->id,
                        'cs_id' => $customerService->id,
                        'err' => $e->getMessage(),
                    ]);
                    continue;
                }

                try {
                    foreach ($targets as $t) {
                        $username = $t['username'];
                        try {
                            if ($t['kind'] === 'pppoe') {
                                $disabled = $driver->disablePppSecret($username);
                                if ($disabled) $result['disabled']++;
                                $kicked = $driver->disconnectPppoeUser($username);
                                if ($kicked) $result['kicked']++;
                            } else {
                                $disabled = $driver->disableHotspotUser($username);
                                if ($disabled) $result['disabled']++;
                                $kicked = $driver->disconnectHotspotUser($username);
                                if ($kicked) $result['kicked']++;
                            }
                        } catch (Throwable $e) {
                            $result['errors'][] = sprintf('Router #%d (%s/%s): %s', $router->id, $t['kind'], $username, $e->getMessage());
                        }
                    }
                } catch (Throwable $e) {
                    $result['errors'][] = sprintf('Router #%d loop error: %s', $router->id, $e->getMessage());
                }

                try { $driver->disconnect(); } catch (Throwable $e) {}
            }

            $result['success'] = ($result['disabled'] > 0 || $result['kicked'] > 0) || count($result['errors']) === 0;

            if ($result['success']) {
                try {
                    $customerService->timestamps = false;
                    $customerService->status = 'suspended';
                    $customerService->suspended_at = $customerService->suspended_at ?? now();
                    $customerService->saveQuietly();
                } catch (Throwable $e) {
                    $result['errors'][] = 'Update DB status CustomerService gagal: ' . $e->getMessage();
                }
            }

            Log::info('ISPProvisioning suspendCustomerService selesai', [
                'cs_id' => $customerService->id,
                'result' => $result,
            ]);

            return $result;
        } catch (Throwable $e) {
            $result['errors'][] = 'FATAL: ' . $e->getMessage();
            Log::error('ISPProvisioning suspendCustomerService FATAL', [
                'cs_id' => $customerService->id,
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $result;
        }
    }

    public function reactivateCustomerService(CustomerService $customerService): array
    {
        $result = [
            'success' => false,
            'enabled' => 0,
            'kicked' => 0,
            'routers_checked' => 0,
            'errors' => [],
            'service_type' => null,
            'usernames' => [],
        ];

        try {
            $pppoeUser = $customerService->pppoeUser()->first();
            $hotspotUser = $customerService->hotspotUser()->first();

            $targets = [];

            if ($pppoeUser && !empty($pppoeUser->username)) {
                $targets[] = [
                    'kind' => 'pppoe',
                    'username' => $pppoeUser->username,
                ];
                $result['service_type'] = 'pppoe';
                $result['usernames'][] = $pppoeUser->username;
            }

            if ($hotspotUser && !empty($hotspotUser->username)) {
                $targets[] = [
                    'kind' => 'hotspot',
                    'username' => $hotspotUser->username,
                ];
                $result['service_type'] = $result['service_type'] ?? 'hotspot';
                $result['usernames'][] = $hotspotUser->username;
            }

            if (empty($targets)) {
                $csUsername = $customerService->username;
                if (!empty($csUsername)) {
                    $serviceType = $customerService->service?->type;
                    $kind = ($serviceType && str_contains(strtolower($serviceType), 'hotspot')) ? 'hotspot' : 'pppoe';
                    $targets[] = [
                        'kind' => $kind,
                        'username' => $csUsername,
                    ];
                    $result['service_type'] = $kind;
                    $result['usernames'][] = $csUsername;
                }
            }

            if (empty($targets)) {
                $result['errors'][] = 'Tidak ada username PPPoE / Hotspot yang ditemukan untuk CustomerService #' . $customerService->id;
                return $result;
            }

            $routers = Router::active()->get();
            if ($routers->isEmpty()) {
                $result['errors'][] = 'Tidak ada Router MikroTik aktif.';
                return $result;
            }

            foreach ($routers as $router) {
                try {
                    $driver = $this->routerOSService->getDriver($router);
                    if (!$driver->connect()) {
                        continue;
                    }
                    $result['routers_checked']++;
                } catch (Throwable $e) {
                    Log::warning('ISPProvisioning skip router connect gagal (reactivate)', [
                        'router_id' => $router->id,
                        'cs_id' => $customerService->id,
                        'err' => $e->getMessage(),
                    ]);
                    continue;
                }

                try {
                    foreach ($targets as $t) {
                        $username = $t['username'];
                        try {
                            if ($t['kind'] === 'pppoe') {
                                $enabled = $driver->enablePppSecret($username);
                                if ($enabled) $result['enabled']++;
                                $kicked = $driver->disconnectPppoeUser($username);
                                if ($kicked) $result['kicked']++;
                            } else {
                                $enabled = $driver->enableHotspotUser($username);
                                if ($enabled) $result['enabled']++;
                                $kicked = $driver->disconnectHotspotUser($username);
                                if ($kicked) $result['kicked']++;
                            }
                        } catch (Throwable $e) {
                            $result['errors'][] = sprintf('Router #%d (%s/%s): %s', $router->id, $t['kind'], $username, $e->getMessage());
                        }
                    }
                } catch (Throwable $e) {
                    $result['errors'][] = sprintf('Router #%d loop error: %s', $router->id, $e->getMessage());
                }

                try { $driver->disconnect(); } catch (Throwable $e) {}
            }

            $result['success'] = ($result['enabled'] > 0 || count($result['errors']) === 0);

            if ($result['success']) {
                try {
                    $customerService->timestamps = false;
                    if ($customerService->status === 'suspended' || $customerService->status === 'pending') {
                        $customerService->status = 'active';
                    }
                    $customerService->suspended_at = null;
                    if (empty($customerService->activated_at)) {
                        $customerService->activated_at = now();
                    }
                    $customerService->saveQuietly();
                } catch (Throwable $e) {
                    $result['errors'][] = 'Update DB status CustomerService gagal: ' . $e->getMessage();
                }
            }

            Log::info('ISPProvisioning reactivateCustomerService selesai', [
                'cs_id' => $customerService->id,
                'result' => $result,
            ]);

            return $result;
        } catch (Throwable $e) {
            $result['errors'][] = 'FATAL: ' . $e->getMessage();
            Log::error('ISPProvisioning reactivateCustomerService FATAL', [
                'cs_id' => $customerService->id,
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $result;
        }
    }
}
