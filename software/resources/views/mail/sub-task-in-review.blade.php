{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Sub-Task Review Required

Hello {{ $user->name }},

The sub-task **{{ $subTask->name }} ({{ $subTask->slug }})** has been moved to **In Review**.

Please review the sub-task at your earliest convenience.

Once you're done, you can either move it to **Done** or back to **In Progress** if more work is needed.

<x-mail::button :url="url('/sub-tasks/' . $subTask->id)">
    Review Sub-Task
</x-mail::button>

This is an automated message. Please do not reply directly to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
