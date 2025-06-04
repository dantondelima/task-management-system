<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Get paginated and filtered tasks
     */
    public function getPaginatedTasks(
        array $filters = [],
        ?string $sortBy = null,
        string $sortDirection = 'desc',
        ?int $userId = null
    ): LengthAwarePaginator;

    /**
     * Get all tasks
     */
    public function getAllTasks(): Collection;

    /**
     * Get task by id
     *
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id): Task;

    /**
     * Check if task belongs to user
     */
    public function taskBelongsToUser(int $taskId, int $userId): bool;

    /**
     * Create new task
     */
    public function createTask(array $taskData): Task;

    /**
     * Update task
     *
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData): Task;

    /**
     * Delete task
     *
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id): bool;
}
