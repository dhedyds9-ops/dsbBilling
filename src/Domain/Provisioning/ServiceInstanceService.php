<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ServiceInstanceService
{
    public function __construct(
        private ServiceInstanceRepositoryInterface $repository,
        private ProvisionPipelineRepositoryInterface $pipelineRepository
    ) {}

    public function createServiceInstance(
        Uuid $customerServiceId,
        Uuid $pipelineId
    ): ServiceInstance {
        $pipeline = $this->pipelineRepository->findById($pipelineId);
        if (!$pipeline) {
            throw new \InvalidArgumentException("Provision pipeline not found");
        }

        $instance = ServiceInstance::create(
            Uuid::generate(),
            $customerServiceId,
            $pipelineId
        );

        $this->repository->save($instance);
        return $instance;
    }

    public function startProvisioning(Uuid $instanceId): void
    {
        $instance = $this->repository->findById($instanceId);
        if (!$instance) {
            throw new \InvalidArgumentException("Service instance not found");
        }

        $instance->startReserving();
        $this->repository->save($instance);
    }

    public function completeStep(Uuid $instanceId, string $stepName, string $status, ?string $error = null): void
    {
        $instance = $this->repository->findById($instanceId);
        if (!$instance) {
            throw new \InvalidArgumentException("Service instance not found");
        }

        $instance->addStep($stepName, $status, $error);
        $this->repository->save($instance);
    }

    public function failProvisioning(Uuid $instanceId): void
    {
        $instance = $this->repository->findById($instanceId);
        if (!$instance) {
            throw new \InvalidArgumentException("Service instance not found");
        }

        $instance->fail();
        $this->repository->save($instance);
    }
}
