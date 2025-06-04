<?php

declare(strict_types=1);

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->user = $user;
});

test('task creation requires a title', function () {
    $taskData = [
        'description' => 'Test description',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => now()->addDays(7)->format('Y-m-d'),
    ];
    
    $response = $this->post(route('tasks.store'), $taskData);
    
    $response->assertSessionHasErrors(['title']);
    
    $this->assertDatabaseCount('tasks', 0);
});

test('task creation requires a valid status', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'Test description',
        'status' => 'invalid_status',
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => now()->addDays(7)->format('Y-m-d'),
    ];
    
    $response = $this->post(route('tasks.store'), $taskData);
    
    $response->assertSessionHasErrors(['status']);
    
    $this->assertDatabaseCount('tasks', 0);
});

test('task creation requires a valid priority', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'Test description',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => 'invalid_priority',
        'due_date' => now()->addDays(7)->format('Y-m-d'),
    ];
    
    $response = $this->post(route('tasks.store'), $taskData);
    
    $response->assertSessionHasErrors(['priority']);
    
    $this->assertDatabaseCount('tasks', 0);
});

test('task creation requires a valid date format', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'Test description',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => 'invalid-date-format',
    ];
    
    $response = $this->post(route('tasks.store'), $taskData);
    
    $response->assertSessionHasErrors(['due_date']);
    
    $this->assertDatabaseCount('tasks', 0);
});

test('task update requires a title', function () {
    $task = Task::factory()->user($this->user)->create();
    $originalTitle = $task->title;
    
    $updateData = [
        'title' => '',
        'description' => 'Updated description',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
        'priority' => TaskPriorityEnum::HIGH->value,
        'due_date' => now()->addDays(14)->format('Y-m-d'),
    ];
    
    $response = $this->put(route('tasks.update', $task->id), $updateData);
    
    $response->assertSessionHasErrors(['title']);
    
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => $originalTitle
    ]);
});

test('task update requires a valid status', function () {
    $task = Task::factory()->user($this->user)->create();
    $originalStatus = $task->status;
    
    $updateData = [
        'title' => 'Updated Task',
        'description' => 'Updated description',
        'status' => 'invalid_status',
        'priority' => TaskPriorityEnum::HIGH->value,
        'due_date' => now()->addDays(14)->format('Y-m-d'),
    ];
    
    $response = $this->put(route('tasks.update', $task->id), $updateData);
    
    $response->assertSessionHasErrors(['status']);
    
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => $originalStatus->value
    ]);
});

test('task update requires a valid priority', function () {
    $task = Task::factory()->user($this->user)->create();
    $originalPriority = $task->priority;
    
    $updateData = [
        'title' => 'Updated Task',
        'description' => 'Updated description',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
        'priority' => 'invalid_priority',
        'due_date' => now()->addDays(14)->format('Y-m-d'),
    ];
    
    $response = $this->put(route('tasks.update', $task->id), $updateData);
    
    $response->assertSessionHasErrors(['priority']);
    
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'priority' => $originalPriority->value
    ]);
});

test('task update requires a valid date format', function () {
    $task = Task::factory()->user($this->user)->create();
    $originalDueDate = $task->due_date->format('Y-m-d');
    
    $updateData = [
        'title' => 'Updated Task',
        'description' => 'Updated description',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
        'priority' => TaskPriorityEnum::HIGH->value,
        'due_date' => 'invalid-date-format',
    ];
    
    $response = $this->put(route('tasks.update', $task->id), $updateData);
    
    $response->assertSessionHasErrors(['due_date']);
    
    $task->refresh();
    $this->assertEquals($originalDueDate, $task->due_date->format('Y-m-d'));
});

test('validation error messages appear in the UI when creating a task', function () {
    $response = $this->actingAs($this->user)
        ->from(route('tasks.create'))
        ->followingRedirects()
        ->post(route('tasks.store'), ['title' => '']);

    $response->assertSee('The title field is required');
});

test('validation error messages appear in the UI when updating a task', function () {
    $task = Task::factory()->user($this->user)->create();
    
    $response = $this->actingAs($this->user)
        ->from(route('tasks.create'))
        ->followingRedirects()
        ->put(route('tasks.update', $task->id), ['title' => '']);

    $response->assertSee('The title field is required');
});