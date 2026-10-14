<?php

namespace Src\Domain\Provisioning\Radius;

interface RadiusProvisioningAdapterInterface
{
    public function addUser(string $username, string $password, array $attributes = []): bool;
    public function removeUser(string $username): bool;
    public function updateUser(string $username, array $attributes = []): bool;
    public function checkUser(string $username): bool;
}
