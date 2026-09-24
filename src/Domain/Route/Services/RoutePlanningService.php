<?php

namespace Src\Domain\Route\Services;

use Src\Domain\Route\FiberRoute;
use Src\Domain\Route\FiberSegment;
use Src\Domain\Route\RouteCalculation;
use Src\Domain\Route\Repositories\FiberRouteRepositoryInterface;
use Src\Domain\Route\Repositories\FiberSegmentRepositoryInterface;
use Src\Domain\Route\Repositories\RouteCalculationRepositoryInterface;
use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\Route\Enums\RouteStatus;
use Src\Domain\Route\Enums\RouteType;
use Src\Domain\Route\Events\RouteCalculated;
use Src\Domain\Route\ValueObjects\PathHop;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class RoutePlanningService
{
    private array $graph = [];
    private array $nodeMetadata = [];

    public function __construct(
        private FiberRouteRepositoryInterface $routeRepository,
        private FiberSegmentRepositoryInterface $segmentRepository,
        private RouteCalculationRepositoryInterface $calculationRepository,
        private ShortestPathService $shortestPathService,
        private AlternativePathService $alternativePathService
    ) {}

    public function calculateRoute(
        string $sourceNodeId,
        string $sourceNodeType,
        string $targetNodeId,
        string $targetNodeType,
        RouteType $routeType = RouteType::SHORTEST,
        AlgorithmType $algorithm = AlgorithmType::DIJKSTRA,
        array $options = []
    ): FiberRoute {
        $this->buildGraph();

        $route = FiberRoute::create(
            Uuid::generate(),
            $sourceNodeId,
            $sourceNodeType,
            $targetNodeId,
            $targetNodeType,
            $routeType,
            $algorithm
        );

        $calculation = RouteCalculation::create(
            Uuid::generate(),
            $sourceNodeId,
            $targetNodeId,
            $algorithm
        );

        try {
            $result = $this->shortestPathService->findPath(
                $sourceNodeId,
                $targetNodeId,
                $algorithm,
                $this->graph,
                $options
            );

            if ($result['found']) {
                $hops = $this->buildHops($result['path'], $result['cost']);
                $route->setRoute($hops, $result['cost'], $result['calculation_time']);
                $calculation->complete($result['path'], $result['cost']);

                $this->routeRepository->save($route);
                $this->calculationRepository->save($calculation);
            } else {
                $route->markFailed("No path found from {$sourceNodeId} to {$targetNodeId}");
                $calculation->fail("No path found");
                $this->calculationRepository->save($calculation);
            }
        } catch (\Exception $e) {
            $route->markFailed($e->getMessage());
            $calculation->fail($e->getMessage());
            $this->calculationRepository->save($calculation);
        }

        $this->routeRepository->save($route);
        return $route;
    }

    public function calculateAlternativeRoutes(
        string $sourceNodeId,
        string $sourceNodeType,
        string $targetNodeId,
        string $targetNodeType,
        int $count = 3
    ): array {
        $this->buildGraph();

        $alternatives = $this->alternativePathService->findAlternatives(
            $sourceNodeId,
            $targetNodeId,
            $count,
            $this->graph
        );

        $routes = [];
        foreach ($alternatives as $index => $alt) {
            $route = FiberRoute::create(
                Uuid::generate(),
                $sourceNodeId,
                $sourceNodeType,
                $targetNodeId,
                $targetNodeType,
                RouteType::ALTERNATIVE,
                AlgorithmType::DIJKSTRA
            );

            $hops = $this->buildHops($alt['path'], $alt['cost']);
            $route->setRoute($hops, $alt['cost'], 0);
            $route->addMetadata('alternative_rank', $index + 1);
            $route->addMetadata('difference_from_shortest', $alt['difference']);

            $this->routeRepository->save($route);
            $routes[] = $route;
        }

        return $routes;
    }

    public function optimizeRoute(Uuid $routeId): FiberRoute
    {
        $route = $this->routeRepository->findById($routeId);
        if (!$route) {
            throw new \InvalidArgumentException("Route not found");
        }

        $optimizedPath = $this->shortestPathService->optimizePath(
            $route->hops,
            $this->graph
        );

        $previousCost = $route->getTotalCost();
        $newCost = $this->calculateCost($optimizedPath);
        $hopReduction = count($route->hops) - count($optimizedPath);

        $route->optimize(
            ['hop_reduction' => $hopReduction],
            $previousCost,
            $newCost->totalCost
        );

        $this->routeRepository->save($route);
        return $route;
    }

    private function buildGraph(): void
    {
        $segments = $this->segmentRepository->findActiveSegments();
        $this->graph = [];

        foreach ($segments as $segment) {
            if (!$segment->canTraverse()) {
                continue;
            }

            $startId = $segment->startNodeId;
            $endId = $segment->endNodeId;

            $this->graph[$startId][$endId] = [
                'segment_id' => $segment->id->value,
                'distance' => $segment->distance,
                'fiber_length' => $segment->fiberLength,
                'travel_time' => $segment->travelTime,
                'cost' => $segment->getCost(),
                'latency' => $segment->getLatency(),
                'capacity_usage' => $segment->capacityUsage,
            ];

            $this->graph[$endId][$startId] = [
                'segment_id' => $segment->id->value,
                'distance' => $segment->distance,
                'fiber_length' => $segment->fiberLength,
                'travel_time' => $segment->travelTime,
                'cost' => $segment->getCost(),
                'latency' => $segment->getLatency(),
                'capacity_usage' => $segment->capacityUsage,
            ];

            $this->nodeMetadata[$startId] = [
                'type' => $segment->startNodeType,
                'coordinate' => $segment->startCoordinate,
            ];
            $this->nodeMetadata[$endId] = [
                'type' => $segment->endNodeType,
                'coordinate' => $segment->endCoordinate,
            ];
        }
    }

    private function buildHops(array $path, RouteCost $cost): array
    {
        $hops = [];
        $cumulativeDistance = 0;
        $cumulativeFiberLength = 0;

        foreach ($path as $index => $nodeId) {
            $metadata = $this->nodeMetadata[$nodeId] ?? ['type' => 'unknown', 'coordinate' => null];
            $prevNodeId = $index > 0 ? $path[$index - 1] : null;

            $distanceFromPrevious = 0;
            $fiberLength = 0;

            if ($prevNodeId && isset($this->graph[$prevNodeId][$nodeId])) {
                $edgeData = $this->graph[$prevNodeId][$nodeId];
                $distanceFromPrevious = $edgeData['distance'];
                $fiberLength = $edgeData['fiber_length'];
            }

            $cumulativeDistance += $distanceFromPrevious;
            $cumulativeFiberLength += $fiberLength;

            $hops[] = new PathHop(
                sequence: $index + 1,
                nodeId: $nodeId,
                nodeType: $metadata['type'],
                nodeName: $nodeId,
                distanceFromPrevious: $distanceFromPrevious,
                fiberLength: $fiberLength,
                cumulativeDistance: $cumulativeDistance,
                cumulativeFiberLength: $cumulativeFiberLength,
                arrivalTime: $cumulativeDistance / 60,
                coordinate: $metadata['coordinate']
            );
        }

        return $hops;
    }

    private function calculateCost(array $path): RouteCost
    {
        $totalDistance = 0;
        $totalFiberLength = 0;
        $totalTime = 0;
        $totalLatency = 0;
        $hopCount = count($path) - 1;

        for ($i = 1; $i < count($path); $i++) {
            $prevNode = $path[$i - 1];
            $currNode = $path[$i];

            if (isset($this->graph[$prevNode][$currNode])) {
                $edgeData = $this->graph[$prevNode][$currNode];
                $totalDistance += $edgeData['distance'];
                $totalFiberLength += $edgeData['fiber_length'];
                $totalTime += $edgeData['travel_time'];
                $totalLatency += $edgeData['latency'];
            }
        }

        return new RouteCost(
            distance: $totalDistance,
            time: $totalTime,
            fiberLength: $totalFiberLength,
            hopCount: max(0, $hopCount),
            latency: $totalLatency,
            bandwidth: 10000,
            totalCost: $totalDistance + ($totalTime * 10) + ($hopCount * 50)
        );
    }

    public function getRouteStatistics(): array
    {
        $activeRoutes = $this->routeRepository->findActiveRoutes();
        $calculatedRoutes = $this->routeRepository->findByStatus(RouteStatus::CALCULATED);
        
        $totalDistance = 0;
        $totalHops = 0;

        foreach ($activeRoutes as $route) {
            $totalDistance += $route->getTotalDistance();
            $totalHops += $route->getHopCount();
        }

        return [
            'active_routes' => count($activeRoutes),
            'calculated_routes' => count($calculatedRoutes),
            'total_distance' => round($totalDistance, 2),
            'total_hops' => $totalHops,
            'average_hops' => count($activeRoutes) > 0 ? round($totalHops / count($activeRoutes), 2) : 0,
        ];
    }
}
