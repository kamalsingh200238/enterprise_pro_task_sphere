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

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'Selected project does not exist.',

            'name.required' => 'Task name is required.',
            'name.max' => 'Task name may not be greater than 255 characters.',

            'description.required' => 'Task description is required.',

            'start_date.required' => 'Start date is required.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be after or equal to the start date.',

            'status_id.required' => 'Task status is required.',
            'status_id.exists' => 'Selected status does not exist.',

            'priority_id.required' => 'Priority is required.',
            'priority_id.exists' => 'Selected priority does not exist.',

            'supervisor_id.required' => 'Supervisor is required.',
            'supervisor_id.exists' => 'Selected supervisor does not exist.',

            'assignees.required' => 'Please assign at least one user.',
            'assignees.*.exists' => 'One or more selected assignees are invalid.',

            'viewers.prohibited_unless' => 'Viewers can only be set if the task is private.',
            'viewers.*.exists' => 'One or more selected viewers are invalid.',
        ];
    }
}
