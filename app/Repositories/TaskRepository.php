<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class TaskRepository implements TaskRepositoryInterface
{
    protected Task $task;

    /**
     * TaskRepository constructor
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get paginated and filtered tasks
     */
    public function getPaginatedTasks(
        array $filters = [],
        ?string $sortBy = null,
        string $sortDirection = 'desc',
        ?int $userId = null
    ): LengthAwarePaginator {
        $query = $this->task->newQuery();

        $query->with('user');

        // Filter by user
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        // Apply filters
        if (isset($filters['status']) && ! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority']) && ! empty($filters['priority'])) {
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
     */
    public function getAllTasks(): Collection
    {
        return $this->task->all();
    }

    /**
     * Get task by id
     *
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id): Task
    {
        $task = $this->task->with('user')->find($id);

        if (! $task) {
            throw new TaskNotFoundException($id);
        }

        return $task;
    }

    /**
     * Check if task belongs to user
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
     */
    public function createTask(array $taskData): Task
    {
        return $this->task->create($taskData);
    }

    /**
     * Update task
     *
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData): Task
    {
        $task = $this->task->find($id);

        if (! $task) {
            throw new TaskNotFoundException($id);
        }

        $task->update($taskData);

        return $task;
    }

    /**
     * Delete task
     *
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id): bool
    {
        $task = $this->task->find($id);

        if (! $task) {
            throw new TaskNotFoundException($id);
        }

        return $task->delete();
    }
}
