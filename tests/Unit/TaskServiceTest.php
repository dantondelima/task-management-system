<?php

declare(strict_types=1);

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Models\User;
use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\TaskRepositoryInterface;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mockTaskRepository = $this->createMock(TaskRepositoryInterface::class);
    $this->mockCategoryRepository = $this->createMock(CategoryRepositoryInterface::class);
    $this->taskService = new TaskService($this->mockTaskRepository, $this->mockCategoryRepository);
    $this->user = User::factory()->create();
});

test('get all tasks returns paginated tasks', function () {
    $filters = ['status' => 'pending'];
    $sortBy = 'due_date';
    $sortDirection = 'asc';
    $userId = $this->user->id;

    $mockPaginator = new LengthAwarePaginator(
        Task::factory()->count(5)->make(),
        5,
        10,
        1
    );

    $this->mockTaskRepository->expects($this->once())
        ->method('getPaginatedTasks')
        ->with($filters, $sortBy, $sortDirection, $userId)
        ->willReturn($mockPaginator);

    $result = $this->taskService->getAllTasks($filters, $sortBy, $sortDirection, $userId);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($result->total())->toBe(5);
});

test('get all users returns all users', function () {
    User::factory()->count(3)->create();

    $result = $this->taskService->getAllUsers();

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe(4);
});

test('get all categories returns all categories', function () {
    $categories = new Collection(['category1', 'category2']);

    $this->mockCategoryRepository->expects($this->once())
        ->method('getAllCategories')
        ->willReturn($categories);

    $result = $this->taskService->getAllCategories();

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toBe($categories);
});

test('get task by id returns task', function () {
    $task = Task::factory()->make(['id' => 1]);

    $this->mockTaskRepository->expects($this->once())
        ->method('getTaskById')
        ->with(1)
        ->willReturn($task);

    $result = $this->taskService->getTaskById(1);

    expect($result)->toBeInstanceOf(Task::class);
    expect($result->id)->toBe(1);
});

test('get task by id with user check returns task if it belongs to user', function () {
    $task = Task::factory()->make(['id' => 1, 'user_id' => $this->user->id]);
    $userId = $this->user->id;

    $this->mockTaskRepository->expects($this->once())
        ->method('getTaskById')
        ->with(1)
        ->willReturn($task);

    $this->mockTaskRepository->expects($this->once())
        ->method('taskBelongsToUser')
        ->with(1, $userId)
        ->willReturn(true);

    $result = $this->taskService->getTaskById(1, $userId);

    expect($result)->toBeInstanceOf(Task::class);
    expect($result->id)->toBe(1);
});

test('get task by id with user check throws exception if task does not belong to user', function () {
    $task = Task::factory()->make(['id' => 1, 'user_id' => 999]);
    $userId = $this->user->id;

    $this->mockTaskRepository->expects($this->once())
        ->method('getTaskById')
        ->with(1)
        ->willReturn($task);

    $this->mockTaskRepository->expects($this->once())
        ->method('taskBelongsToUser')
        ->with(1, $userId)
        ->willReturn(false);

    expect(fn () => $this->taskService->getTaskById(1, $userId))
        ->toThrow(TaskNotFoundException::class);
});

test('create task returns a new task', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'Test Description',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => '2023-12-31',
        'user_id' => $this->user->id,
    ];

    $task = Task::factory()->make($taskData + ['id' => 1]);

    $this->mockTaskRepository->expects($this->once())
        ->method('createTask')
        ->with($taskData)
        ->willReturn($task);

    $result = $this->taskService->createTask($taskData);

    expect($result)->toBeInstanceOf(Task::class);
    expect($result->title)->toBe('Test Task');
});

test('update task with user check returns updated task if it belongs to user', function () {
    $taskId = 1;
    $userId = $this->user->id;
    $taskData = [
        'title' => 'Updated Task',
        'description' => 'Updated Description',
    ];

    $task = Task::factory()->make($taskData + ['id' => $taskId]);

    $this->mockTaskRepository->expects($this->once())
        ->method('taskBelongsToUser')
        ->with($taskId, $userId)
        ->willReturn(true);

    $this->mockTaskRepository->expects($this->once())
        ->method('updateTask')
        ->with($taskId, $taskData)
        ->willReturn($task);

    $result = $this->taskService->updateTask($taskId, $taskData, $userId);

    expect($result)->toBeInstanceOf(Task::class);
    expect($result->title)->toBe('Updated Task');
});

test('update task with user check throws exception if task does not belong to user', function () {
    $taskId = 1;
    $userId = $this->user->id;
    $taskData = [
        'title' => 'Updated Task',
        'description' => 'Updated Description',
    ];

    $this->mockTaskRepository->expects($this->once())
        ->method('taskBelongsToUser')
        ->with($taskId, $userId)
        ->willReturn(false);

    expect(fn () => $this->taskService->updateTask($taskId, $taskData, $userId))
        ->toThrow(TaskNotFoundException::class);
});

test('handle completed status marks task as completed', function () {
    Carbon::setTestNow('2023-01-15 10:00:00');

    $taskData = [
        'title' => 'Test Task',
        'status' => TaskStatusEnum::PENDING->value,
    ];

    $result = $this->taskService->handleCompletedStatus($taskData, true);

    expect($result['status'])->toBe(TaskStatusEnum::COMPLETED->value);
    expect($result['completed_at']->toDateTimeString())->toBe('2023-01-15 10:00:00');

    $result = $this->taskService->handleCompletedStatus($taskData, false);

    expect($result['status'])->toBe(TaskStatusEnum::PENDING->value);
    expect($result['completed_at'])->toBeNull();

    Carbon::setTestNow();
});

test('delete task returns true on success', function () {
    $taskId = 1;

    $this->mockTaskRepository->expects($this->once())
        ->method('deleteTask')
        ->with($taskId)
        ->willReturn(true);

    $result = $this->taskService->deleteTask($taskId);

    expect($result)->toBeTrue();
});

test('delete task with user check returns true if task belongs to user', function () {
    $taskId = 1;
    $userId = $this->user->id;

    $this->mockTaskRepository->expects($this->once())
        ->method('taskBelongsToUser')
        ->with($taskId, $userId)
        ->willReturn(true);

    $this->mockTaskRepository->expects($this->once())
        ->method('deleteTask')
        ->with($taskId)
        ->willReturn(true);

    $result = $this->taskService->deleteTask($taskId, $userId);

    expect($result)->toBeTrue();
});

test('delete task with user check throws exception if task does not belong to user', function () {
    $taskId = 1;
    $userId = $this->user->id;

    $this->mockTaskRepository->expects($this->once())
        ->method('taskBelongsToUser')
        ->with($taskId, $userId)
        ->willReturn(false);

    expect(fn () => $this->taskService->deleteTask($taskId, $userId))
        ->toThrow(TaskNotFoundException::class);
});
