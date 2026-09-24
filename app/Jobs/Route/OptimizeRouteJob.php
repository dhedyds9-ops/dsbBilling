<?php

namespace App\Jobs\Route;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Domain\Route\Services\RoutePlanningService;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class OptimizeRouteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $routeId,
        protected array $options = []
    ) {}

    public function handle(RoutePlanningService $routePlanningService): void
    {
        $routeUuid = Uuid::fromString($this->routeId);

        $this->updateProgress('optimization_started', [
            'route_id' => $this->routeId
        ]);

        $route = $routePlanningService->optimizeRoute($routeUuid);

        $this->updateProgress('optimization_completed', [
            'route_id' => $route->id->value,
            'hop_count' => $route->getHopCount(),
            'total_distance' => $route->getTotalDistance(),
            'optimized_at' => $route->optimizedAt?->format('Y-m-d H:i:s')
        ]);

        if (!empty($this->options['optimize_alternatives'])) {
            $alternatives = $routePlanningService->calculateAlternativeRoutes(
                $route->sourceNodeId,
                $route->sourceNodeType,
                $route->targetNodeId,
                $route->targetNodeType,
                $this->options['alternative_count'] ?? 2
            );

            foreach ($alternatives as $altRoute) {
                $routePlanningService->optimizeRoute($altRoute->id);
            }

            $this->updateProgress('alternatives_optimized', [
                'count' => count($alternatives)
            ]);
        }
    }

    private function updateProgress(string $stage, array $data): void
    {
        if (!empty($this->options['callback'])) {
            $callback = $this->options['callback'];
            $callback($stage, $data);
        }
    }

    public function getQueue(): string
    {
        return 'route-optimization';
    }
}
