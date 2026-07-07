<?php

namespace Src\Domain\Outage;

use DateTimeImmutable;
use Src\Domain\Outage\Enums\NodeType;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\SharedKernel\Aggregates\AggregateRoot;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class AffectedNode extends AggregateRoot
{
    public function __construct(
        public readonly Uuid $id,
        public readonly Uuid $outageId,
        public readonly NodeType $nodeType,
        public readonly string $nodeId,
        public readonly string $nodeName,
        public ImpactLevel $impactLevel,
        public int $childNodeCount = 0,
        public int $affectedCustomerCount = 0,
        public ?DateTimeImmutable $affectedSince = null,
        public ?DateTimeImmutable $recoveredAt = null,
        public bool $isRootCause = false,
        public array $parentNodes = [],
        public array $childNodes = [],
        public array $metadata = []
    ) {}

    public static function create(
        Uuid $id,
        Uuid $outageId,
        NodeType $nodeType,
        string $nodeId,
        string $nodeName,
        ImpactLevel $impactLevel
    ): self {
        return new self(
            $id,
            $outageId,
            $nodeType,
            $nodeId,
            $nodeName,
            $impactLevel,
            0,
            0,
            new DateTimeImmutable()
        );
    }

    public function markAsRootCause(): void
    {
        $this->isRootCause = true;
        $this->metadata['root_cause_determined_at'] = new DateTimeImmutable();
    }

    public function addChildNode(string $nodeId, NodeType $nodeType): void
    {
        $this->childNodes[] = [
            'node_id' => $nodeId,
            'node_type' => $nodeType->value
        ];
        $this->childNodeCount = count($this->childNodes);
    }

    public function addParentNode(string $nodeId, NodeType $nodeType): void
    {
        $this->parentNodes[] = [
            'node_id' => $nodeId,
            'node_type' => $nodeType->value
        ];
    }

    public function recover(): void
    {
        $this->recoveredAt = new DateTimeImmutable();
        $this->impactLevel = ImpactLevel::NONE;
    }

    public function getDowntimeMinutes(): ?int
    {
        if ($this->recoveredAt === null) {
            return null;
        }
        
        return (int) (($this->recoveredAt->getTimestamp() - $this->affectedSince->getTimestamp()) / 60);
    }

    public function isRecovered(): bool
    {
        return $this->recoveredAt !== null;
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
