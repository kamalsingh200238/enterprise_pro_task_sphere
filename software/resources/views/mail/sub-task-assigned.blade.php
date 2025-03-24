{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Sub-Task Assignment Notification

Hello {{ $user->name }},

You have been assigned a sub-task.

Sub-Task name: **{{ $subTask->name }} ({{ $subTask->slug }})**

Please click the button below to view the task details.

<x-mail::button :url="url('/sub-tasks/' . $subTask->id)">
    View Sub-Task
</x-mail::button>

This is an automated message. Please do not reply directly to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
