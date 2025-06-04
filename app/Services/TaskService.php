<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use App\Repositories\TaskRepositoryInterface;
use App\Exceptions\TaskNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class TaskService implements TaskServiceInterface
{
    protected TaskRepositoryInterface $taskRepository;

    /**
     * TaskService constructor
     * 
     * @param TaskRepositoryInterface $taskRepository
     */
    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks with optional filtering, sorting and pagination
     * 
     * @param array $filters
     * @param string|null $sortBy
     * @param string $sortDirection
     * @param int|null $userId
     * @return LengthAwarePaginator
     */
    public function getAllTasks(
        array $filters = [],
        ?string $sortBy = null,
        string $sortDirection = 'desc',
        ?int $userId = null
    ): LengthAwarePaginator {
        // Delegate to repository layer
        return $this->taskRepository->getPaginatedTasks(
            $filters,
            $sortBy,
            $sortDirection,
            $userId
        );
    }

    /**
     * Get all users for task assignment
     * 
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return User::all();
    }

    /**
     * Get task by id
     * 
     * @param int $id
     * @param int|null $userId Only return task if it belongs to this user (or null for any)
     * @return Task
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id, ?int $userId = null): Task
    {
        $task = $this->taskRepository->getTaskById($id);
        
        // If userId is specified, check if task belongs to user
        if ($userId !== null && !$this->taskBelongsToUser($id, $userId)) {
            throw new TaskNotFoundException($id);
        }
        
        return $task;
    }

    /**
     * Check if a task belongs to a user
     * 
     * @param int $taskId
     * @param int $userId
     * @return bool
     */
    private function taskBelongsToUser(int $taskId, int $userId): bool
    {
        return $this->taskRepository->taskBelongsToUser($taskId, $userId);
    }

    /**
     * Create new task
     * 
     * @param array $taskData
     * @return Task
     */
    public function createTask(array $taskData): Task
    {
        return $this->taskRepository->createTask($taskData);
    }

    /**
     * Update task
     * 
     * @param int $id
     * @param array $taskData
     * @param int|null $userId Only update if task belongs to this user (or null for any)
     * @return Task
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData, ?int $userId = null): Task
    {
        // If userId is specified, check if task belongs to user
        if ($userId !== null && !$this->taskBelongsToUser($id, $userId)) {
            throw new TaskNotFoundException($id);
        }
        
        return $this->taskRepository->updateTask($id, $taskData);
    }

    /**
     * Handle the completed status update
     * 
     * @param array $taskData
     * @param bool $completed
     * @return array
     */
    public function handleCompletedStatus(array $taskData, bool $completed): array
    {
        $taskData['completed_at'] = $completed ? Carbon::now() : null;
        
        // If marked as completed, update status as well
        if ($completed && (!isset($taskData['status']) || $taskData['status'] !== 'completed')) {
            $taskData['status'] = 'completed';
        }
        
        return $taskData;
    }

    /**
     * Delete task
     * 
     * @param int $id
     * @param int|null $userId Only delete if task belongs to this user (or null for any)
     * @return bool
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id, ?int $userId = null): bool
    {
        // If userId is specified, check if task belongs to user
        if ($userId !== null && !$this->taskBelongsToUser($id, $userId)) {
            throw new TaskNotFoundException($id);
        }
        
        return $this->taskRepository->deleteTask($id);
    }
} 