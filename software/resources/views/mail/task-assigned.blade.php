{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Task Assignment Notification

Hello {{ $user->name }},

You have been assigned a task.

Task name: **{{ $task->name }} ({{ $task->slug }})**

Please click the button below to view the task details.

<x-mail::button :url="url('/tasks/' . $task->id)">
    View Task
</x-mail::button>

This is an automated message. Please do not reply directly to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
