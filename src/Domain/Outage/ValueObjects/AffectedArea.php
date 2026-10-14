<?php

namespace Src\Domain\Outage\ValueObjects;

use Src\Domain\Outage\Enums\NodeType;

final class AffectedArea
{
    public function __construct(
        public readonly NodeType $originType,
        public readonly string $originId,
        public readonly array $affectedNodes = [],
        public readonly array $affectedOltIds = [],
        public readonly array $affectedOdpIds = [],
        public readonly array $affectedOnuIds = [],
        public readonly array $affectedCustomerIds = [],
        public readonly int $totalNodes,
        public readonly int $totalCustomers,
        public readonly ?string $geographicArea = null
    ) {}

    public function getImpactRadius(): int
    {
        if ($this->totalCustomers >= 500) {
            return 1;
        } elseif ($this->totalCustomers >= 100) {
            return 2;
        } elseif ($this->totalCustomers >= 50) {
            return 3;
        }
        return 4;
    }

    public function hasAffectedOLT(): bool
    {
        return !empty($this->affectedOltIds);
    }

    public function hasAffectedODP(): bool
    {
        return !empty($this->affectedOdpIds);
    }

    public function hasAffectedONU(): bool
    {
        return !empty($this->affectedOnuIds);
    }

    public function getAffectedNodeCountByType(NodeType $type): int
    {
        return count(array_filter($this->affectedNodes, fn($n) => $n['type'] === $type->value));
    }

    public function toArray(): array
    {
        return [
            'origin_type' => $this->originType->value,
            'origin_id' => $this->originId,
            'affected_nodes' => $this->affectedNodes,
            'affected_olt_ids' => $this->affectedOltIds,
            'affected_odp_ids' => $this->affectedOdpIds,
            'affected_onu_ids' => $this->affectedOnuIds,
            'affected_customer_ids' => $this->affectedCustomerIds,
            'total_nodes' => $this->totalNodes,
            'total_customers' => $this->totalCustomers,
            'geographic_area' => $this->geographicArea,
        ];
    }
}
