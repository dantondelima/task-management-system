<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('New Task: '.$this->task->title)
            ->line('A new task has been created.')
            ->line('Title: '.$this->task->title)
            ->line('Description: '.$this->task->description);

        if ($this->task->categories->isNotEmpty()) {
            $categories = $this->task->categories->pluck('name')->join(', ');
            $mailMessage->line('Categories: '.$categories);
        }

        return $mailMessage
            ->action('View Task', route('tasks.show', $this->task->id))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $data = [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'description' => $this->task->description,
        ];

        if ($this->task->categories->isNotEmpty()) {
            $data['categories'] = $this->task->categories->pluck('name')->toArray();
        }

        return $data;
    }
}
