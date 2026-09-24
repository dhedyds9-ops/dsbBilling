<?php

namespace App\Services\ACS;

use App\Models\ACS\DeviceTask;
use App\Repositories\TaskRepository;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getAllTasks()
    {
        return $this->taskRepository->all(['*'], ['device']);
    }

    public function getTaskById(int $id)
    {
        return $this->taskRepository->find($id, ['*'], ['device']);
    }

    public function createTask(array $data)
    {
        $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $data['created_by'] = auth()->id() ?? null;
        $data['updated_by'] = auth()->id() ?? null;
        return $this->taskRepository->create($data);
    }

    public function updateTask(int $id, array $data)
    {
        $data['updated_by'] = auth()->id() ?? null;
        return $this->taskRepository->update($id, $data);
    }

    public function deleteTask(int $id)
    {
        return $this->taskRepository->delete($id);
    }
}
