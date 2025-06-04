<?php

declare(strict_types=1);

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 192);
            $table->text('description');
            $table->string('status', 32)->default(TaskStatusEnum::PENDING->value);
            $table->string('priority', 32)->default(TaskPriorityEnum::LOW->value);
            $table->date('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['status', 'priority'], 'tasks_status_priority_index');
            $table->index(['user_id', 'status'], 'tasks_user_status_index');
            $table->index(['user_id', 'created_at'], 'tasks_user_recent_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
