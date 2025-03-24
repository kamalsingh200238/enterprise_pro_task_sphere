{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Task Viewer Assignment Notification

Hello {{ $user->name }},

You have been added as a viewer to a task.

Task name: **{{ $task->name }} ({{ $task->slug }})**

Please click the button below to view the task details.

<x-mail::button :url="url('/tasks/' . $task->id)">
    View Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
