<?php

declare(strict_types=1);

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->taskRepository = new TaskRepository(new Task());
    $this->user = User::factory()->create();
});

test('get paginated tasks returns expected results', function () {
    Task::factory()->count(15)->user($this->user)->create([
        'status' => TaskStatusEnum::PENDING,
    ]);

    Task::factory()->count(5)->user($this->user)->create([
        'status' => TaskStatusEnum::IN_PROGRESS,
    ]);

    $otherUser = User::factory()->create();
    Task::factory()->count(10)->user($otherUser)->create();

    $result = $this->taskRepository->getPaginatedTasks();
    expect($result->total())->toBe(30);

    $result = $this->taskRepository->getPaginatedTasks([], null, 'desc', $this->user->id);
    expect($result->total())->toBe(20);

    $result = $this->taskRepository->getPaginatedTasks(['status' => 'pending'], null, 'desc', $this->user->id);
    expect($result->total())->toBe(15);

    $result = $this->taskRepository->getPaginatedTasks(['status' => 'in_progress'], null, 'desc', $this->user->id);
    expect($result->total())->toBe(5);

    $result = $this->taskRepository->getPaginatedTasks(['per_page' => 5]);
    expect($result->total())->toBe(30);
    expect($result->count())->toBe(5);
});

test('get all tasks returns all tasks', function () {
    Task::factory()->count(5)->user($this->user)->create();

    $result = $this->taskRepository->getAllTasks();

    expect($result->count())->toBe(5);
});

test('get task by id returns the task', function () {
    $task = Task::factory()->user($this->user)->create();

    $result = $this->taskRepository->getTaskById($task->id);

    expect($result->id)->toBe($task->id);
    expect($result->title)->toBe($task->title);
});

test('get task by id throws exception for non-existent task', function () {
    expect(fn () => $this->taskRepository->getTaskById(999))
        ->toThrow(TaskNotFoundException::class);
});

test('task belongs to user returns correct result', function () {
    $task = Task::factory()->user($this->user)->create();

    $otherUser = User::factory()->create();
    $otherTask = Task::factory()->user($otherUser)->create();

    $result = $this->taskRepository->taskBelongsToUser($task->id, $this->user->id);
    expect($result)->toBeTrue();

    $result = $this->taskRepository->taskBelongsToUser($otherTask->id, $this->user->id);
    expect($result)->toBeFalse();
});

test('create task adds a task to the database', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'Test Description',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => '2023-12-31',
        'user_id' => $this->user->id,
    ];

    $result = $this->taskRepository->createTask($taskData);

    expect($result->title)->toBe('Test Task');
    expect($result->description)->toBe('Test Description');
    expect($result->status)->toBe(TaskStatusEnum::PENDING);
    expect($result->priority)->toBe(TaskPriorityEnum::MEDIUM);
    expect($result->due_date->format('Y-m-d'))->toBe('2023-12-31');
    expect($result->user_id)->toBe($this->user->id);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'user_id' => $this->user->id,
    ]);
});

test('update task updates a task in the database', function () {
    $task = Task::factory()->user($this->user)->create([
        'title' => 'Original Title',
        'status' => TaskStatusEnum::PENDING,
    ]);

    $updateData = [
        'title' => 'Updated Title',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
    ];

    $result = $this->taskRepository->updateTask($task->id, $updateData);

    expect($result->title)->toBe('Updated Title');
    expect($result->status)->toBe(TaskStatusEnum::IN_PROGRESS);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
    ]);
});

test('update task throws exception for non-existent task', function () {
    $updateData = [
        'title' => 'Updated Title',
    ];

    expect(fn () => $this->taskRepository->updateTask(999, $updateData))
        ->toThrow(TaskNotFoundException::class);
});

test('delete task removes a task from the database', function () {
    $task = Task::factory()->user($this->user)->create();

    $result = $this->taskRepository->deleteTask($task->id);

    expect($result)->toBeTrue();

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

test('delete task throws exception for non-existent task', function () {
    expect(fn () => $this->taskRepository->deleteTask(999))
        ->toThrow(TaskNotFoundException::class);
});
