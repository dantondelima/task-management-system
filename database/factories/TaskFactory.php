<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(TaskStatusEnum::cases()),
            'priority' => fake()->randomElement(TaskPriorityEnum::cases()),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }

    public function user(User $user): Factory
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }
}
