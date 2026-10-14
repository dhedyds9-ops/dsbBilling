<?php

namespace Src\Domain\Route\Services;

use Src\Domain\Route\NearestNode;
use Src\Domain\Route\Repositories\NearestNodeRepositoryInterface;
use Src\Domain\Route\Repositories\FiberSegmentRepositoryInterface;
use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\Route\ValueObjects\Coordinate;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class NearestNodeService
{
    private array $nodeCoordinates = [];
    private array $nodeSegments = [];

    public function __construct(
        private NearestNodeRepositoryInterface $nearestNodeRepository,
        private FiberSegmentRepositoryInterface $segmentRepository,
        private ShortestPathService $shortestPathService
    ) {}

    public function findNearestNode(
        string $searchNodeId,
        string $searchNodeType,
        string $targetNodeType,
        Coordinate $searchCoordinate,
        float $maxDistance = 50.0
    ): NearestNode {
        $this->loadNodeData($targetNodeType);

        $nearest = NearestNode::create(
            Uuid::generate(),
            $searchNodeId,
            $searchNodeType,
            $targetNodeType,
            $searchCoordinate
        );

        $nearest->setSearchRadius($maxDistance);

        $candidates = $this->findCandidatesInRadius($searchCoordinate, $maxDistance);

        if (empty($candidates)) {
            $nearest->addMetadata('no_candidates_found', true);
            $this->nearestNodeRepository->save($nearest);
            return $nearest;
        }

        usort($candidates, fn($a, $b) => $a['distance'] <=> $b['distance']);

        $bestCandidate = $candidates[0];
        $nearest->setFoundNode(
            $bestCandidate['node_id'],
            $bestCandidate['node_name'],
            $bestCandidate['coordinate'],
            $bestCandidate['distance'],
            $bestCandidate['distance'] / 60,
            RouteCost::fromSegment($bestCandidate['distance'], $bestCandidate['distance'] / 60)
        );

        foreach (array_slice($candidates, 0, 10) as $candidate) {
            $nearest->addNearbyNode(
                $candidate['node_id'],
                $candidate['node_name'],
                $candidate['node_type'],
                $candidate['coordinate'],
                $candidate['distance']
            );
            $nearest->incrementNodesSearched();
        }

        $this->nearestNodeRepository->save($nearest);
        return $nearest;
    }

    public function findNearestOLT(
        string $searchNodeId,
        string $searchNodeType,
        Coordinate $searchCoordinate,
        float $maxDistance = 50.0
    ): NearestNode {
        return $this->findNearestNode($searchNodeId, $searchNodeType, 'olt', $searchCoordinate, $maxDistance);
    }

    public function findNearestODP(
        string $searchNodeId,
        string $searchNodeType,
        Coordinate $searchCoordinate,
        float $maxDistance = 5.0
    ): NearestNode {
        return $this->findNearestNode($searchNodeId, $searchNodeType, 'odp', $searchCoordinate, $maxDistance);
    }

    public function findNearestPOP(
        string $searchNodeId,
        string $searchNodeType,
        Coordinate $searchCoordinate,
        float $maxDistance = 100.0
    ): NearestNode {
        return $this->findNearestNode($searchNodeId, $searchNodeType, 'pop', $searchCoordinate, $maxDistance);
    }

    private function findCandidatesInRadius(Coordinate $coordinate, float $radiusKm): array
    {
        $candidates = [];

        foreach ($this->nodeCoordinates as $nodeId => $data) {
            $nodeCoord = Coordinate::fromArray($data['coordinate']);
            $distance = $coordinate->distanceTo($nodeCoord);

            if ($distance <= $radiusKm) {
                $candidates[] = [
                    'node_id' => $nodeId,
                    'node_name' => $data['name'] ?? $nodeId,
                    'node_type' => $data['type'] ?? 'unknown',
                    'coordinate' => $nodeCoord,
                    'distance' => $distance,
                ];
            }
        }

        return $candidates;
    }

    private function loadNodeData(string $nodeType): void
    {
        $this->nodeCoordinates = [];
        
        $segments = $this->segmentRepository->findActiveSegments();

        foreach ($segments as $segment) {
            if ($segment->startNodeType === $nodeType && $segment->startCoordinate) {
                $this->nodeCoordinates[$segment->startNodeId] = [
                    'type' => $segment->startNodeType,
                    'name' => $segment->startNodeId,
                    'coordinate' => $segment->startCoordinate->toArray(),
                ];
            }

            if ($segment->endNodeType === $nodeType && $segment->endCoordinate) {
                $this->nodeCoordinates[$segment->endNodeId] = [
                    'type' => $segment->endNodeType,
                    'name' => $segment->endNodeId,
                    'coordinate' => $segment->endCoordinate->toArray(),
                ];
            }
        }
    }

    public function getNodeStatistics(): array
    {
        return [
            'total_olt' => count(array_filter($this->nodeCoordinates, fn($n) => $n['type'] === 'olt')),
            'total_odp' => count(array_filter($this->nodeCoordinates, fn($n) => $n['type'] === 'odp')),
            'total_odc' => count(array_filter($this->nodeCoordinates, fn($n) => $n['type'] === 'odc')),
            'total_pop' => count(array_filter($this->nodeCoordinates, fn($n) => $n['type'] === 'pop')),
        ];
    }
}
