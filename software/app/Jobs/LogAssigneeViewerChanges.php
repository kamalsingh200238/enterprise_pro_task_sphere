<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogAssigneeViewerChanges implements ShouldQueue
{
    use Queueable;

    protected $model;

    protected User $causedBy;

    protected string $relationName;

    protected string $eventType;

    protected array $pivotIds;

    public function __construct($causedBy, $model, $relationName, $pivotIds, $eventType)
    {
        $this->model = $model;
        $this->causedBy = $causedBy;
        $this->relationName = $relationName;
        $this->pivotIds = $pivotIds;
        $this->eventType = $eventType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch the details of the added/removed users
        $affectedUsers = User::whereIn('id', $this->pivotIds)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            })->toArray();

        activity()
            ->performedOn($this->model)
            ->causedBy($this->causedBy)
            ->withProperties($this->getProperties($affectedUsers))
            ->event($this->eventType)
            ->log($this->eventType);
    }

    private function getProperties($affectedUsers)
    {
        if ($this->eventType === 'assignee added' || $this->eventType === 'viewer added') {
            return [
                'attributes' => [
                    $this->relationName => $affectedUsers,
                ],
            ];
        } else {
            return [
                'old' => [
                    $this->relationName => $affectedUsers,
                ],
            ];
        }
    }
}
