<?php

declare(strict_types=1);

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('create task request validates correctly', function () {
    $request = new CreateTaskRequest();

    $validData = [
        'title' => 'Test Task',
        'description' => 'Test description',
        'status' => TaskStatusEnum::PENDING->value,
        'priority' => TaskPriorityEnum::MEDIUM->value,
        'due_date' => now()->addDays(5)->format('Y-m-d'),
        'user_id' => $this->user->id,
    ];

    $validator = Validator::make($validData, $request->rules());
    expect($validator->passes())->toBeTrue();

    $requiredFields = ['title', 'status', 'priority', 'due_date'];
    foreach ($requiredFields as $field) {
        $invalidData = $validData;
        unset($invalidData[$field]);

        $validator = Validator::make($invalidData, $request->rules());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->toArray())->toHaveKey($field);
    }

    $invalidData = $validData;
    $invalidData['status'] = 'invalid_status';

    $validator = Validator::make($invalidData, $request->rules());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->toArray())->toHaveKey('status');

    $invalidData = $validData;
    $invalidData['priority'] = 'invalid_priority';

    $validator = Validator::make($invalidData, $request->rules());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->toArray())->toHaveKey('priority');

    $invalidData = $validData;
    $invalidData['due_date'] = 'not-a-date';

    $validator = Validator::make($invalidData, $request->rules());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->toArray())->toHaveKey('due_date');
});

test('update task request validates correctly', function () {
    $request = new UpdateTaskRequest();

    $validData = [
        'title' => 'Updated Task',
        'description' => 'Updated description',
        'status' => TaskStatusEnum::IN_PROGRESS->value,
        'priority' => TaskPriorityEnum::HIGH->value,
        'due_date' => now()->addDays(10)->format('Y-m-d'),
    ];

    $validator = Validator::make($validData, $request->rules());
    expect($validator->passes())->toBeTrue();

    $invalidData = $validData;
    unset($invalidData['title']);

    $optionalFields = ['description', 'status', 'priority', 'due_date'];
    foreach ($optionalFields as $field) {
        $partialData = ['title' => 'Updated Task'];

        $validator = Validator::make($partialData, $request->rules());
        expect($validator->passes())->toBeTrue();
    }

    $invalidData = $validData;
    $invalidData['status'] = 'invalid_status';

    $validator = Validator::make($invalidData, $request->rules());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->toArray())->toHaveKey('status');

    $invalidData = $validData;
    $invalidData['priority'] = 'invalid_priority';

    $validator = Validator::make($invalidData, $request->rules());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->toArray())->toHaveKey('priority');

    $invalidData = $validData;
    $invalidData['due_date'] = 'not-a-date';

    $validator = Validator::make($invalidData, $request->rules());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->toArray())->toHaveKey('due_date');
});
