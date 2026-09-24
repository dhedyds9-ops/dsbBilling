<?php

namespace Src\Domain\Outage\Services;

use Src\Domain\Outage\Outage;
use Src\Domain\Outage\AffectedNode;
use Src\Domain\Outage\AffectedCustomer;
use Src\Domain\Outage\AffectedService;
use Src\Domain\Outage\Enums\NodeType;
use Src\Domain\Outage\Enums\OutageSeverity;
use Src\Domain\Outage\Enums\ImpactLevel;
use Src\Domain\Outage\Events\OutageDetected;
use Src\Domain\Outage\Events\CustomerAffected;
use Src\Domain\Outage\Repositories\OutageRepositoryInterface;
use Src\Domain\Outage\Repositories\AffectedNodeRepositoryInterface;
use Src\Domain\Outage\Repositories\AffectedCustomerRepositoryInterface;
use Src\Domain\Outage\Repositories\AffectedServiceRepositoryInterface;
use Src\Domain\Outage\ValueObjects\AffectedArea;
use Src\Domain\Outage\ValueObjects\ImpactAssessment;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class OutageAnalysisService
{
    public function __construct(
        private OutageRepositoryInterface $outageRepository,
        private AffectedNodeRepositoryInterface $nodeRepository,
        private AffectedCustomerRepositoryInterface $customerRepository,
        private AffectedServiceRepositoryInterface $serviceRepository
    ) {}

    public function detectOutage(
        NodeType $nodeType,
        string $nodeId,
        string $nodeName,
        string $detectionMethod,
        ?OutageSeverity $severity = null
    ): Outage {
        $outageId = Uuid::generate();
        
        $severity = $severity ?? OutageSeverity::MINOR;

        $outage = Outage::create(
            $outageId,
            $nodeType,
            $nodeId,
            $nodeName,
            $severity,
            $detectionMethod
        );

        $this->outageRepository->save($outage);

        return $outage;
    }

    public function analyzeImpact(Uuid $outageId, array $affectedNodes): ImpactAssessment
    {
        $outage = $this->outageRepository->findById($outageId);
        if (!$outage) {
            throw new \InvalidArgumentException("Outage not found");
        }

        $totalAffected = 0;
        $completelyDown = 0;
        $partiallyAffected = 0;
        $degradedService = 0;
        $affectedServices = [];

        foreach ($affectedNodes as $node) {
            $this->addAffectedNode($outageId, $node);

            $customerCount = $node['affected_customer_count'] ?? 0;
            $totalAffected += $customerCount;

            $impactLevel = $node['impact_level'] ?? ImpactLevel::TOTAL;
            
            if ($impactLevel === ImpactLevel::TOTAL) {
                $completelyDown += $customerCount;
            } elseif ($impactLevel === ImpactLevel::PARTIAL) {
                $partiallyAffected += $customerCount;
            } elseif ($impactLevel === ImpactLevel::DEGRADED) {
                $degradedService += $customerCount;
            }

            foreach ($node['affected_services'] ?? [] as $service) {
                $affectedServices[$service['service_id']] = $service;
            }
        }

        $overallLevel = ImpactLevel::fromPercentage(
            $completelyDown > 0 ? 0 : ($partiallyAffected > 0 ? 50 : ($degradedService > 0 ? 90 : 100))
        );

        $revenueImpact = $this->calculateRevenueImpact($totalAffected);

        $assessment = new ImpactAssessment(
            $totalAffected,
            $completelyDown,
            $partiallyAffected,
            $degradedService,
            $overallLevel,
            $revenueImpact,
            array_values($affectedServices)
        );

        $affectedArea = $this->buildAffectedArea($outage, $affectedNodes);

        $outage->confirm($assessment, $affectedArea);
        $this->outageRepository->save($outage);

        return $assessment;
    }

    public function traceDownstream(string $nodeType, string $nodeId): array
    {
        return match($nodeType) {
            'olt' => $this->traceFromOlt($nodeId),
            'odc' => $this->traceFromOdc($nodeId),
            'odp' => $this->traceFromOdp($nodeId),
            'splitter' => $this->traceFromSplitter($nodeId),
            'fiber_cable' => $this->traceFromFiberCable($nodeId),
            default => [],
        };
    }

    private function traceFromOlt(string $oltId): array
    {
        $affectedNodes = [];

        $affectedNodes[] = [
            'type' => NodeType::OLT->value,
            'id' => $oltId,
            'impact_level' => ImpactLevel::TOTAL
        ];

        return $affectedNodes;
    }

    private function traceFromOdc(string $odcId): array
    {
        $affectedNodes = [];

        $affectedNodes[] = [
            'type' => NodeType::ODC->value,
            'id' => $odcId,
            'impact_level' => ImpactLevel::TOTAL
        ];

        return $affectedNodes;
    }

    private function traceFromOdp(string $odpId): array
    {
        $affectedNodes = [];

        $affectedNodes[] = [
            'type' => NodeType::ODP->value,
            'id' => $odpId,
            'impact_level' => ImpactLevel::TOTAL
        ];

        return $affectedNodes;
    }

    private function traceFromSplitter(string $splitterId): array
    {
        $affectedNodes = [];

        $affectedNodes[] = [
            'type' => NodeType::SPLITTER->value,
            'id' => $splitterId,
            'impact_level' => ImpactLevel::TOTAL
        ];

        return $affectedNodes;
    }

    private function traceFromFiberCable(string $cableId): array
    {
        $affectedNodes = [];

        $affectedNodes[] = [
            'type' => NodeType::FIBER_CABLE->value,
            'id' => $cableId,
            'impact_level' => ImpactLevel::TOTAL
        ];

        return $affectedNodes;
    }

    public function addAffectedNode(Uuid $outageId, array $nodeData): AffectedNode
    {
        $nodeType = NodeType::from($nodeData['type']);
        
        $node = AffectedNode::create(
            Uuid::generate(),
            $outageId,
            $nodeType,
            $nodeData['id'],
            $nodeData['name'] ?? $nodeData['id'],
            ImpactLevel::from($nodeData['impact_level'] ?? 'total')
        );

        if (!empty($nodeData['parent_nodes'])) {
            foreach ($nodeData['parent_nodes'] as $parent) {
                $node->addParentNode($parent['id'], NodeType::from($parent['type']));
            }
        }

        if (!empty($nodeData['child_nodes'])) {
            foreach ($nodeData['child_nodes'] as $child) {
                $node->addChildNode($child['id'], NodeType::from($child['type']));
            }
        }

        $node->affectedCustomerCount = $nodeData['affected_customer_count'] ?? 0;

        $this->nodeRepository->save($node);

        return $node;
    }

    public function addAffectedCustomer(
        Uuid $outageId,
        string $customerId,
        string $customerName,
        string $customerCode,
        ImpactLevel $impactLevel,
        array $affectedServices = []
    ): AffectedCustomer {
        $customer = AffectedCustomer::create(
            Uuid::generate(),
            $outageId,
            $customerId,
            $customerName,
            $customerCode,
            $impactLevel,
            $affectedServices
        );

        $this->customerRepository->save($customer);

        foreach ($affectedServices as $service) {
            $affectedService = AffectedService::create(
                Uuid::generate(),
                $outageId,
                $customerId,
                $service['service_id'],
                $service['service_name'],
                $service['service_type'],
                $impactLevel
            );

            $this->serviceRepository->save($affectedService);
        }

        return $customer;
    }

    private function buildAffectedArea(Outage $outage, array $affectedNodes): AffectedArea
    {
        $affectedOltIds = [];
        $affectedOdpIds = [];
        $affectedOnuIds = [];
        $affectedCustomerIds = [];
        $totalCustomers = 0;

        foreach ($affectedNodes as $node) {
            $nodeType = $node['type'] ?? '';
            
            if ($nodeType === 'olt') {
                $affectedOltIds[] = $node['id'];
            } elseif ($nodeType === 'odp') {
                $affectedOdpIds[] = $node['id'];
            } elseif ($nodeType === 'onu') {
                $affectedOnuIds[] = $node['id'];
            }

            $totalCustomers += $node['affected_customer_count'] ?? 0;
        }

        return new AffectedArea(
            $outage->originNodeType,
            $outage->originNodeId,
            $affectedNodes,
            $affectedOltIds,
            $affectedOdpIds,
            $affectedOnuIds,
            $affectedCustomerIds,
            count($affectedNodes),
            $totalCustomers
        );
    }

    private function calculateRevenueImpact(int $affectedCustomers): float
    {
        $averageRevenuePerCustomer = 150000;
        return $affectedCustomers * $averageRevenuePerCustomer;
    }

    public function getOutageStatistics(): array
    {
        $activeOutages = $this->outageRepository->findActiveOutages();
        
        $totalAffectedCustomers = 0;
        $criticalCount = 0;
        $majorCount = 0;

        foreach ($activeOutages as $outage) {
            $totalAffectedCustomers += $outage->affectedCustomerCount;
            
            if ($outage->severity === OutageSeverity::CRITICAL) {
                $criticalCount++;
            } elseif ($outage->severity === OutageSeverity::MAJOR) {
                $majorCount++;
            }
        }

        return [
            'active_outages' => count($activeOutages),
            'critical_count' => $criticalCount,
            'major_count' => $majorCount,
            'total_affected_customers' => $totalAffectedCustomers,
        ];
    }
}
