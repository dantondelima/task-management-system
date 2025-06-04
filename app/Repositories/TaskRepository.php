<?php

namespace App\Repositories;

use App\Models\Task;
use App\Exceptions\TaskNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class TaskRepository implements TaskRepositoryInterface
{
    protected Task $task;

    /**
     * TaskRepository constructor
     * 
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get paginated and filtered tasks
     * 
     * @param array $filters
     * @param string|null $sortBy
     * @param string $sortDirection
     * @param int|null $userId
     * @return LengthAwarePaginator
     */
    public function getPaginatedTasks(
        array $filters = [],
        ?string $sortBy = null,
        string $sortDirection = 'desc',
        ?int $userId = null
    ): LengthAwarePaginator {
        $query = $this->task->newQuery();
        
        // Filter by user
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        
        // Apply filters
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['priority']) && !empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        // Completed filter
        if (isset($filters['completed'])) {
            if ($filters['completed'] === 'yes') {
                $query->whereNotNull('completed_at');
            } elseif ($filters['completed'] === 'no') {
                $query->whereNull('completed_at');
            }
        }
        
        // Apply sorting
        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest(); // Default sort by created_at desc
        }
        
        // Get items per page from filters or use default
        $perPage = $filters['per_page'] ?? 10;
        
        // Return paginated results
        return $query->paginate($perPage)->appends(Request::query());
    }

    /**
     * Get all tasks
     * 
     * @return Collection
     */
    public function getAllTasks(): Collection
    {
        return $this->task->all();
    }

    /**
     * Get task by id
     * 
     * @param int $id
     * @return Task
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id): Task
    {
        $task = $this->task->find($id);
        
        if (!$task) {
            throw new TaskNotFoundException($id);
        }
        
        return $task;
    }

    /**
     * Check if task belongs to user
     * 
     * @param int $taskId
     * @param int $userId
     * @return bool
     */
    public function taskBelongsToUser(int $taskId, int $userId): bool
    {
        $task = $this->task->where('id', $taskId)
            ->where('user_id', $userId)
            ->first();
            
        return $task !== null;
    }

    /**
     * Create new task
     * 
     * @param array $taskData
     * @return Task
     */
    public function createTask(array $taskData): Task
    {
        return $this->task->create($taskData);
    }

    /**
     * Update task
     * 
     * @param int $id
     * @param array $taskData
     * @return Task
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData): Task
    {
        $task = $this->task->find($id);
        
        if (!$task) {
            throw new TaskNotFoundException($id);
        }
        
        $task->update($taskData);
        return $task;
    }

    /**
     * Delete task
     * 
     * @param int $id
     * @return bool
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id): bool
    {
        $task = $this->task->find($id);
        
        if (!$task) {
            throw new TaskNotFoundException($id);
        }
        
        return $task->delete();
    }
} 