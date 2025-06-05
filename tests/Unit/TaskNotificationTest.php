<?php

declare(strict_types=1);

use App\Events\TaskCreated;
use App\Listeners\SendTaskCreatedNotification;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->task = Task::factory()->user($this->user)->create([
        'title' => 'Test Task',
        'description' => 'This is a test task',
    ]);

    $this->categories = Category::factory()->count(2)->create()->each(function ($category, $index) {
        $category->name = 'Category '.($index + 1);
        $category->save();
    });
});

test('task created notification includes task details', function () {
    $notification = new TaskCreatedNotification($this->task);

    $mailData = $notification->toMail($this->user);
    $arrayData = $notification->toArray($this->user);

    expect($mailData->subject)->toBe('New Task: Test Task');

    expect($arrayData)->toHaveKey('task_id');
    expect($arrayData)->toHaveKey('title');
    expect($arrayData['title'])->toBe('Test Task');
    expect($arrayData['description'])->toBe('This is a test task');
});

test('task created notification includes category information', function () {
    $this->task->categories()->attach($this->categories->pluck('id'));

    $notification = new TaskCreatedNotification($this->task);

    $mailData = $notification->toMail($this->user);
    $arrayData = $notification->toArray($this->user);

    expect($arrayData)->toHaveKey('categories');
    expect($arrayData['categories'])->toContain('Category 1');
    expect($arrayData['categories'])->toContain('Category 2');
});

test('task created listener handles event correctly', function () {
    Notification::fake();

    $listener = new SendTaskCreatedNotification();
    $event = new TaskCreated($this->task);

    $listener->handle($event);

    Notification::assertSentTo(
        $this->user,
        TaskCreatedNotification::class
    );
});
