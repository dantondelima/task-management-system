<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Enums\TaskStatusEnum;
use App\Enums\TaskPriorityEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'title' => fake()->title(),
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
