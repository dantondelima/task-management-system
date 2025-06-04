<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService implements TaskServiceInterface
{
    protected TaskRepositoryInterface $taskRepository;

    /**
     * TaskService constructor
     */
    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks with optional filtering, sorting and pagination
     */
    public function getAllTasks(
        array $filters = [],
        ?string $sortBy = null,
        string $sortDirection = 'desc',
        ?int $userId = null
    ): LengthAwarePaginator {
        return $this->taskRepository->getPaginatedTasks(
            $filters,
            $sortBy,
            $sortDirection,
            $userId
        );
    }

    /**
     * Get all users for task assignment
     */
    public function getAllUsers(): Collection
    {
        return User::all();
    }

    /**
     * Get task by id
     *
     * @param  int|null  $userId  Only return task if it belongs to this user (or null for any)
     *
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id, ?int $userId = null): Task
    {
        $task = $this->taskRepository->getTaskById($id);

        if ($userId !== null && ! $this->taskBelongsToUser($id, $userId)) {
            throw new TaskNotFoundException($id);
        }

        return $task;
    }

    /**
     * Create new task
     */
    public function createTask(array $taskData): Task
    {
        return $this->taskRepository->createTask($taskData);
    }

    /**
     * Update task
     *
     * @param  int|null  $userId  Only update if task belongs to this user (or null for any)
     *
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData, ?int $userId = null): Task
    {
        if ($userId !== null && ! $this->taskBelongsToUser($id, $userId)) {
            throw new TaskNotFoundException($id);
        }

        if (isset($taskData['status'])) {
            $task = $this->taskRepository->getTaskById($id);
            
            if ($taskData['status'] === 'completed' && $task->status->value !== 'completed') {
                $taskData['completed_at'] = Carbon::now();
            }
            
            if ($taskData['status'] !== 'completed' && $task->status->value === 'completed') {
                $taskData['completed_at'] = null;
            }
        }

        return $this->taskRepository->updateTask($id, $taskData);
    }

    /**
     * Handle the completed status update
     */
    public function handleCompletedStatus(array $taskData, bool $completed): array
    {
        $taskData['completed_at'] = $completed ? Carbon::now() : null;

        if ($completed && (! isset($taskData['status']) || $taskData['status'] !== 'completed')) {
            $taskData['status'] = 'completed';
        }

        return $taskData;
    }

    /**
     * Delete task
     *
     * @param  int|null  $userId  Only delete if task belongs to this user (or null for any)
     *
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id, ?int $userId = null): bool
    {
        if ($userId !== null && ! $this->taskBelongsToUser($id, $userId)) {
            throw new TaskNotFoundException($id);
        }

        return $this->taskRepository->deleteTask($id);
    }

    /**
     * Check if a task belongs to a user
     */
    private function taskBelongsToUser(int $taskId, int $userId): bool
    {
        return $this->taskRepository->taskBelongsToUser($taskId, $userId);
    }
}
