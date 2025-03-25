{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Sub-Task Viewer Assignment Notification

Hello {{ $user->name }},

You have been added as a viewer to a sub-task.

Sub-Task name: **{{ $subTask->name }} ({{ $subTask->slug }})**

Please click the button below to view the sub-task details.

<x-mail::button :url="url('/sub-tasks/' . $subTask->id)">
    View Sub-Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
