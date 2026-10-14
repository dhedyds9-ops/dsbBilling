<?php

namespace Src\Domain\Outage\Repositories;

use Src\Domain\Outage\AffectedNode;
use Src\Domain\Outage\Enums\NodeType;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

interface AffectedNodeRepositoryInterface
{
    public function save(AffectedNode $node): void;
    
    public function findById(Uuid $id): ?AffectedNode;
    
    public function findByOutageId(Uuid $outageId): array;
    
    public function findByNodeId(string $nodeId): array;
    
    public function findByNodeType(NodeType $nodeType): array;
    
    public function findRootCauseNode(Uuid $outageId): ?AffectedNode;
    
    public function findRecoveredNodes(Uuid $outageId): array;
    
    public function findUnrecoveredNodes(Uuid $outageId): array;
    
    public function delete(Uuid $id): void;
}
