<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use Validator;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view-logs');
        $validator = Validator::make($request->all(), [
            'startDate' => ['required', Rule::date()],
            'endDate' => ['required', Rule::date()->afterOrEqual('startDate')],
        ]);

        $startDate = null;
        $endDate = null;
        $perPage = $request->input('perPage', 10);

        if ($validator->fails()) {
            $startDate = Carbon::now()->setTimezone('Europe/London')->startOfDay()->subDays(7)->utc();
            $endDate = Carbon::now()->setTimezone('Europe/London')->endOfDay()->utc();
        } else {
            $validated = $validator->validate();
            $startDate = Carbon::parse($validated['startDate']);
            $endDate = Carbon::parse($validated['endDate']);
        }

        // Fetch paginated activity logs within the date range
        $logs = Activity::with(['causer', 'subject'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $logs->getCollection()->transform(fn ($log) => $this->formatLog($log));

        // Return view with logs and date range
        return Inertia::render('logs/ShowLogs', [
            'logs' => $logs,
            'startDate' => $startDate->toIso8601ZuluString(),
            'endDate' => $endDate->toIso8601ZuluString(),
        ]);
    }

    public function exportLogs(Request $request)
    {
        Gate::authorize('export-logs');
        $validator = Validator::make($request->all(), [
            'startDate' => ['required', Rule::date()],
            'endDate' => ['required', Rule::date()->afterOrEqual('startDate')],
        ]);

        $validated = $validator->validate();
        $startDate = Carbon::parse($validated['startDate']);
        $endDate = Carbon::parse($validated['endDate']);

        // Fetch paginated activity logs within the date range
        $logs = Activity::with(['causer', 'subject'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($log) => $this->formatLog($log));

        $pdf = PDF::loadView('pdf.activity-logs', ['logs' => $logs])->setPaper('a4', 'landscape');

        return $pdf->download('logs.pdf');
    }

    private function formatLog($activityLog)
    {
        return [
            'id' => $activityLog->id,
            'timestamp' => Carbon::parse($activityLog->created_at)->timezone('Europe/London')->format('d M, Y H:i:s'),
            'causer' => ['name' => $activityLog->causer->name, 'email' => $activityLog->causer->email],
            'event' => $this->getEventDetails($activityLog),
            'subject' => $this->getFormattedSubject($activityLog),
            'oldValues' => $this->renderOldValues($activityLog),
            'newValues' => $this->renderNewValues($activityLog),
        ];
    }

    /**
     * Get event description
     */
    private function getEventDetails($activityLog)
    {
        if ($activityLog->subject_type === Comment::class && $activityLog->event === 'created') {
            return 'comment added';
        }
        if ($activityLog->subject_type === Comment::class && $activityLog->event === 'deleted') {
            return 'comment removed';
        }

        return $activityLog->event;
    }

    /**
     * Get formatted subject
     */
    private function getFormattedSubject($activityLog)
    {
        if ($activityLog->subject_type === Comment::class && $activityLog->event === 'created') {
            $slug = $activityLog->properties['attributes']['commentable.slug'] ?? '';
            $name = $activityLog->properties['attributes']['commentable.name'] ?? '';

            return [
                'heading' => $slug,
                'description' => $name,
            ];
        }
        if ($activityLog->subject_type === Comment::class && $activityLog->event === 'deleted') {
            $slug = $activityLog->properties['old']['commentable.slug'] ?? '';
            $name = $activityLog->properties['old']['commentable.name'] ?? '';

            return [
                'heading' => $slug,
                'description' => $name,
            ];
        }
        if ($activityLog->subject_type === User::class) {
            return [
                'heading' => $activityLog->subject->name,
                'description' => $activityLog->subject->email,
            ];
        }
        $slug = $activityLog->subject->slug ?? '';
        $name = $activityLog->subject->name ?? '';

        return [
            'heading' => $slug,
            'description' => $name,
        ];
    }

    /**
     * Render new values
     */
    private function renderNewValues($activityLog)
    {
        $newValues = [];

        if ($activityLog->event === 'assignee added' && isset($activityLog->properties['attributes']['assignees'])) {
            $result = [];
            foreach ($activityLog->properties['attributes']['assignees'] as $assignee) {
                $result[] = "added assignee: {$assignee['name']} ({$assignee['email']})";
            }

            return $result;
        }

        if ($activityLog->event === 'viewer added' && isset($activityLog->properties['attributes']['viewers'])) {
            $result = [];
            foreach ($activityLog->properties['attributes']['viewers'] as $viewer) {
                $result[] = "added viewer: {$viewer['name']} ({$viewer['email']})";
            }

            return $result;
        }

        if ($activityLog->subject_type === Comment::class) {
            $content = $activityLog->properties['attributes']['content'] ?? '';

            return ["comment: {$content}"];
        }

        if (isset($activityLog->properties['attributes'])) {
            foreach ($activityLog->properties['attributes'] as $key => $value) {
                if ($key === 'start_date' || $key === 'due_date') {
                    $value = Carbon::parse($value)->timezone('Europe/London')->format('d M, Y H:i:s');
                } elseif ($key === 'is_private') {
                    $value = $value ? 'true' : 'false';
                }
                $formattedKey = str_replace(['.', '_'], ' ', $key);
                $newValues[] = "{$formattedKey}: {$value}";
            }
        }

        return $newValues;
    }

    /**
     * Render old values
     */
    private function renderOldValues($activityLog)
    {
        $oldValues = [];

        if ($activityLog->event === 'assignee removed' && isset($activityLog->properties['old']['assignees'])) {
            foreach ($activityLog->properties['old']['assignees'] as $assignee) {
                $oldValues[] = "removed assignee: {$assignee['name']} ({$assignee['email']})";
            }

            return $oldValues;
        }

        if ($activityLog->event === 'viewer removed' && isset($activityLog->properties['old']['viewers'])) {
            foreach ($activityLog->properties['old']['viewers'] as $viewer) {
                $oldValues[] = "removed viewer: {$viewer['name']} ({$viewer['email']})";
            }

            return $oldValues;
        }

        if ($activityLog->subject_type === Comment::class) {
            $content = $activityLog->properties['old']['content'] ?? '';

            return ["comment: {$content}"];
        }

        if (isset($activityLog->properties['old'])) {
            foreach ($activityLog->properties['old'] as $key => $value) {
                if ($key === 'start_date' || $key === 'due_date') {
                    $value = Carbon::parse($value)->timezone('Europe/London')->format('d M, Y H:i:s');
                } elseif ($key === 'is_private') {
                    $value = $value ? 'true' : 'false';
                }
                $formattedKey = str_replace(['.', '_'], ' ', $key);
                $oldValues[] = "{$formattedKey}: {$value}";
            }

            return $oldValues;
        }

        return $oldValues;
    }
}
