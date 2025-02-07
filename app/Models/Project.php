<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
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
        'supervisor_id'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'is_private' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship: Project belongs to a status
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Relationship: Project belongs to a priority
     */
    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    /**
     * Relationship: User who created the project
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: User who last updated the project
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship: Project supervisor
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Relationship: Project assignees
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'project_assignees');
    }

    /**
     * Relationship: Project viewers (for private projects)
     */
    public function viewers()
    {
        return $this->belongsToMany(User::class, 'project_viewers');
    }
}
