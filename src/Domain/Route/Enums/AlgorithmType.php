<?php

namespace Src\Domain\Route\Enums;

enum AlgorithmType: string
{
    case DIJKSTRA = 'dijkstra';
    case BFS = 'bfs';
    case DFS = 'dfs';
    case ASTAR = 'astar';
    case BELLMAN_FORD = 'bellman_ford';
    case FLOYD_WARSHALL = 'floyd_warshall';

    public function label(): string
    {
        return match($this) {
            self::DIJKSTRA => 'Dijkstra Algorithm',
            self::BFS => 'Breadth-First Search',
            self::DFS => 'Depth-First Search',
            self::ASTAR => 'A* Algorithm',
            self::BELLMAN_FORD => 'Bellman-Ford Algorithm',
            self::FLOYD_WARSHALL => 'Floyd-Warshall Algorithm',
        };
    }

    public function isWeighted(): bool
    {
        return match($this) {
            self::DIJKSTRA, self::ASTAR, self::BELLMAN_FORD, self::FLOYD_WARSHALL => true,
            default => false,
        };
    }

    public function guaranteesShortestPath(): bool
    {
        return match($this) {
            self::DIJKSTRA, self::ASTAR, self::BELLMAN_FORD, self::FLOYD_WARSHALL, self::BFS => true,
            default => false,
        };
    }

    public function timeComplexity(): string
    {
        return match($this) {
            self::DIJKSTRA => 'O((V + E) log V)',
            self::BFS => 'O(V + E)',
            self::DFS => 'O(V + E)',
            self::ASTAR => 'O(E)',
            self::BELLMAN_FORD => 'O(VE)',
            self::FLOYD_WARSHALL => 'O(V³)',
        };
    }
}
