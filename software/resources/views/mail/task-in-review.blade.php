{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Task Review Required

Hello {{ $user->name }},

The task **{{ $task->name }} ({{ $task->slug }})** has been moved to **In Review**.

Please review the task at your earliest convenience.

Once you're done, you can either move it to **Done** or back to **In Progress** if more work is needed.

<x-mail::button :url="url('/tasks/' . $task->id)">
    Review Task
</x-mail::button>

This is an automated message. Please do not reply directly to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
