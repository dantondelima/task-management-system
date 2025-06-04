<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => ['sometimes', 'required', new Enum(TaskStatusEnum::class)],
            'priority' => ['sometimes', 'required', new Enum(TaskPriorityEnum::class)],
            'due_date' => 'sometimes|nullable|date',
            'completed_at' => 'sometimes|nullable|date',
            'user_id' => 'sometimes|required|exists:users,id',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim($this->input('title')),
            'description' => trim($this->input('description')),
            'user_id' => auth()->id(),
        ]);
    }
}
