<?php

namespace App\Models;

use App\Jobs\LogAssigneeViewerChanges;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, LogsActivity, PivotEventTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'due_date',
        'status_id',
        'priority_id',
        'is_private',
        'created_by',
        'updated_by',
        'supervisor_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'is_private' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'start_date', 'due_date', 'is_private', 'status.name', 'priority.name', 'supervisor.name', 'supervisor.email'])
            ->logOnlyDirty();
    }

    public static function boot()
    {
        parent::boot();

        static::pivotAttached(function ($model, $relationName, $pivotIds) {
            $user = auth()->user();
            $eventType = $relationName === 'assignees' ? 'assignee added' : 'viewer added';
            LogAssigneeViewerChanges::dispatch(
                $user,
                $model,
                $relationName,
                $pivotIds,
                $eventType
            );
        });

        static::pivotDetached(function ($model, $relationName, $pivotIds) {
            $user = auth()->user();
            $eventType = $relationName === 'assignees' ? 'assignee removed' : 'viewer removed';
            LogAssigneeViewerChanges::dispatch(
                $user,
                $model,
                $relationName,
                $pivotIds,
                $eventType
            );
        });
    }

    /**
     * Get status associated to project
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get priority associated to project
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    /**
     * Get user who created the project
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get user who last updated the project
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the supervisor of the project
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the assignees of the project
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_assignees');
    }

    /**
     * Get the viewers of the project
     */
    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_viewers');
    }

    /**
     * Get the tasks of the project
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the comments of the project
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
