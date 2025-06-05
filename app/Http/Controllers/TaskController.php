<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\TaskNotFoundException;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\User;
use App\Services\TaskServiceInterface;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

class TaskController extends Controller
{
    protected TaskServiceInterface $taskService;

    /**
     * TaskController constructor
     */
    public function __construct(TaskServiceInterface $taskService)
    {
        $this->taskService = $taskService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $filters = $request->only(['status', 'priority', 'completed', 'per_page', 'category_id']);

            $sortBy = $request->input('sort_by');
            $sortDirection = $request->input('sort_direction', 'desc');

            $userId = auth()->id();

            $tasks = $this->taskService->getAllTasks($filters, $sortBy, $sortDirection, $userId);
            $categories = $this->taskService->getAllCategories($userId);

            $sortOptions = [
                'created_at' => 'Created Date',
                'due_date' => 'Due Date',
                'priority' => 'Priority',
                'status' => 'Status',
                'title' => 'Title',
            ];

            return view('tasks.index', compact('tasks', 'sortOptions', 'categories'));
        } catch (Exception $e) {
            Log::error('Error fetching tasks: '.$e->getMessage(), [
                'exception' => $e,
                'filters' => $request->only(['status', 'priority', 'completed', 'category_id']),
            ]);

            return redirect()->route('home')
                ->with('error', 'An error occurred while fetching tasks. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|RedirectResponse
    {
        try {
            $userId = auth()->id();
            $users = $this->taskService->getAllUsers();
            $categories = $this->taskService->getAllCategories($userId);

            return view('tasks.create', compact('users', 'categories'));
        } catch (Exception $e) {
            Log::error('Error loading task creation form: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while loading the task creation form. Please try again later.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTaskRequest $request): RedirectResponse
    {
        try {
            $taskData = $request->validated();

            // If user_id is not set, assign to current user
            if (! isset($taskData['user_id'])) {
                $taskData['user_id'] = auth()->id();
            }

            $task = $this->taskService->createTask($taskData);

            Log::info('Task created successfully', [
                'task_id' => $task->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('tasks.index')
                ->with('success', 'Task created successfully.');
        } catch (InvalidArgumentException $e) {
            Log::warning('Invalid category assignment: '.$e->getMessage(), [
                'data' => $request->validated(),
            ]);

            return redirect()->route('tasks.create')
                ->with('error', 'Cannot assign categories that do not belong to you.')
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error creating task: '.$e->getMessage(), [
                'exception' => $e,
                'data' => $request->validated(),
            ]);

            return redirect()->route('tasks.create')
                ->with('error', 'An error occurred while creating the task. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View|RedirectResponse
    {
        try {
            $userId = auth()->id();
            $task = $this->taskService->getTaskById($id, $userId);

            return view('tasks.show', compact('task'));
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);

            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (Exception $e) {
            Log::error('Error showing task: '.$e->getMessage(), [
                'exception' => $e,
                'task_id' => $id,
            ]);

            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while retrieving the task. Please try again later.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $userId = auth()->id();
            $task = $this->taskService->getTaskById($id, $userId);
            $users = $this->taskService->getAllUsers();
            $categories = $this->taskService->getAllCategories($userId);

            return view('tasks.edit', compact('task', 'users', 'categories'));
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);

            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (Exception $e) {
            Log::error('Error loading task edit form: '.$e->getMessage(), [
                'exception' => $e,
                'task_id' => $id,
            ]);

            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while loading the task edit form. Please try again later.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, int $id): RedirectResponse
    {
        try {
            $taskData = $request->validated();

            if ($request->has('completed')) {
                $taskData = $this->taskService->handleCompletedStatus($taskData, (bool) $request->completed);
            }

            $task = $this->taskService->updateTask($id, $taskData, auth()->id());

            Log::info('Task updated successfully', [
                'task_id' => $task->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('tasks.show', $task->id)
                ->with('success', 'Task updated successfully.');
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);

            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (InvalidArgumentException $e) {
            Log::warning('Invalid category assignment: '.$e->getMessage(), [
                'task_id' => $id,
                'data' => $request->validated(),
            ]);

            return redirect()->route('tasks.edit', $id)
                ->with('error', 'Cannot assign categories that do not belong to you.')
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error updating task: '.$e->getMessage(), [
                'exception' => $e,
                'task_id' => $id,
                'data' => $request->validated(),
            ]);

            return redirect()->route('tasks.edit', $id)
                ->with('error', 'An error occurred while updating the task. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->taskService->deleteTask($id, auth()->id());

            Log::info('Task deleted successfully', [
                'task_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('tasks.index')
                ->with('success', 'Task deleted successfully.');
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);

            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (Exception $e) {
            Log::error('Error deleting task: '.$e->getMessage(), [
                'exception' => $e,
                'task_id' => $id,
            ]);

            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while deleting the task. Please try again.');
        }
    }
}
