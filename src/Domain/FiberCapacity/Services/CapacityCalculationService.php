<?php

namespace Src\Domain\FiberCapacity\Services;

use Src\Domain\FiberCapacity\FiberCapacity;
use Src\Domain\FiberCapacity\Repositories\FiberCapacityRepositoryInterface;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class CapacityCalculationService
{
    public function __construct(
        private FiberCapacityRepositoryInterface $capacityRepository
    ) {}

    public function calculateUtilization(int $used, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }
        return ($used / $total) * 100;
    }

    public function calculateAvailable(int $used, int $total): int
    {
        return max(0, $total - $used);
    }

    public function calculateTrend(array $history): array
    {
        if (count($history) < 2) {
            return [
                'trend' => 'stable',
                'average_growth' => 0.0,
                'projected_full' => null
            ];
        }

        $differences = [];
        for ($i = 1; $i < count($history); $i++) {
            $differences[] = $history[$i]['used'] - $history[$i-1]['used'];
        }

        $averageGrowth = array_sum($differences) / count($differences);

        $currentUsed = end($history)['used'];
        $total = end($history)['total'] ?? $currentUsed;
        
        if ($averageGrowth <= 0) {
            $projectedFull = null;
        } else {
            $remaining = $total - $currentUsed;
            $daysToFull = $remaining / $averageGrowth;
            $projectedFull = date('Y-m-d', strtotime("+{$daysToFull} days"));
        }

        $trend = $averageGrowth > 5 ? 'increasing' : ($averageGrowth < -5 ? 'decreasing' : 'stable');

        return [
            'trend' => $trend,
            'average_growth' => round($averageGrowth, 2),
            'projected_full' => $projectedFull,
            'days_to_full' => $averageGrowth > 0 ? (int) ceil($remaining / $averageGrowth) : null
        ];
    }

    public function forecastCapacity(
        Uuid $resourceId,
        string $resourceType,
        int $daysToForecast = 30
    ): array {
        $capacity = $this->capacityRepository->findByResourceId($resourceId, $resourceType);
        
        if (!$capacity || empty($capacity->capacityHistory)) {
            return [
                'current_utilization' => 0.0,
                'projected_utilization' => 0.0,
                'will_exceed_warning' => false,
                'will_exceed_critical' => false,
                'forecast_date' => null
            ];
        }

        $trend = $this->calculateTrend($capacity->capacityHistory);
        $currentUsed = $capacity->usedCore;
        $total = $capacity->totalCore;
        $currentUtilization = $this->calculateUtilization($currentUsed, $total);

        $projectedUsed = $currentUsed + ($trend['average_growth'] * $daysToForecast);
        $projectedUtilization = $this->calculateUtilization($projectedUsed, $total);

        $warningDate = null;
        $criticalDate = null;

        if ($currentUtilization < 75.0 && $projectedUtilization >= 75.0) {
            $warningDate = $this->findThresholdDate($capacity, 75.0, $trend['average_growth']);
        }

        if ($currentUtilization < 90.0 && $projectedUtilization >= 90.0) {
            $criticalDate = $this->findThresholdDate($capacity, 90.0, $trend['average_growth']);
        }

        return [
            'current_utilization' => round($currentUtilization, 2),
            'projected_utilization' => round($projectedUtilization, 2),
            'current_used' => $currentUsed,
            'projected_used' => (int) $projectedUsed,
            'will_exceed_warning' => $projectedUtilization >= 75.0,
            'will_exceed_critical' => $projectedUtilization >= 90.0,
            'warning_date' => $warningDate,
            'critical_date' => $criticalDate,
            'forecast_days' => $daysToForecast,
            'trend' => $trend
        ];
    }

    public function calculateRequiredCapacity(
        int $currentUsed,
        int $plannedGrowth,
        float $safetyMargin = 20.0
    ): int {
        $required = $currentUsed + $plannedGrowth;
        $withMargin = $required * (1 + ($safetyMargin / 100));
        
        return (int) ceil($withMargin);
    }

    public function analyzeCapacityGaps(
        array $capacities,
        int $thresholdWarning = 75,
        int $thresholdCritical = 90
    ): array {
        $gaps = [
            'critical' => [],
            'warning' => [],
            'optimal' => [],
            'summary' => [
                'total' => count($capacities),
                'critical_count' => 0,
                'warning_count' => 0,
                'optimal_count' => 0
            ]
        ];

        foreach ($capacities as $capacity) {
            $utilization = $capacity->getUtilizationPercentage();
            
            $item = [
                'id' => $capacity->resourceId->value,
                'type' => $capacity->resourceType,
                'utilization' => round($utilization, 2),
                'available' => $capacity->getAvailableCore()
            ];

            if ($utilization >= $thresholdCritical) {
                $gaps['critical'][] = $item;
                $gaps['summary']['critical_count']++;
            } elseif ($utilization >= $thresholdWarning) {
                $gaps['warning'][] = $item;
                $gaps['summary']['warning_count']++;
            } else {
                $gaps['optimal'][] = $item;
                $gaps['summary']['optimal_count']++;
            }
        }

        return $gaps;
    }

    public function calculateNetworkCapacity(array $oltCapacities): array
    {
        $totalPonPorts = 0;
        $totalOnuCapacity = 0;
        $totalUsedOnu = 0;

        foreach ($oltCapacities as $olt) {
            $totalPonPorts += $olt['total_ports'];
            $totalUsedOnu += $olt['total_onus'];
        }

        $availablePonPorts = array_sum(array_column($oltCapacities, 'available_ports'));
        $totalOnuCapacity = $totalPonPorts * 64;
        
        $utilization = $this->calculateUtilization($totalUsedOnu, $totalOnuCapacity);

        return [
            'total_pon_ports' => $totalPonPorts,
            'available_pon_ports' => $availablePonPorts,
            'total_onu_capacity' => $totalOnuCapacity,
            'total_used_onu' => $totalUsedOnu,
            'available_onu_capacity' => $totalOnuCapacity - $totalUsedOnu,
            'network_utilization' => round($utilization, 2)
        ];
    }

    private function findThresholdDate(
        FiberCapacity $capacity,
        float $threshold,
        float $dailyGrowth
    ): ?string {
        if ($dailyGrowth <= 0) {
            return null;
        }

        $currentUsed = $capacity->usedCore;
        $total = $capacity->totalCore;
        
        if ($currentUsed >= $total) {
            return null;
        }

        $thresholdUsed = ($threshold / 100) * $total;
        $remaining = $thresholdUsed - $currentUsed;
        
        if ($remaining <= 0) {
            return null;
        }

        $days = $remaining / $dailyGrowth;
        
        return date('Y-m-d', strtotime("+{$days} days"));
    }
}
