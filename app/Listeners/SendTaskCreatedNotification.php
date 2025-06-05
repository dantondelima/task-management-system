<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Notifications\TaskCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskCreatedNotification implements ShouldQueue
{
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;
        $user = $task->user;

        $task->load('categories');

        if ($user) {
            $user->notify(new TaskCreatedNotification($task));
        }
    }
}
