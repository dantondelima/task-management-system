<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskServiceInterface;
use App\Exceptions\TaskNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    protected TaskServiceInterface $taskService;

    /**
     * TaskController constructor
     * 
     * @param TaskServiceInterface $taskService
     */
    public function __construct(TaskServiceInterface $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            // Get filters from request
            $filters = $request->only(['status', 'priority', 'completed', 'per_page']);
            
            // Get sort parameters
            $sortBy = $request->input('sort_by');
            $sortDirection = $request->input('sort_direction', 'desc');
            
            // Get current user ID
            $userId = auth()->id();
            
            // Get tasks with filters, sorting, and user-specific view
            $tasks = $this->taskService->getAllTasks($filters, $sortBy, $sortDirection, $userId);
            
            // Get available sort options for the view
            $sortOptions = [
                'created_at' => 'Created Date',
                'due_date' => 'Due Date',
                'priority' => 'Priority',
                'status' => 'Status',
                'title' => 'Title'
            ];
            
            return view('tasks.index', compact('tasks', 'sortOptions'));
        } catch (Exception $e) {
            Log::error('Error fetching tasks: ' . $e->getMessage(), [
                'exception' => $e,
                'filters' => $request->only(['status', 'priority', 'completed'])
            ]);
            
            return redirect()->route('home')
                ->with('error', 'An error occurred while fetching tasks. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        try {
            $users = $this->taskService->getAllUsers();
            return view('tasks.create', compact('users'));
        } catch (Exception $e) {
            Log::error('Error loading task creation form: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while loading the task creation form. Please try again later.');
        }
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param CreateTaskRequest $request
     * @return RedirectResponse
     */
    public function store(CreateTaskRequest $request): RedirectResponse
    {
        try {
            $taskData = $request->validated();
            
            // If user_id is not set, assign to current user
            if (!isset($taskData['user_id'])) {
                $taskData['user_id'] = auth()->id();
            }
            
            $task = $this->taskService->createTask($taskData);
            
            Log::info('Task created successfully', [
                'task_id' => $task->id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('tasks.index')
                ->with('success', 'Task created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $request->validated()
            ]);
            
            return redirect()->route('tasks.create')
                ->with('error', 'An error occurred while creating the task. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return View|RedirectResponse
     */
    public function show(int $id): View|RedirectResponse
    {
        try {
            // Only show task if it belongs to the current user
            $task = $this->taskService->getTaskById($id, auth()->id());
            return view('tasks.show', compact('task'));
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (Exception $e) {
            Log::error('Error showing task: ' . $e->getMessage(), [
                'exception' => $e,
                'task_id' => $id
            ]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while retrieving the task. Please try again later.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * @param int $id
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            // Only edit task if it belongs to the current user
            $task = $this->taskService->getTaskById($id, auth()->id());
            $users = $this->taskService->getAllUsers();
            
            return view('tasks.edit', compact('task', 'users'));
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (Exception $e) {
            Log::error('Error loading task edit form: ' . $e->getMessage(), [
                'exception' => $e,
                'task_id' => $id
            ]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while loading the task edit form. Please try again later.');
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param UpdateTaskRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateTaskRequest $request, int $id): RedirectResponse
    {
        try {
            $taskData = $request->validated();
            
            // Handle completed checkbox if present
            if ($request->has('completed')) {
                $taskData = $this->taskService->handleCompletedStatus($taskData, (bool)$request->completed);
            }
            
            // Only update task if it belongs to the current user
            $task = $this->taskService->updateTask($id, $taskData, auth()->id());
            
            Log::info('Task updated successfully', [
                'task_id' => $task->id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('tasks.show', $task->id)
                ->with('success', 'Task updated successfully.');
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage(), [
                'exception' => $e,
                'task_id' => $id,
                'data' => $request->validated()
            ]);
            
            return redirect()->route('tasks.edit', $id)
                ->with('error', 'An error occurred while updating the task. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            // Only delete task if it belongs to the current user
            $this->taskService->deleteTask($id, auth()->id());
            
            Log::info('Task deleted successfully', [
                'task_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('tasks.index')
                ->with('success', 'Task deleted successfully.');
        } catch (TaskNotFoundException $e) {
            Log::warning($e->getMessage(), ['task_id' => $id]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'Task not found.');
        } catch (Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage(), [
                'exception' => $e,
                'task_id' => $id
            ]);
            
            return redirect()->route('tasks.index')
                ->with('error', 'An error occurred while deleting the task. Please try again later.');
        }
    }
}
