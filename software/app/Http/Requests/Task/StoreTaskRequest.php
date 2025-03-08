<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status_id' => ['required', 'exists:statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'supervisor_id' => ['required', 'exists:users,id'],
            'assignees' => ['required', 'array', 'min:1'],
            'assignees.*' => ['exists:users,id'],
            'is_private' => ['boolean'],
            'viewers' => ['array', 'prohibited_unless:is_private,true'],
            'viewers.*' => ['exists:users,id'],
        ];
    }
}
