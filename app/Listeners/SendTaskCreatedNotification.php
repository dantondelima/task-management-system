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
        $user = $event->task->user;

        if ($user) {
            $user->notify(new TaskCreatedNotification($event->task));
        }
    }
}
