<?php

namespace App\Integration\MikroTik\Services;

use App\Integration\MikroTik\Contracts\RouterOSDriverInterface;
use App\Models\ISP\Router;
use Src\Domain\Integration\RetryEngine;

class RouterOSService
{
    public function __construct(
        private RetryEngine $retryEngine
    ) {}

    public function getDriver(Router $router): RouterOSDriverInterface
    {
        $config = [
            'host' => $router->ip_address,
            'username' => $router->username,
            'password' => $router->password,
            'port' => $router->api_port ?? 8728,
            'ssl' => $router->use_ssl ?? false,
            'timeout' => $router->timeout ?? 30,
        ];

        return new \App\Integration\MikroTik\Drivers\RouterOSDriver($config, $this->retryEngine);
    }
}
