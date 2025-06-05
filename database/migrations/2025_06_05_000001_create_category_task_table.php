<?php

declare(strict_types=1);

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
        Schema::create('category_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['category_id', 'task_id']);

            $table->index(['task_id', 'category_id']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_task');
    }
};
