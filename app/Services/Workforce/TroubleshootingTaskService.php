<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\TroubleshootingTaskRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Events\TroubleshootingTaskCompletedEvent;
use Src\Domain\Workforce\Events\TroubleshootingTaskStartedEvent;
use Src\Domain\Workforce\TroubleshootingTask;

readonly class TroubleshootingTaskService {
    public function __construct(
        private TroubleshootingTaskRepository $taskRepository,
    ) {}

    public function createTroubleshootingTask(
        Uuid $workOrderId,
        Uuid $assignmentId,
        Uuid $customerId,
        Uuid $ticketId,
        ?string $notes = null,
    ): TroubleshootingTask {
        $task = TroubleshootingTask::create(
            $workOrderId,
            $assignmentId,
            $customerId,
            $ticketId,
            $notes,
        );
        $this->taskRepository->save($task);
        return $task;
    }

    public function startTroubleshootingTask(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");

        $task->startTask();
        $this->taskRepository->save($task);

        $event = TroubleshootingTaskStartedEvent::create(
            $task->id,
            $task->workOrderId,
            $task->assignmentId,
            $task->customerId,
            $task->ticketId,
        );
        Event::dispatch($event);

        return $task;
    }

    public function completeTroubleshootingTask(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");

        $task->completeTask();
        $this->taskRepository->save($task);

        $event = TroubleshootingTaskCompletedEvent::create(
            $task->id,
            $task->workOrderId,
            $task->assignmentId,
            $task->customerId,
            $task->ticketId,
        );
        Event::dispatch($event);

        return $task;
    }

    public function setResolution(Uuid $taskId, string $resolution): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->setResolution($resolution);
        return $this->taskRepository->save($task);
    }

    public function moveTaskToChecklist(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToChecklist();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToPhoto(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToPhoto();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToMaterial(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToMaterial();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToSignature(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToSignature();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToQC(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToQC();
        return $this->taskRepository->save($task);
    }

    public function approveQC(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->approveQC();
        return $this->taskRepository->save($task);
    }

    public function rejectQC(Uuid $taskId): TroubleshootingTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->rejectQC();
        return $this->taskRepository->save($task);
    }
}
