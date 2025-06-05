<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and categories
        $users = User::all();
        $categories = Category::all();

        // Create 5 tasks for each user with random categories
        foreach ($users as $user) {
            // Create tasks with different statuses and priorities
            $this->createTask($user, 'Complete project report', 'Detailed description of the project progress', TaskStatusEnum::IN_PROGRESS, TaskPriorityEnum::HIGH, $categories->random(2));
            $this->createTask($user, 'Schedule team meeting', 'Discuss upcoming deadlines and project status', TaskStatusEnum::PENDING, TaskPriorityEnum::MEDIUM, $categories->random(1));
            $this->createTask($user, 'Review client feedback', 'Go through client comments and prepare responses', TaskStatusEnum::COMPLETED, TaskPriorityEnum::MEDIUM, $categories->random(2));
            $this->createTask($user, 'Update website content', 'Refresh the content on the homepage and about page', TaskStatusEnum::PENDING, TaskPriorityEnum::LOW, $categories->random(1));
            $this->createTask($user, 'Prepare quarterly presentation', 'Create slides for the quarterly review meeting', TaskStatusEnum::IN_PROGRESS, TaskPriorityEnum::URGENT, $categories->random(2));
        }
    }

    /**
     * Create a task with the given parameters
     */
    private function createTask(User $user, string $title, string $description, TaskStatusEnum $status, TaskPriorityEnum $priority, $categories): void
    {
        $dueDate = now()->addDays(rand(1, 30));
        $completedAt = $status === TaskStatusEnum::COMPLETED ? now()->subDays(rand(1, 5)) : null;

        $task = Task::create([
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate,
            'completed_at' => $completedAt,
            'user_id' => $user->id,
        ]);

        // Attach categories to the task
        $task->categories()->attach($categories);
    }
} 