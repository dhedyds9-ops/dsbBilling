<?php

namespace Src\Domain\Route\Services;

use Src\Domain\Route\Events\AlternativeFound;
use Src\Domain\Route\ValueObjects\RouteCost;

class AlternativePathService
{
    public function __construct(
        private ShortestPathService $shortestPathService
    ) {}

    public function findAlternatives(
        string $source,
        string $target,
        int $count,
        array $graph
    ): array {
        $shortestResult = $this->shortestPathService->findPath(
            $source,
            $target,
            \Src\Domain\Route\Enums\AlgorithmType::DIJKSTRA,
            $graph
        );

        if (!$shortestResult['found']) {
            return [];
        }

        $alternatives = [];
        $blockedEdges = [];
        $maxIterations = $count * 3;
        $iterations = 0;

        while (count($alternatives) < $count && $iterations < $maxIterations) {
            $iterations++;
            $modifiedGraph = $this->removeEdges($graph, $blockedEdges);

            $result = $this->shortestPathService->findPath(
                $source,
                $target,
                \Src\Domain\Route\Enums\AlgorithmType::DIJKSTRA,
                $modifiedGraph
            );

            if ($result['found']) {
                $pathKey = implode('-', $result['path']);
                
                if (!isset($blockedEdges[$pathKey])) {
                    $difference = $this->calculateDifference(
                        $shortestResult['cost']->totalCost,
                        $result['cost']->totalCost
                    );

                    $alternatives[] = [
                        'path' => $result['path'],
                        'cost' => $result['cost'],
                        'difference' => $difference,
                        'iteration' => $iterations,
                    ];

                    $blockedEdges[] = $pathKey;
                }
            }

            if ($this->shouldTerminate($alternatives, $iterations)) {
                break;
            }
        }

        usort($alternatives, fn($a, $b) => $a['difference'] <=> $b['difference']);

        return array_slice($alternatives, 0, $count);
    }

    public function findDisjointPaths(
        string $source,
        string $target,
        int $count,
        array $graph
    ): array {
        $paths = [];

        $firstPath = $this->shortestPathService->findPath(
            $source,
            $target,
            \Src\Domain\Route\Enums\AlgorithmType::DIJKSTRA,
            $graph
        );

        if (!$firstPath['found']) {
            return [];
        }

        $paths[] = $firstPath;

        for ($i = 1; $i < $count; $i++) {
            $modifiedGraph = $this->makeEdgesExpensive($graph, $paths);
            
            $nextPath = $this->shortestPathService->findPath(
                $source,
                $target,
                \Src\Domain\Route\Enums\AlgorithmType::DIJKSTRA,
                $modifiedGraph
            );

            if ($nextPath['found']) {
                $paths[] = $nextPath;
            }
        }

        return $paths;
    }

    public function findKShortestPaths(
        string $source,
        string $target,
        int $k,
        array $graph
    ): array {
        $paths = [];
        $pathCosts = [];

        $firstPath = $this->shortestPathService->findPath(
            $source,
            $target,
            \Src\Domain\Route\Enums\AlgorithmType::DIJKSTRA,
            $graph
        );

        if (!$firstPath['found']) {
            return [];
        }

        $paths[] = $firstPath;
        $pathCosts[] = $firstPath['cost']->totalCost;

        $candidatePaths = [];
        $maxCandidates = $k * 10;

        for ($i = 1; $i < $k && count($paths) < $k; $i++) {
            $lastPath = $paths[count($paths) - 1];

            for ($j = 0; $j < count($lastPath['path']) - 1; $j++) {
                $spurNode = $lastPath['path'][$j];
                $rootPath = array_slice($lastPath['path'], 0, $j + 1);

                $modifiedGraph = $this->removeEdgesExceptRoot($graph, $rootPath);
                
                $spurResult = $this->shortestPathService->findPath(
                    $spurNode,
                    $target,
                    \Src\Domain\Route\Enums\AlgorithmType::DIJKSTRA,
                    $modifiedGraph
                );

                if ($spurResult['found']) {
                    $totalPath = array_merge(
                        array_slice($rootPath, 0, -1),
                        $spurResult['path']
                    );
                    
                    $totalCost = $this->calculateTotalCost($totalPath, $graph);
                    $cost = new RouteCost(
                        distance: $totalCost,
                        time: $totalCost / 60,
                        fiberLength: $totalCost,
                        hopCount: count($totalPath) - 1,
                        totalCost: $totalCost
                    );

                    $pathKey = implode('-', $totalPath);
                    
                    if (!in_array($pathKey, array_map(fn($p) => implode('-', $p['path']), $paths))) {
                        $candidatePaths[] = [
                            'path' => $totalPath,
                            'cost' => $cost,
                        ];
                    }
                }
            }

            if (!empty($candidatePaths)) {
                usort($candidatePaths, fn($a, $b) => $a['cost']->totalCost <=> $b['cost']->totalCost);
                
                foreach ($candidatePaths as $candidate) {
                    $pathKey = implode('-', $candidate['path']);
                    $exists = false;
                    
                    foreach ($paths as $existing) {
                        if (implode('-', $existing['path']) === $pathKey) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    if (!$exists) {
                        $paths[] = $candidate;
                        break;
                    }
                }
            }
        }

        return array_slice($paths, 0, $k);
    }

    private function calculateDifference(float $shortestCost, float $alternativeCost): float
    {
        if ($shortestCost === 0) {
            return 0;
        }
        return (($alternativeCost - $shortestCost) / $shortestCost) * 100;
    }

    private function removeEdges(array $graph, array $pathKeys): array
    {
        $modified = $graph;

        foreach ($pathKeys as $pathKey) {
            $nodes = explode('-', $pathKey);
            
            for ($i = 0; $i < count($nodes) - 1; $i++) {
                $from = $nodes[$i];
                $to = $nodes[$i + 1];

                if (isset($modified[$from][$to])) {
                    $modified[$from][$to]['cost'] = PHP_FLOAT_MAX / 2;
                }
                if (isset($modified[$to][$from])) {
                    $modified[$to][$from]['cost'] = PHP_FLOAT_MAX / 2;
                }
            }
        }

        return $modified;
    }

    private function makeEdgesExpensive(array $graph, array $existingPaths): array
    {
        $modified = $graph;

        foreach ($existingPaths as $pathData) {
            $path = $pathData['path'];
            
            for ($i = 0; $i < count($path) - 1; $i++) {
                $from = $path[$i];
                $to = $path[$i + 1];

                if (isset($modified[$from][$to])) {
                    $modified[$from][$to]['cost'] *= 2;
                }
                if (isset($modified[$to][$from])) {
                    $modified[$to][$from]['cost'] *= 2;
                }
            }
        }

        return $modified;
    }

    private function removeEdgesExceptRoot(array $graph, array $rootPath): array
    {
        $modified = $graph;

        for ($i = 0; $i < count($rootPath) - 1; $i++) {
            $from = $rootPath[$i];
            $to = $rootPath[$i + 1];

            if (isset($modified[$from][$to])) {
                unset($modified[$from][$to]);
            }
            if (isset($modified[$to][$from])) {
                unset($modified[$to][$from]);
            }
        }

        return $modified;
    }

    private function calculateTotalCost(array $path, array $graph): float
    {
        $totalCost = 0;

        for ($i = 0; $i < count($path) - 1; $i++) {
            $from = $path[$i];
            $to = $path[$i + 1];

            if (isset($graph[$from][$to])) {
                $totalCost += $graph[$from][$to]['cost'] ?? $graph[$from][$to]['distance'] ?? 0;
            }
        }

        return $totalCost;
    }

    private function shouldTerminate(array $alternatives, int $iterations): bool
    {
        if (empty($alternatives)) {
            return $iterations >= 10;
        }

        $lastAlternative = end($alternatives);
        return $lastAlternative['difference'] > 200;
    }
}
