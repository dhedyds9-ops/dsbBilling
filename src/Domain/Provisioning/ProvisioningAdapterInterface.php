<?php

namespace Src\Domain\Provisioning;

use Src\Domain\Customer\Service\CustomerService;

interface ProvisioningAdapterInterface
{
    public function provision(CustomerService $service): bool;
    public function deprovision(CustomerService $service): bool;
    public function reconnect(CustomerService $service): bool;
    public function disconnect(CustomerService $service): bool;
}
