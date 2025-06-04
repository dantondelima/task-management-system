<?php

namespace App\Providers;

use App\Repositories\TaskRepository;
use App\Repositories\TaskRepositoryInterface;
use App\Services\TaskService;
use App\Services\TaskServiceInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 