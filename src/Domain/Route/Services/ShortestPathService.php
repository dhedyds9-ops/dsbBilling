<?php

namespace Src\Domain\Route\Services;

use Src\Domain\Route\Enums\AlgorithmType;
use Src\Domain\Route\ValueObjects\RouteCost;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ShortestPathService
{
    public function findPath(
        string $source,
        string $target,
        AlgorithmType $algorithm,
        array $graph,
        array $options = []
    ): array {
        $startTime = microtime(true);

        $result = match($algorithm) {
            AlgorithmType::DIJKSTRA => $this->dijkstra($source, $target, $graph),
            AlgorithmType::BFS => $this->bfs($source, $target, $graph),
            AlgorithmType::DFS => $this->dfs($source, $target, $graph),
            AlgorithmType::ASTAR => $this->astar($source, $target, $graph, $options['heuristic'] ?? null),
            AlgorithmType::BELLMAN_FORD => $this->bellmanFord($source, $target, $graph),
            default => throw new \InvalidArgumentException("Unsupported algorithm: {$algorithm->value}")
        };

        $result['calculation_time'] = microtime(true) - $startTime;
        return $result;
    }

    private function dijkstra(string $source, string $target, array $graph): array
    {
        $distances = [];
        $previous = [];
        $unvisited = [];
        $visited = [];

        foreach (array_keys($graph) as $node) {
            $distances[$node] = PHP_FLOAT_MAX;
            $previous[$node] = null;
            $unvisited[$node] = true;
        }

        $distances[$source] = 0;

        while (!empty($unvisited)) {
            $current = $this->getMinDistanceNode($distances, $unvisited);

            if ($current === null || $current === $target) {
                break;
            }

            unset($unvisited[$current]);
            $visited[$current] = true;

            if (!isset($graph[$current])) {
                continue;
            }

            foreach ($graph[$current] as $neighbor => $edgeData) {
                if (isset($unvisited[$neighbor])) {
                    $alt = $distances[$current] + ($edgeData['cost'] ?? $edgeData['distance']);
                    if ($alt < $distances[$neighbor]) {
                        $distances[$neighbor] = $alt;
                        $previous[$neighbor] = $current;
                    }
                }
            }
        }

        return $this->buildResult($source, $target, $previous, $distances, $graph);
    }

    private function bfs(string $source, string $target, array $graph): array
    {
        $queue = [$source];
        $visited = [$source => true];
        $previous = [$source => null];
        $distances = [$source => 0];

        while (!empty($queue)) {
            $current = array_shift($queue);

            if ($current === $target) {
                break;
            }

            if (!isset($graph[$current])) {
                continue;
            }

            foreach (array_keys($graph[$current]) as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $queue[] = $neighbor;
                    $previous[$neighbor] = $current;
                    $distances[$neighbor] = ($distances[$current] ?? 0) + 1;
                }
            }
        }

        return $this->buildResult($source, $target, $previous, $distances, $graph);
    }

    private function dfs(string $source, string $target, array $graph): array
    {
        $visited = [];
        $previous = [$source => null];
        $distances = [$source => 0];

        $this->dfsRecursive($source, $target, $graph, $visited, $previous, $distances, 0);

        return $this->buildResult($source, $target, $previous, $distances, $graph);
    }

    private function dfsRecursive(
        string $current,
        string $target,
        array $graph,
        array &$visited,
        array &$previous,
        array &$distances,
        int $currentDistance
    ): bool {
        $visited[$current] = true;

        if ($current === $target) {
            return true;
        }

        if (!isset($graph[$current])) {
            return false;
        }

        foreach (array_keys($graph[$current]) as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $edgeDistance = $graph[$current][$neighbor]['distance'] ?? 1;
                $previous[$neighbor] = $current;
                $distances[$neighbor] = $currentDistance + $edgeDistance;

                if ($this->dfsRecursive($neighbor, $target, $graph, $visited, $previous, $distances, $currentDistance + $edgeDistance)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function astar(string $source, string $target, array $graph, ?callable $heuristic = null): array
    {
        $openSet = [$source => true];
        $closedSet = [];
        $gScore = [$source => 0];
        $fScore = [$source => $heuristic ? $heuristic($source, $target) : 0];
        $previous = [$source => null];

        while (!empty($openSet)) {
            uasort($fScore, fn($a, $b) => $a <=> $b);
            $current = array_key_first($fScore);

            if ($current === $target) {
                break;
            }

            unset($openSet[$current], $fScore[$current]);
            $closedSet[$current] = true;

            if (!isset($graph[$current])) {
                continue;
            }

            foreach ($graph[$current] as $neighbor => $edgeData) {
                if (isset($closedSet[$neighbor])) {
                    continue;
                }

                $tentativeGScore = ($gScore[$current] ?? PHP_FLOAT_MAX) + ($edgeData['cost'] ?? $edgeData['distance']);
                
                if (!isset($gScore[$neighbor]) || $tentativeGScore < $gScore[$neighbor]) {
                    $previous[$neighbor] = $current;
                    $gScore[$neighbor] = $tentativeGScore;
                    $fScore[$neighbor] = $tentativeGScore + ($heuristic ? $heuristic($neighbor, $target) : 0);
                    $openSet[$neighbor] = true;
                }
            }
        }

        $distances = $gScore;
        return $this->buildResult($source, $target, $previous, $distances, $graph);
    }

    private function bellmanFord(string $source, string $target, array $graph): array
    {
        $distances = [];
        $previous = [];
        $nodes = array_keys($graph);

        foreach ($nodes as $node) {
            $distances[$node] = PHP_FLOAT_MAX;
            $previous[$node] = null;
        }
        $distances[$source] = 0;

        $edgeCount = count($nodes) - 1;
        for ($i = 0; $i < $edgeCount; $i++) {
            $updated = false;
            foreach ($graph as $from => $neighbors) {
                foreach ($neighbors as $to => $edgeData) {
                    if ($distances[$from] !== PHP_FLOAT_MAX) {
                        $newDist = $distances[$from] + ($edgeData['cost'] ?? $edgeData['distance']);
                        if ($newDist < $distances[$to]) {
                            $distances[$to] = $newDist;
                            $previous[$to] = $from;
                            $updated = true;
                        }
                    }
                }
            }
            if (!$updated) break;
        }

        return $this->buildResult($source, $target, $previous, $distances, $graph);
    }

    public function optimizePath(array $hops, array $graph): array
    {
        if (count($hops) <= 2) {
            return $hops;
        }

        $path = array_map(fn($hop) => $hop['node_id'] ?? $hop, $hops);
        
        $optimized = $path;
        $improved = true;
        $iterations = 0;
        $maxIterations = 100;

        while ($improved && $iterations < $maxIterations) {
            $improved = false;
            $iterations++;

            for ($i = 1; $i < count($optimized) - 1; $i++) {
                for ($j = $i + 1; $j < count($optimized); $j++) {
                    if ($this->canShortenPath($optimized, $i, $j, $graph)) {
                        $optimized = $this->shortenPath($optimized, $i, $j);
                        $improved = true;
                    }
                }
            }
        }

        return $optimized;
    }

    private function canShortenPath(array $path, int $i, int $j, array $graph): bool
    {
        if ($j <= $i + 1) return false;

        $currentPathCost = 0;
        for ($k = $i; $k < $j; $k++) {
            $from = $path[$k];
            $to = $path[$k + 1];
            if (isset($graph[$from][$to])) {
                $currentPathCost += $graph[$from][$to]['distance'] ?? $graph[$from][$to]['cost'] ?? 0;
            }
        }

        if (isset($graph[$path[$i]][$path[$j]])) {
            $directDistance = $graph[$path[$i]][$path[$j]]['distance'] ?? PHP_FLOAT_MAX;
            return $directDistance < $currentPathCost;
        }

        return false;
    }

    private function shortenPath(array $path, int $i, int $j): array
    {
        $newPath = [];
        for ($k = 0; $k <= $i; $k++) {
            $newPath[] = $path[$k];
        }
        for ($k = $j; $k < count($path); $k++) {
            $newPath[] = $path[$k];
        }
        return $newPath;
    }

    private function getMinDistanceNode(array $distances, array $unvisited): ?string
    {
        $min = PHP_FLOAT_MAX;
        $node = null;

        foreach (array_keys($unvisited) as $n) {
            if (isset($distances[$n]) && $distances[$n] < $min) {
                $min = $distances[$n];
                $node = $n;
            }
        }

        return $node;
    }

    private function buildResult(
        string $source,
        string $target,
        array $previous,
        array $distances,
        array $graph
    ): array {
        $path = [];
        $current = $target;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $previous[$current] ?? null;
        }

        if ($path[0] !== $source) {
            return [
                'found' => false,
                'path' => [],
                'cost' => new RouteCost(),
                'distance' => 0,
                'hop_count' => 0,
            ];
        }

        $totalDistance = 0;
        $totalFiberLength = 0;
        $totalTime = 0;
        $totalLatency = 0;

        for ($i = 1; $i < count($path); $i++) {
            $prev = $path[$i - 1];
            $curr = $path[$i];

            if (isset($graph[$prev][$curr])) {
                $edgeData = $graph[$prev][$curr];
                $totalDistance += $edgeData['distance'] ?? 0;
                $totalFiberLength += $edgeData['fiber_length'] ?? 0;
                $totalTime += $edgeData['travel_time'] ?? 0;
                $totalLatency += $edgeData['latency'] ?? 0;
            }
        }

        $hopCount = count($path) - 1;

        return [
            'found' => true,
            'path' => $path,
            'cost' => new RouteCost(
                distance: $totalDistance,
                time: $totalTime,
                fiberLength: $totalFiberLength,
                hopCount: max(0, $hopCount),
                latency: $totalLatency,
                bandwidth: 10000,
                totalCost: $totalDistance + ($totalTime * 10) + ($hopCount * 50)
            ),
            'distance' => $totalDistance,
            'hop_count' => $hopCount,
        ];
    }
}
