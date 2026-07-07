<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\InstallationTaskRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Events\InstallationTaskCompletedEvent;
use Src\Domain\Workforce\Events\InstallationTaskStartedEvent;
use Src\Domain\Workforce\InstallationTask;

readonly class InstallationTaskService {
    public function __construct(
        private InstallationTaskRepository $taskRepository,
    ) {}

    public function createInstallationTask(
        Uuid $workOrderId,
        Uuid $assignmentId,
        Uuid $customerId,
        ?string $notes = null,
    ): InstallationTask {
        $task = InstallationTask::create(
            $workOrderId,
            $assignmentId,
            $customerId,
            $notes,
        );
        $this->taskRepository->save($task);
        return $task;
    }

    public function startInstallationTask(Uuid $taskId): InstallationTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");

        $task->startTask();
        $this->taskRepository->save($task);

        $event = InstallationTaskStartedEvent::create(
            $task->id,
            $task->workOrderId,
            $task->assignmentId,
            $task->customerId,
        );
        Event::dispatch($event);

        return $task;
    }

    public function completeInstallationTask(Uuid $taskId): InstallationTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");

        $task->completeTask();
        $this->taskRepository->save($task);

        $event = InstallationTaskCompletedEvent::create(
            $task->id,
            $task->workOrderId,
            $task->assignmentId,
            $task->customerId,
        );
        Event::dispatch($event);

        return $task;
    }

    public function moveTaskToChecklist(Uuid $taskId): InstallationTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToChecklist();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToPhoto(Uuid $taskId): InstallationTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToPhoto();
        return $this->taskRepository->save($task);
    }

    public function moveTaskToMaterial(Uuid $taskId): InstallationTask {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) throw new \InvalidArgumentException("Task not found");
        $task->moveToMaterial();
        return $this->taskRepository->save($task);
    }
}
