<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\MaintenanceTaskRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Events\MaintenanceTaskCompletedEvent;
use Src\Domain\Workforce\Events\MaintenanceTaskStartedEvent;
use Src\Domain\Workforce\MaintenanceTask;

readonly class MaintenanceTaskService {
    public function __construct(
        private MaintenanceTaskRepository $taskRepository,
    ) {}

    public function createMaintenanceTask(
        Uuid $workOrderId,
        Uuid $assignmentId,
        Uuid $customerId,
        Uuid $serviceId,
        ?string $notes = null,
    ): MaintenanceTask {
        $task = MaintenanceTask::create(
            $workOrderId,
            $assignmentId,
            $customerId,
            $serviceId,
            $notes,
        );
        $this->taskRepository->save($task);
        return $task;
    }

    public function startMaintenanceTask(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");

        $task->startTask();
        $this->taskRepository->save($task);

        $event = MaintenanceTaskStartedEvent::create(
            $task->id,
            $task->workOrderId,
            $task->assignmentId,
            $task->customerId,
        );
        Event::dispatch($event);

        return $task;
    }

    public function completeMaintenanceTask(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");

        $task->completeTask();
        $this->taskRepository->save($task);

        $event = MaintenanceTaskCompletedEvent::create(
            $task->id,
            $task->workOrderId,
            $task->assignmentId,
            $task->customerId,
        );
        Event::dispatch($event);

        return $task;
    }

    public function moveTaskToChecklist(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToChecklist();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToPhoto(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToPhoto();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToMaterial(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToMaterial();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToSignature(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToSignature();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToQC(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToQC();
        return $this->taskRepository->save($task);
    }

    public function approveQC(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->approveQC();
        return $this->taskRepository->save($task);
    }

    public function rejectQC(Uuid $taskId): MaintenanceTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->rejectQC();
        return $this->taskRepository->save($task);
    }
}
