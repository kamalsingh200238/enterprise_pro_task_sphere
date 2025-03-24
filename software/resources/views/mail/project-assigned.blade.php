{{-- WARN: do not indent this code, and do not change formatting of the code --}}
<x-mail::message>
# Project Assignment Notification

Hello {{ $user->name }},

You have been assigned a project.

Project name: **{{ $project->name }} ({{ $project->slug }})**

Please click the button below to view the project details.

<x-mail::button :url="url('/projects/' . $project->id)">
    View Project
</x-mail::button>

This is an automated message. Please do not reply directly to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
