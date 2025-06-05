<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskServiceInterface
{
    /**
     * Get all tasks with optional filtering, sorting and pagination
     */
    public function getAllTasks(
        array $filters = [],
        ?string $sortBy = null,
        string $sortDirection = 'desc',
        ?int $userId = null
    ): LengthAwarePaginator;

    /**
     * Get all users for task assignment
     */
    public function getAllUsers(): Collection;

    /**
     * Get all categories for task categorization
     */
    public function getAllCategories(): Collection;

    /**
     * Get task by id
     *
     * @param  int|null  $userId  Only return task if it belongs to this user (or null for any)
     *
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id, ?int $userId = null): Task;

    /**
     * Create new task
     */
    public function createTask(array $taskData): Task;

    /**
     * Update task
     *
     * @param  int|null  $userId  Only update if task belongs to this user (or null for any)
     *
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData, ?int $userId = null): Task;

    /**
     * Handle the completed status update
     */
    public function handleCompletedStatus(array $taskData, bool $completed): array;

    /**
     * Delete task
     *
     * @param  int|null  $userId  Only delete if task belongs to this user (or null for any)
     *
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id, ?int $userId = null): bool;
}
