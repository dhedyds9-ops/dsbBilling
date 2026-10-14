<?php

namespace Src\Domain\Provisioning;

use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class ProvisioningOrchestrator
{
    public function __construct(
        private ServiceInstanceRepositoryInterface $instanceRepository,
        private ProvisionPipelineRepositoryInterface $pipelineRepository,
        private ResourceReservationService $reservationService,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function startProvisioningWorkflow(Uuid $customerServiceId, Uuid $pipelineId): Uuid
    {
        $pipeline = $this->pipelineRepository->findById($pipelineId);
        if (!$pipeline) {
            throw new \InvalidArgumentException("Pipeline not found");
        }

        $instance = ServiceInstance::create(
            Uuid::generate(),
            $customerServiceId,
            $pipelineId
        );

        $this->instanceRepository->save($instance);

        $this->eventDispatcher->dispatch(new Events\ServiceInstanceCreatedEvent($instance->id));

        return $instance->id;
    }

    public function executeNextStep(Uuid $instanceId): void
    {
        $instance = $this->instanceRepository->findById($instanceId);
        if (!$instance) {
            throw new \InvalidArgumentException("Instance not found");
        }

        $pipeline = $this->pipelineRepository->findById($instance->provisionPipelineId);
        $steps = $pipeline->getOrderedSteps();

        $completedSteps = array_filter($instance->steps, fn($s) => $s['status'] === 'completed');
        $nextStepIndex = count($completedSteps);

        if ($nextStepIndex >= count($steps)) {
            $instance->markVerified();
            $this->instanceRepository->save($instance);
            $this->eventDispatcher->dispatch(new Events\ProvisioningVerifiedEvent($instanceId));
            return;
        }

        $nextStep = $steps[$nextStepIndex];
        $this->executeStep($instance, $nextStep);
    }

    private function executeStep(ServiceInstance $instance, PipelineStep $step): void
    {
        try {
            $instance->addStep($step->value, 'in_progress');

            switch ($step) {
                case PipelineStep::RESERVE_RESOURCES:
                    $this->eventDispatcher->dispatch(new Events\ResourcesReservedEvent($instance->id));
                    break;
                case PipelineStep::ASSIGN_DEVICE:
                    $this->eventDispatcher->dispatch(new Events\DeviceAssignedEvent($instance->id));
                    break;
                case PipelineStep::ASSIGN_VLAN:
                    $this->eventDispatcher->dispatch(new Events\VlanAllocatedEvent($instance->id));
                    break;
                case PipelineStep::ASSIGN_IP:
                    $this->eventDispatcher->dispatch(new Events\IpAllocatedEvent($instance->id));
                    break;
                case PipelineStep::ASSIGN_QUEUE:
                    $this->eventDispatcher->dispatch(new Events\QueueAllocatedEvent($instance->id));
                    break;
                case PipelineStep::PROVISION_ROUTER:
                    $this->eventDispatcher->dispatch(new Events\RouterProvisionedEvent($instance->id));
                    break;
                case PipelineStep::PROVISION_RADIUS:
                    $this->eventDispatcher->dispatch(new Events\RadiusProvisionedEvent($instance->id));
                    break;
                case PipelineStep::PROVISION_ONU:
                    $this->eventDispatcher->dispatch(new Events\OnuProvisionedEvent($instance->id));
                    break;
                case PipelineStep::VERIFY_PROVISIONING:
                    $this->eventDispatcher->dispatch(new Events\ProvisioningVerifiedEvent($instance->id));
                    break;
                case PipelineStep::RELEASE_RESERVATION:
                    $this->eventDispatcher->dispatch(new Events\ResourcesReleasedEvent($instance->id));
                    break;
                default:
                    break;
            }

            $instance->addStep($step->value, 'completed');
        } catch (\Exception $e) {
            $instance->addStep($step->value, 'failed', $e->getMessage());
            $instance->fail();
            $this->eventDispatcher->dispatch(new Events\ProvisioningFailedEvent($instance->id, $e->getMessage()));
        }

        $this->instanceRepository->save($instance);
    }

    public function rollback(Uuid $instanceId): void
    {
        $instance = $this->instanceRepository->findById($instanceId);
        if (!$instance) {
            throw new \InvalidArgumentException("Instance not found");
        }

        $this->eventDispatcher->dispatch(new Events\RollbackStartedEvent($instanceId));

        // Execute rollback logic
        $instance->cancel();

        $this->eventDispatcher->dispatch(new Events\RollbackCompletedEvent($instanceId));
        $this->instanceRepository->save($instance);
    }
}
