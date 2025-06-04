@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Task Details') }}</span>
                    <div>
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary btn-sm me-2">Edit Task</a>
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">Back to Tasks</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h2>{{ $task->title }}</h2>
                        
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <span class="fw-bold">Status:</span>
                                @switch($task->status->value)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @break
                                    @case('in_progress')
                                        <span class="badge bg-info text-dark">In Progress</span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-success">Completed</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $task->status->value }}</span>
                                @endswitch
                            </div>
                            
                            <div class="me-3">
                                <span class="fw-bold">Priority:</span>
                                @switch($task->priority->value)
                                    @case('low')
                                        <span class="badge bg-success">Low</span>
                                        @break
                                    @case('medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                        @break
                                    @case('high')
                                        <span class="badge bg-danger">High</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $task->priority->value }}</span>
                                @endswitch
                            </div>
                            
                            <div>
                                <span class="fw-bold">Due Date:</span>
                                <span>{{ $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            Description
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $task->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    Assignment Details
                                </div>
                                <div class="card-body">
                                    <p><strong>Assigned To:</strong> {{ $task->user->name ?? 'Unassigned' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    Status
                                </div>
                                <div class="card-body">
                                    <p><strong>Status:</strong> {{ $task->status->label() }}</p>
                                    @if($task->status->value === 'completed' && $task->completed_at)
                                        <p><strong>Completed at:</strong> {{ $task->completed_at->format('Y-m-d H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Created: {{ $task->created_at->format('Y-m-d H:i') }}</small><br>
                            <small class="text-muted">Last Updated: {{ $task->updated_at->format('Y-m-d H:i') }}</small>
                        </div>
                        
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Task</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 