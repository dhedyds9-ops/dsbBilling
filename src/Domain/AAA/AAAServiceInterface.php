<?php

namespace Src\Domain\AAA;

interface AAAServiceInterface
{
    public function authenticate(string $username, string $password): bool;
    public function authorize(string $username, string $service): bool;
    public function account(string $username, array $data): bool;
}
