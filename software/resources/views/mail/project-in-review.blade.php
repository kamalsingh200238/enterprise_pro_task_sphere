{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Project Review Required

Hello {{ $user->name }},

The project **{{ $project->name }} ({{ $project->slug }})** has been moved to **In Review**.

Please review the project at your earliest convenience.

Once you're done, you can either move it to **Done** or back to **In Progress** if more work is needed.

<x-mail::button :url="url('/projects/' . $project->id)">
    Review Project
</x-mail::button>

This is an automated message. Please do not reply directly to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
