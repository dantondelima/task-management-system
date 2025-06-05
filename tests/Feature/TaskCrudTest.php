<?php

declare(strict_types=1);

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->user = $user;
    $this->otherUser = $otherUser;
    $this->categories = Category::factory()->count(3)->create();
});

test('user can view a paginated list of their tasks', function () {
    Task::factory()->count(15)->user($this->user)->create();

    Task::factory()->count(5)->user($this->otherUser)->create();

    $response = $this->actingAs($this->user)->get(route('tasks.index'));

    $response->assertStatus(200);
    $response->assertViewIs('tasks.index');

    $response->assertViewHas('tasks', function ($tasks) {
        return $tasks->total() === 15 && $tasks->count() === 10;
    });

    $response->assertDontSee(
        Task::where('user_id', $this->otherUser->id)->first()->title
    );
});

test('user can filter tasks by status', function () {
    Task::factory()->count(5)->user($this->user)->create([
        'status' => TaskStatusEnum::PENDING,
    ]);
    Task::factory()->count(3)->user($this->user)->create([
        'status' => TaskStatusEnum::IN_PROGRESS,
    ]);

    $response = $this->actingAs($this->user)->get(route('tasks.index', ['status' => 'in_progress']));

    $response->assertStatus(200);
    $response->assertViewHas('tasks', function ($tasks) {
        return $tasks->total() === 3;
    });
});

test('user can view a single task that belongs to them', function () {
    $task = Task::factory()->user($this->user)->create();
    $task->categories()->attach($this->categories->first()->id);

    $response = $this->actingAs($this->user)->get(route('tasks.show', $task->id));

    $response->assertStatus(200);
    $response->assertViewIs('tasks.show');
    $response->assertViewHas('task', function ($viewTask) use ($task) {
        return $viewTask->id === $task->id;
    });
    $response->assertSee($task->title);
    $response->assertSee($task->description);
    $response->assertSee($this->categories->first()->name);
});

test('user cannot view a task that belongs to another user', function () {
    $task = Task::factory()->user($this->otherUser)->create();

    $response = $this->actingAs($this->user)->get(route('tasks.show', $task->id));

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHas('error', 'Task not found.');
});

test('user can create a task with valid data', function () {
    $taskData = [
        'title' => 'New Test Task',
        'description' => 'This is a test task description',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => now()->addWeek()->format('Y-m-d'),
    ];

    $response = $this->actingAs($this->user)->post(route('tasks.store'), $taskData);

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHas('success', 'Task created successfully.');

    $this->assertDatabaseHas('tasks', [
        'title' => 'New Test Task',
        'user_id' => $this->user->id,
    ]);
});

test('user can create a task with categories', function () {
    $categoryIds = $this->categories->pluck('id')->toArray();

    $taskData = [
        'title' => 'New Test Task with Categories',
        'description' => 'This is a test task description with categories',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => now()->addWeek()->format('Y-m-d'),
        'categories' => $categoryIds,
    ];

    $response = $this->actingAs($this->user)->post(route('tasks.store'), $taskData);

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHas('success', 'Task created successfully.');

    $task = Task::where('title', 'New Test Task with Categories')->first();

    $this->assertDatabaseHas('tasks', [
        'title' => 'New Test Task with Categories',
        'user_id' => $this->user->id,
    ]);

    foreach ($categoryIds as $categoryId) {
        $this->assertDatabaseHas('category_task', [
            'task_id' => $task->id,
            'category_id' => $categoryId,
        ]);
    }
});

test('user cannot create a task with invalid data', function () {
    $taskData = [
        'title' => '',
        'description' => 'This is a test task description',
    ];

    $response = $this->actingAs($this->user)->post(route('tasks.store'), $taskData);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['title']);

    $this->assertDatabaseMissing('tasks', [
        'description' => 'This is a test task description',
        'user_id' => $this->user->id,
    ]);
});

test('user can update their task with valid data', function () {
    $task = Task::factory()->user($this->user)->create([
        'status' => TaskStatusEnum::PENDING,
    ]);

    $updateData = [
        'title' => 'Updated Task Title',
        'description' => 'Updated task description',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
        'priority' => TaskPriorityEnum::HIGH->value,
        'due_date' => now()->addWeeks(2)->format('Y-m-d'),
    ];

    $response = $this->actingAs($this->user)->put(route('tasks.update', $task->id), $updateData);

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.show', $task->id));
    $response->assertSessionHas('success', 'Task updated successfully.');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
    ]);
});

test('user can update their task with categories', function () {
    $task = Task::factory()->user($this->user)->create([
        'status' => TaskStatusEnum::PENDING,
    ]);

    $categoryIds = $this->categories->pluck('id')->toArray();

    $updateData = [
        'title' => 'Updated Task with Categories',
        'description' => 'Updated task description with categories',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
        'priority' => TaskPriorityEnum::HIGH->value,
        'due_date' => now()->addWeeks(2)->format('Y-m-d'),
        'categories' => $categoryIds,
    ];

    $response = $this->actingAs($this->user)->put(route('tasks.update', $task->id), $updateData);

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.show', $task->id));
    $response->assertSessionHas('success', 'Task updated successfully.');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task with Categories',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
    ]);

    foreach ($categoryIds as $categoryId) {
        $this->assertDatabaseHas('category_task', [
            'task_id' => $task->id,
            'category_id' => $categoryId,
        ]);
    }
});

test('user cannot update a task with invalid data', function () {
    $task = Task::factory()->user($this->user)->create();
    $originalTitle = $task->title;

    $updateData = [
        'title' => '',
        'description' => 'Updated task description',
    ];

    $response = $this->actingAs($this->user)->put(route('tasks.update', $task->id), $updateData);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['title']);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => $originalTitle,
    ]);
});

test('user cannot update a task that belongs to another user', function () {
    $task = Task::factory()->user($this->otherUser)->create();

    $updateData = [
        'title' => 'Updated Task Title',
        'description' => 'Updated task description',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
        'priority' => TaskPriorityEnum::HIGH->value,
        'due_date' => now()->addWeeks(2)->format('Y-m-d'),
    ];

    $response = $this->actingAs($this->user)->put(route('tasks.update', $task->id), $updateData);

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHas('error', 'Task not found.');

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
    ]);
});

test('user can mark their task as completed', function () {
    $task = Task::factory()->user($this->user)->create([
        'status' => TaskStatusEnum::PENDING,
        'completed_at' => null,
    ]);

    $updateData = [
        'title' => $task->title,
        'description' => $task->description,
        'status' => $task->status->value,
        'priority' => $task->priority->value,
        'due_date' => $task->due_date->format('Y-m-d'),
        'completed' => true,
    ];

    $response = $this->actingAs($this->user)->put(route('tasks.update', $task->id), $updateData);

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.show', $task->id));

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => TaskStatusEnum::COMPLETED->value,
    ]);

    $task->refresh();
    $this->assertNotNull($task->completed_at);
});

test('user can delete their task', function () {
    $task = Task::factory()->user($this->user)->create();
    $task->categories()->attach($this->categories->first()->id);

    $response = $this->actingAs($this->user)->delete(route('tasks.destroy', $task->id));

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHas('success', 'Task deleted successfully.');

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    $this->assertDatabaseMissing('category_task', ['task_id' => $task->id]);
});

test('user cannot delete a task that belongs to another user', function () {
    $task = Task::factory()->user($this->otherUser)->create();

    $response = $this->actingAs($this->user)->delete(route('tasks.destroy', $task->id));

    $response->assertStatus(302);
    $response->assertRedirect(route('tasks.index'));
    $response->assertSessionHas('error', 'Task not found.');

    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

test('unauthenticated users cannot access task routes', function ($route) {
    $task = Task::factory()->user($this->user)->create();
    auth()->logout();

    $this->get($route)->assertRedirect(route('login'));
})->with([
    fn () => route('tasks.index'),
    fn () => route('tasks.create'),
    fn () => route('tasks.show', Task::factory()->user($this->otherUser)->create()->id),
    fn () => route('tasks.edit', Task::factory()->user($this->otherUser)->create()->id),
]);

test('multiple tasks can be created with factories', function () {
    $pendingTasks = Task::factory()->count(3)->user($this->user)->create([
        'status' => TaskStatusEnum::PENDING,
    ]);

    $inProgressTasks = Task::factory()->count(2)->user($this->user)->create([
        'status' => TaskStatusEnum::IN_PROGRESS,
    ]);

    $completedTasks = Task::factory()->count(4)->user($this->user)->create([
        'status' => TaskStatusEnum::COMPLETED,
        'completed_at' => now(),
    ]);

    $this->assertDatabaseCount('tasks', 9);

    $this->assertEquals(3, Task::where('status', TaskStatusEnum::PENDING)->count());
    $this->assertEquals(2, Task::where('status', TaskStatusEnum::IN_PROGRESS)->count());
    $this->assertEquals(4, Task::where('status', TaskStatusEnum::COMPLETED)->count());

    $this->assertEquals(9, Task::where('user_id', $this->user->id)->count());
});
