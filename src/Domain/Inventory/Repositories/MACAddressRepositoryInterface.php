<?php

namespace Src\Domain\Inventory\Repositories;

use Src\Domain\Inventory\MACAddressRecord;
use Src\Domain\SharedKernel\Repositories\RepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface MACAddressRepositoryInterface extends RepositoryInterface
{
    public function findById(Uuid $id): ?MACAddressRecord;
    public function findByMACAddress(string $macAddress): ?MACAddressRecord;
    public function findByAsset(Uuid $assetId): array;
    public function findActive(): array;
    public function search(string $query): array;
    public function save(MACAddressRecord $mac): void;
    public function delete(MACAddressRecord $mac): void;
}
