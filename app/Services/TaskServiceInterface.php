<?php

namespace App\Services;

use App\Models\Task;
use App\Exceptions\TaskNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskServiceInterface
{
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
    ): LengthAwarePaginator;

    /**
     * Get all users for task assignment
     * 
     * @return Collection
     */
    public function getAllUsers(): Collection;

    /**
     * Get task by id
     * 
     * @param int $id
     * @param int|null $userId Only return task if it belongs to this user (or null for any)
     * @return Task
     * @throws TaskNotFoundException
     */
    public function getTaskById(int $id, ?int $userId = null): Task;

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
     * @param int|null $userId Only update if task belongs to this user (or null for any)
     * @return Task
     * @throws TaskNotFoundException
     */
    public function updateTask(int $id, array $taskData, ?int $userId = null): Task;

    /**
     * Handle the completed status update
     * 
     * @param array $taskData
     * @param bool $completed
     * @return array
     */
    public function handleCompletedStatus(array $taskData, bool $completed): array;

    /**
     * Delete task
     * 
     * @param int $id
     * @param int|null $userId Only delete if task belongs to this user (or null for any)
     * @return bool
     * @throws TaskNotFoundException
     */
    public function deleteTask(int $id, ?int $userId = null): bool;
} 