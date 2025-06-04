<?php

namespace App\Repositories;

use App\Models\Task;
use App\Exceptions\TaskNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
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
    ): LengthAwarePaginator;

    /**
     * Get all tasks
     * 
     * @return Collection
     */
    public function getAllTasks(): Collection;

    /**
     * Get task by id
     * 
     * @param int $id
     * @return Task
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id): Task;

    /**
     * Check if task belongs to user
     * 
     * @param int $taskId
     * @param int $userId
     * @return bool
     */
    public function taskBelongsToUser(int $taskId, int $userId): bool;

    /**
     * Create new task
     * 
     * @param array $taskData
     * @return Task
     */
    public function createTask(array $taskData): Task;

    /**
     * Update task
     * 
     * @param int $id
     * @param array $taskData
     * @return Task
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData): Task;

    /**
     * Delete task
     * 
     * @param int $id
     * @return bool
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id): bool;
} 