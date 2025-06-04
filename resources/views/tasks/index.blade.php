@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('My Tasks') }}</span>
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">Create New Task</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filters and Sorting -->
                    <form action="{{ route('tasks.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <!-- Filters -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Filters</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <!-- Status Filter -->
                                            <div class="col-md-4">
                                                <label for="status" class="form-label">Status</label>
                                                <select name="status" id="status" class="form-select">
                                                    <option value="">All Statuses</option>
                                                    @foreach(App\Enums\TaskStatusEnum::cases() as $status)
                                                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- Priority Filter -->
                                            <div class="col-md-4">
                                                <label for="priority" class="form-label">Priority</label>
                                                <select name="priority" id="priority" class="form-select">
                                                    <option value="">All Priorities</option>
                                                    @foreach(App\Enums\TaskPriorityEnum::cases() as $priority)
                                                        <option value="{{ $priority->value }}" {{ request('priority') == $priority->value ? 'selected' : '' }}>{{ $priority->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sorting Options -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Sorting</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="sort_by" class="form-label">Sort By</label>
                                            <select name="sort_by" id="sort_by" class="form-select">
                                                <option value="">Default (Created Date)</option>
                                                @foreach($sortOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ request('sort_by') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sort_direction" class="form-label">Direction</label>
                                            <select name="sort_direction" id="sort_direction" class="form-select">
                                                <option value="desc" {{ request('sort_direction', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                                                <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Per Page and Filter Buttons -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label for="per_page" class="form-label me-2">Items per page:</label>
                                        <select name="per_page" id="per_page" class="form-select form-select-sm d-inline-block" style="width: auto;">
                                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tasks Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks as $task)
                                    <tr>
                                        <td>{{ $task->title }}</td>
                                        <td>
                                            @switch($task->status->value)
                                                @case('pending')
                                                    <span class="badge bg-warning text-dark">{{ $task->status->label() }}</span>
                                                    @break
                                                @case('in_progress')
                                                    <span class="badge bg-info text-dark">{{ $task->status->label() }}</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">{{ $task->status->label() }}</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $task->status->label() }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @switch($task->priority->value)
                                                @case('low')
                                                    <span class="badge bg-success">{{ $task->priority->label() }}</span>
                                                    @break
                                                @case('medium')
                                                    <span class="badge bg-warning text-dark">{{ $task->priority->label() }}</span>
                                                    @break
                                                @case('high')
                                                    <span class="badge bg-danger">{{ $task->priority->label() }}</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $task->priority->label() }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-info text-white">View</a>
                                                <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No tasks found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $tasks->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 